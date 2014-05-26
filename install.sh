#!/bin/bash

tput colors &> /dev/null
if [ "$?" == "0" ]; then
    DEFAULT="\e[39m"
    GREEN="\e[32m"
    RED="\\033[1;31m"
    PINK="\\033[1;35m"
    BLUE="\\033[1;34m"
    WHITE="\\033[0;02m"
    YELLOW="\e[33m"
    CYAN="\\033[1;36m"
fi

pluginName="shotnget_smime"

if [ "$1" == "--noinstall" ] ||[ "$2" == "--noinstall" ]; then
    noInstall="true"
elif [ "$1" == "--sendmail" ] ||[ "$2" == "--sendmail" ]; then
    sendMail="true"
fi

echo -e [ $GREEN"Start instalation"$DEFAULT ]

# Check if we are root
if [[ $EUID -ne 0 ]]; then
    echo "Please launch the install script in root mode"
    exit 1;
fi

if [ "$noInstall" != "true" ] && [ "$sendMail" != "true" ]; then
    echo -e [ $CYAN"Check php5 modules"$DEFAULT ]

# Check php5-mcrypt for crypto functions of shotngetapi
    echo Checking for php5-mcrypt ...
    dpkg -s php5-mcrypt &> /dev/null
    if [ "$?" -ne "0" ]; then
	echo -e [ $RED"Error"$DEFAULT ] "php5-mcrypt is currently not installed. If it's already installed try lauching script with --noinstall"
	exit 1;
    else
	echo -e [ "$GREEN""Done""$DEFAULT" ]
    fi

# Check php5-gd for generate QRCode (image) of shotngetapi
    echo Checkin for php5-gd ...
    dpkg -s php5-gd &> /dev/null
    if [ "$?" -ne "0" ]; then
	echo -e [ $RED"Error"$DEFAULT ] "php5-gd is currently not installed. If it's already installed try lauching script with --noinstall"
	exit 1;
    else
	echo -e [ "$GREEN""Done""$DEFAULT" ]
    fi
fi

if [ "$sendMail" != "true" ]; then
    echo -e [ $CYAN"Creating directories"$DEFAULT ]

    echo "Please enter a directory to create Shotnget directory for temp files, private key and sign (ex : /usr)"
else
    echo -e [ $CYAN"Format mail"$DEFAULT ]

    echo "Please enter the certphone directory where temp files, private key and sign are located (ex : /usr)"

fi

read tempDir

# If dir does'nt start with / add pwd to the link
if [[ ${tempDir:0:1} != "/" ]] ; then
    pwd=`pwd`
    tempDir="$pwd/$tempDir"
fi

# If there is a / at the end, remove it
[[ $tempDir == */ ]] && tempDir="${tempDir%/}"

# Check if directory exist
if [ ! -d "$tempDir" ]; then
    echo "$tempDir does not exist."
    exit 2;
fi

echo "Create Shotnget directory in $tempDir/certphone ..."

# Check if directory already exist
if [ ! -d "$tempDir/certphone" ] || [ "$sendMail" == "true" ]; then
    # Create directories for shotngetapi
    if [ "$sendMail" != "true" ]; then
	mkdir "$tempDir/certphone"
	mkdir "$tempDir/certphone/temp"
	mkdir "$tempDir/certphone/Certificates"
	mkdir "$tempDir/certphone/Keys"
	echo -e [ "$GREEN""Done""$DEFAULT" ]
	echo Generate private and public key ...
	# Generate private and public key for the use of shotngetapi
	openssl genrsa -out "$tempDir/certphone/kpri" 1024
	openssl rsa -in "$tempDir/certphone/kpri" -pubout -out "$tempDir/certphone/kpub"
	if [ "$?" -ne "0" ]; then
	    echo -e [ $RED"Error"$DEFAULT ] "Failed to generate private and public key please generate it yourself and put private key inside $tempDir/certphone/kpri and public key inside $tempDir/certphone/kpub and lauch this script with option --sendmail"
	    exit 1;
	fi
	echo -e [ "$GREEN""Done""$DEFAULT" ]
	echo -e "Manage files in $tempDir/certphone"
	mv "shotnget_sign_engine.so" "$tempDir/certphone/"
	# Put right to the file
	chmod 644 "$tempDir/certphone/kpri"
	chown www-data:www-data "$tempDir/certphone/temp"
	chown www-data:www-data "$tempDir/certphone/Keys"
	chown www-data:www-data "$tempDir/certphone/Certificates"
	echo -e [ "$GREEN""Done""$DEFAULT" ]
	
	echo -e [ $CYAN"Creation of signature to verify if the website is correct"$DEFAULT ]
    fi
    echo -e [ "$YELLOW"Info"$DEFAULT" ] "In order to create the signature, a mail will be sent to Trust Designer, creators of this plugin. We will sign it and send you a response with a file to place inside the directiry : $tempDir/certphone/"
    echo -e [ "$YELLOW"Info"$DEFAULT" ] "Before sending this mail we need further informations : "

    
    isOk="false"
    while [ "$isOk" != "true" ]; do
	
	echo "Please enter the physical link for the root web site (ex : /var/www)"
	read physicalLink
	
	if [[ ${physicalLink:0:1} != "/" ]] ; then
	    pwd=`pwd`
	    physicalLink="$pwd/$physicalLink"
	fi
	
	[[ $physicalLink != */ ]] && physicalLink="$physicalLink/"
	
	echo "Please enter the mail address where the signature file will be sent"
	read mailAddress
	
	echo "Please enter the url of your website (ex : http://mail.mailbox.com)"
	read websiteUrl
	
	echo -e [ "$YELLOW"Info"$DEFAULT" ] "Sending mail with configuration below : "
	
	echo -e $PINK"Physical address"$DEFAULT "$physicalLink"
	echo -e $PINK"Mail address"$DEFAULT "$mailAddress"
	echo -e $PINK"WebSite URL"$DEFAULT "$websiteUrl"
	echo -e "Is these informations correct ? [y/n]"
	
	ok=
	while [ "$ok" != "y" ] && [ "$ok" != "Y" ] && [ "$ok" != "n" ] && [ "$ok" != "N" ]; do
	    read ok
	    if [ "$ok" = "y" ] || [ "$ok" = "Y" ]; then
                isOk="true"
	    elif [ "$ok" != "n" ] && [ "$ok" != "N" ]; then
		echo "Please enter y or n."
	    fi
	done
    done
    
    publicKeyFile=$RANDOM
    publicKeyFile=$[ $publicKeyFile % 1000000000 ]
    
    while [ "${#publicKeyFile}" != 10 ]; do
	publicKeyFile="0$publicKeyFile"
    done
    
    cp "$tempDir/certphone/kpub" "$physicalLink$publicKeyFile"
    
    [[ $websiteUrl == */ ]] && websiteUrl="${websiteUrl%/}"

    echo -e "Nouvelle demande de signature\nURL du site : $websiteUrl/$publicKeyFile\nDemande de : $mailAddress\n" | mail -s "Shotnget server signature" "activation@shotnget.com" -- -f "$mailAddress"
