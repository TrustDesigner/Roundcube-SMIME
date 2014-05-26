rcmail.addEventListener('init', function(evt) {
    var tab = $('<span>').addClass('listitem').attr('id', 'settingstabpluginshotngetmail');
    var button = $('<a>')
	.attr('title', rcmail.gettext('certificate', 'shotnget_smime'))
	.attr('id', 'rcmbtn109')
	.attr('href', rcmail.env.comm_path+'&_action=plugin.shotnget_smime.add_certificate')
	.html(rcmail.gettext('certificate')).appendTo(tab);

    // add button and register commands
    rcmail.add_element(tab, 'tabs');
});