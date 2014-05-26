shotnget_smime - Roundcube Plugin
=================================

Roundcube v1.0 plugin to log and sign/encrypt messages into Rouncubemail.

Please report bugs at : https://github.com/TrustDesigner/Roundcube-SMIME/issues

Install
-------

To install this plugin please run the script **install.sh** in root.

If the install.sh script doesn't run properly please follow these indications :

First install the php5 modules :
```bat
apt-get install php5-mcrypt
apt-get install php5-gd
```

Now in a different directory, create a **certphone** directory (ex: /usr/certphone). We recommand to not place this directory inside the website directories (/var) because the private key will be stored here.

Inside this directory, create three others :
  - path_to_certphone/temp
  - path_to_certphone/Certificates
  - path_to_certphone/Keys

Now create the private and public key used by the server to encrypt / decrypt exchanges between the smartphone and the server.
```bat
openssl genrsa -out kpri 1024
openssl rsa -in kpri -pubout -out kpub
```
Place the kpub and kpri files inside the **certphone** directory.
Now give the rights to the files and directories and move the shotnget openssl engine 
```bat
mv path_to_plugin/shotnget_sign_engine.so path_to_certphone/
chmod 644 path_to_certphone/kpri
chown www-data:www-data path_to_certphone/temp
chown www-data:www-data path_to_certphone/Keys
chown www-data:www-data path_to_certphone/Certificates
```

To use the plugin you need a signature form the TrustDesigner company. To have one you must do the following steps :
  - Put the kpub file at the root of yout website (ex: /var/www)
  - Send a mail to activation@shotnget.com with the following content :
      WebSite URL : your_website_url/kpub_filename (ex: https://mail.test.com/kpub_filename)

A response will be send with the signature to put in a file named **urlsign** in the certphone directory.

Now all you have to do is to configure the plugin.
First to activate the plugin, please put in the roundcube configuration file the following line : ```$rcmail_config['plugins'][] = 'shotnget_smime';```.

The in the shotnget_smime directory in the plugin directory of roundcube, add a config.inc.php file containing the following :
```php
<?php

  $config['certificate_path'] = 'path_to_certphone/certphone/Certificates/';
  $config['keys_path'] = 'path_to_certphone/certphone/Keys/';
  $config['shotnget_sign_qrcode'] = true;

?>
```

Then in the **shotngetapi** directory create a config.php file containing the following :
```php
<?php

  class Config
  {
    public static $TEMP_PATH = 'path_to_certphone/certphone/temp/';
    public static $RESPONSE_URL = 'url_to_roundcube/plugins/$pluginName/plugin/certphone_input.php';
    public static $TARGET_REDIRECT = 'url_to_roundcube/plugins/$pluginName/account.php';
    public static $URLSIGN = 'path_to_certphone/certphone/urlsign';
    public static $OLDURLSIGN = 'path_to_certphone/certphone/urlsign';
    public static $KPUB = 'path_to_certphone/certphone/kpub';
    public static $KPRI = 'path_to_certphone/certphone/kpri';
    public static $ENGINE = 'path_to_certphone/certphone/shotnget_sign_engine.so';
  }
?>
```
The *url_to_roundcube* is for example https://mail.test.com/roundcubemail/

Contact
-------


License
-------

This plugin is distributed under the GNU General Public License Version 3.
Please read through the file LICENSE for more information about this license.