else
    echo -e [ "$GREEN""Already installed""$DEFAULT" ]
fi

echo -e [ $CYAN"Add plugin to config"$DEFAULT ]

configPath="../../config/config.inc.php"

# Check if config file is found
if [ ! -f $configPath ]; then
    echo -e [ $RED"Error"$DEFAULT ] "Failed to found config file, please add the pluggin in your config file yourself" ]
else
    # Check how to add plugin in the file
    if grep -Fq "$config['plugins']" "$configPath"; then
	configuration="\$config['plugins']"
    elif grep -Fq "$rcmail_config['plugins']" "$configPath"; then
	configuration="\$rcmail_config['plugins']"
    fi

    # If we know put the plugin
    if [ "$configuration" != '' ]; then
	# Check if the plugin is already activate in roundcube configuration
	if grep -Fxq "array_push($configuration, '$pluginName');" "$configPath"; then
	    echo -e [ "$GREEN""Already installed""$DEFAULT" ]
	else
	    echo "array_push($configuration, '$pluginName');" >> $configPath
	    echo -e [ "$GREEN""Done""$DEFAULT" ]
	fi
    else
	echo -e [ $RED"Error"$DEFAULT ] "Failed to add plugin please add it with $rcmail_config['plugins'] = array('$pluginName');"
    fi
fi

echo -e [ $CYAN"Configure shotnget API"$DEFAULT ]

echo "Please enter url to roundcube mail (ex : http://mail.mailbox.com/roundcubemail)"

read url

# If url ends with /, remove it
[[ $url == */ ]] && url="${url%/}"

# Remove http:// or https:// form the url
if [ "${url:0:8}" = "https://" ]; then
    urlPath="${url:8}"
else
    urlPath="${url:7}"
fi

# Create config.php file for shotnget api
echo -e "<?php\n\nclass Config\n{" > "shotngetapi/config.php"

echo "public static \$TEMP_PATH = '$tempDir/certphone/temp/';" >> "shotngetapi/config.php"

echo "public static \$RESPONSE_URL = '$url/plugins/$pluginName/plugin/certphone_input.php';" >> "shotngetapi/config.php"

echo "public static \$TARGET_REDIRECT = '$url/plugins/$pluginName/account.php';" >> "shotngetapi/config.php"

echo "public static \$URLSIGN = '$tempDir/certphone/urlsign';" >> "shotngetapi/config.php"

echo "public static \$OLDURLSIGN = '$tempDir/certphone/urlsign';" >> "shotngetapi/config.php"

echo "public static \$KPUB = '$tempDir/certphone/kpub';" >> "shotngetapi/config.php"

echo "public static \$KPRI = '$tempDir/certphone/kpri';" >> "shotngetapi/config.php"

echo "public static \$ENGINE = '$tempDir/certphone/shotnget_sign_engine.so';" >> "shotngetapi/config.php"

echo -e "}\n?>" >> "shotngetapi/config.php"

echo -e [ "$GREEN""Done""$DEFAULT" ]

echo -e [ $CYAN"Configure $pluginName"$DEFAULT ]

# Create config.php file for shotnget api
echo -e "<?php\n\n" > "config.inc.php"

echo '$config'"['certificate_path'] = '$tempDir/certphone/Certificates/';" >> "config.inc.php"

echo '$config'"['keys_path'] = '$tempDir/certphone/Keys/';" >> "config.inc.php"

echo '$config'"['shotnget_sign_qrcode'] = true;" >> "config.inc.php"

echo -e "\n?>" >> "config.inc.php"

echo -e [ "$GREEN""Done""$DEFAULT" ]

echo -e [ "$YELLOW"Info"$DEFAULT" ] "If you want to change this configuration please check the file config.php in plugins/$pluginName/shotngetapi/config.php and plugins/$pluginName/config.inc.php"

echo -e [ $GREEN"Installation is now complete"$DEFAULT ]