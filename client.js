function shotnget_login() {
    var button = document.createElement("input");

    button.id = "shotnget_login_button";
    button.type = "button";
    button.className = "button mainaction";
    button.value = rcmail.gettext('connection', 'shotnget_smime');

    document.getElementsByClassName("formbuttons")[0].appendChild(button);
    document.getElementById("rcmloginuser").required = false;
    document.getElementById("rcmloginpwd").required = false;
    
    // Add listener on shotnget button
    var shotnget_button = document.getElementById("shotnget_login_button");
    shotnget_button.addEventListener('click', function () {
	var input = document.createElement("input");
        input.id = 'shotnget_submit_login';
        input.name = '_shotnget';
        input.type = 'hidden';
        input.value = true;
        document.getElementsByTagName("form")[0].appendChild(input);
	document.getElementsByTagName("form")[0].submit();
	document.getElementById("shotnget_submit_login").parentNode.removeChild(document.getElementById("shotnget_submit_login"));
	document.getElementById("shotnget_login_div").style.display = 'block';
    });
}

function shotnget_compose_mail() {
    var div = document.createElement("table");

    div.style.position = "relative";
    div.style.display = "inline-block";
    div.style.fontFamily = '"Lucida Grande", Verdana, Arial, Helvetica, sans-serif';
    div.style.fontSize = "10px";
    div.style.color = "#555";
    div.className = "button";
    div.innerHTML = "<td><label><input id='shotnget_smime_sign' type='checkbox' style='vertical-align: middle; position: relative; bottom: 1px;' />" + rcmail.gettext('sign_mail', 'shotnget_smime') + "</label></td>";
    div.innerHTML += "<td><label><input id='shotnget_smime_crypted' type='checkbox' style='vertical-align: middle; position: relative; bottom:1px;' />" + rcmail.gettext('crypt_mail', 'shotnget_smime') + "</label></td>";

    div.id = 'shotnget_smime';
    document.getElementById('mailtoolbar').appendChild(div);

    document.getElementById('shotnget_smime_sign').addEventListener('click', function(e) {
	var rand = document.getElementById('shotnget_rand');
	if (rand == null ||typeof(rand) == 'undefined')
	    rcmail.http_post('plugin.changeSign', '_checked=' + (this.checked == true ? 'true' : 'false') + "&_rand=" + null, true);
	else
	    rcmail.http_post('plugin.changeSign', '_checked=' + (this.checked == true ? 'true' : 'false') + "&_rand=" + document.getElementById('shotnget_rand').innerHTML, true);
    });
    document.getElementById('shotnget_smime_crypted').addEventListener('click', function(e) {
	rcmail.http_post('plugin.changeCrypted', '_checked=' + (this.checked == true ? 'true' : 'false'), true);
    });

    rcmail.addEventListener('plugin.shotnget_smime.hideQrcode', hide_qrcode);

    if (rcmail.env.shotnget_sign_qrcode == true) {
	document.getElementsByClassName("button send")[0].addEventListener('click', send_mail);
    }    
}

function QRCodeCallback(html) {
    document.getElementById("shotnget_login_div").style.display = 'none';
    document.getElementById("shotnget_login_div").addEventListener('click', function() {
        document.getElementById("shotnget_login_div").style.display = 'none';
        var rand = document.getElementById("shotnget_rand").innerHTML;
        rcmail.http_post('plugin.cancelSend', '&_rand=' + rand, true);
    });
    var input = document.getElementById("shotnget_submit_rand");
    if (input != null & input != 'undefined') {
	document.getElementById("shotnget_submit_rand").value = document.getElementById('shotnget_rand').innerHTML;
    } else {
	var form = document.getElementsByTagName('form');
	if (form != null && form != 'undefined' && form.length != 0) {
	    var input = document.createElement("input");
	    input.id = 'shotnget_submit_rand';
	    input.name = '_rand';
	    input.type = 'hidden';
	    input.value = document.getElementById('shotnget_rand').innerHTML;
	    form[0].appendChild(input);
	}
    }
}

rcmail.addEventListener('init', function(evt) {
    var text;
    if (document.getElementById('login-form') != null) {
	shotnget_login();
	text = rcmail.gettext('flash_to_connect', 'shotnget_smime');
    } else {
	shotnget_compose_mail();
	text = rcmail.gettext('flash_to_sign', 'shotnget_smime');
    }
    rcmail.addEventListener('plugin.changeCallback', response_handler);
    get_shotnget_code("plugins/shotnget_smime/get_code.php", QRCodeCallback, text);
});

function response_handler(e) {
    //console.log(e);
}

function hide_qrcode(e) {
    var div = document.getElementById('shotnget_login_div');
    if (div != null && typeof(div) != 'undefined')
        div.style.display = 'none';
}

function send_mail() {
    if (document.getElementById('shotnget_smime_sign').checked == true)
	document.getElementById("shotnget_login_div").style.display = 'block';
}
