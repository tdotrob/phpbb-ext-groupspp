phpbb.addAjaxCallback('hide_foe_link', function(res) {
	if (res.success) {
		$(this).parent().remove();
	}
});
