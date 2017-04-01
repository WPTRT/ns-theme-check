jQuery( document ).ready(function($) {
	$('#check-status').click(function(){
		$(".theme-check-report").empty();
		themeCheckRunPHPCS();
	});
	function themeCheckRunPHPCS(){
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				'action': 'ns_theme_check_run',
				'themename': $('select[name=themename]').val(),
				'hide_warning': $('input[name=hide_warning]').is(':checked'),
				'raw_output': $('input[name=raw_output]').is(':checked'),
				'minimum_php_version': $('select[name=minimum_php_version]').val(),
				'wordpress-theme': $('input[name=wordpress-theme]').is(':checked'),
				'wordpress-core': $('input[name=wordpress-core]').is(':checked'),
				'wordpress-extra': $('input[name=wordpress-extra]').is(':checked'),
				'wordpress-docs': $('input[name=wordpress-docs]').is(':checked'),
				'wordpress-vip': $('input[name=wordpress-vip]').is(':checked'),
				'ns_theme_check_nonce': $('#ns_theme_check_nonce').val(),
			},
			success:function(data) {
				$(".theme-check-report").html(data);
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});
	}
});
