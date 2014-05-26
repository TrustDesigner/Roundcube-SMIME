function shotnget_smime_remove(e) {
    rcmail.http_post('plugin.removeCertificate', '_email=' + e, true);
}

function shotnget_smime_details(e) {
    rcmail.http_post('plugin.detailsCertificate', '_email=' + e, true);
}

function shotnget_smime_save(e) {
    if (!document.getElementById('solo_cert_file').value) {
	rcmail.display_message(rcmail.gettext('no_file', 'shotnget_smime'), 'error');
	return;
    }
    var rand = document.getElementById("shotnget_rand").innerHTML;

    var input = document.createElement("input");
    input.id = 'shotnget_submit_login';
    input.name = '_rand';
    input.type = 'hidden';
    input.value = rand;
    document.getElementById('form_solo').appendChild(input);
    document.getElementById('form_solo').submit();
    document.getElementById('form_solo').removeChild(document.getElementById("shotnget_submit_login"));

    document.getElementById('shotnget_login_div').style.display = 'block';
    console.log(rand);
}

rcmail.addEventListener('init', function() {
    document.getElementById('settingstabpluginshotngetmail').className = 'listitem selected';
});