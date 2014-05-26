rcmail.addEventListener('init', function(evt) {
    
    var button = document.createElement("input");
    var headers = document.getElementById("messageheader");

    button.type = 'button';
    button.value = rcmail.gettext('uncrypt_message', 'shotnget_smime');
    button.className = 'button';
    button.id = 'shotnget_uncrypt';
    headers.appendChild(button);

    rcmail.addEventListener('plugin.shotnget_smime.hideQrcode', hide_qrcode);

    if (rcmail.env.shotnget_sign_qrcode == true) {
        button.addEventListener('click', uncrypt_mail);
	get_shotnget_code("plugins/shotnget_smime/get_code.php", hide_qrcode, rcmail.gettext('flash_to_uncrypt', 'shotnget_smime'));
    }
});

function uncrypt_mail(e) {
    document.getElementById("shotnget_login_div").style.display = 'block';
    rcmail.http_post('plugin.uncryptMailShotnget', '_rand=' + document.getElementById('shotnget_rand').innerHTML, true);
}

function hide_qrcode(e) {
    var div = document.getElementById('shotnget_login_div');
    console.log(e);
    if (div != null && typeof(div) != 'undefined')
        div.style.display = 'none';
    if (e[1] == null)
	location.reload();
}
