jQuery( document ).ready(function($) {
	'use strict';

	var count = 0;

	$('#check-status').click(function(){
		count = 0;
		$('.theme-check-report').empty();
		$('.progress-bar').remove();
		$('.theme-check-report').before('<div class="progress-bar"><span>' + localization_object.check_starting + '</span></div>');
		themeCheckRunPHPCS();
	});

	function themeCheckRunPHPCS(){
		$.ajax({
			type: 'POST',
			url: ajaxurl,
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
			success:function(response) {
				if ( true === response.success ) {
					var theme_name  = response.data[0],
						theme_args  = response.data[1],
						theme_files_raw = response.data[2],
						total_files = Object.keys(theme_files_raw).length,
						file_number = 0,
						theme_files = _.reduce(theme_files_raw, function(result, value, key) {
							result[file_number] = value;
							file_number++;
							return result;
						}, {});

					individualSniff( theme_name, theme_args, theme_files, total_files, file_number = 0 );
				} else {
					$('.theme-check-report').before(response.data[0].message);
					$('.progress-bar').html( '<span class="error">' + localization_object.check_failed + '</span>' ).addClass('install-error');
				}
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});
	}

	function individualSniff( theme_name, theme_args, theme_files, total_files, file_number ) {
		var file_no = file_number;
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				'action': 'ns_theme_check_sniff',
				'theme_name': theme_name,
				'theme_args': theme_args,
				'file': theme_files[file_no],
				'ns_theme_check_nonce': $('#ns_theme_check_nonce').val(),
			},
			success: function(data, status, xhr) {
				var wrapper;
				count++;
				bumpProgressBar(count, total_files);
				wrapper = renderJSON(data)
				$('.theme-check-report').append(wrapper);
			},
			complete: function() {
				file_no++;
				if (file_no < total_files) {
					individualSniff( theme_name, theme_args, theme_files, total_files, file_no );
				}
			},
			error: function(xhr, status, errorThrown) {
				var wrapper;

				count++;
				bumpProgressBar(count, total_files);

				if ( 500 === xhr.status) {
					var files_val = {};
					files_val[theme_files[file_no]] = {
						'errors': 1,
						'warnings': 0,
						'messages': [{
							'column': 1,
							'fixable': false,
							'line': 1,
							'message': localization_object.sniff_error,
							'severity': 5,
							'type': 'ERROR'
						}]
					};
					var error_data = {
						'success': false,
						'data': {
							'files': files_val,
							'totals': {
								'errors': 1,
								'fixable': 0,
								'warnings': 0,
								'fatal_error': 1
							}
						}
					};
					$('.progress-bar').addClass('install-error');
					wrapper = renderJSON(error_data)
				}
				$('.theme-check-report').append(wrapper);
			}
		});
		return false;

	}

	function renderJSON( json ) {
		if ( typeof json.data == "undefined" || json.data == null ) {
			return;
		}

		if ( typeof json.data.totals == "undefined" || json.data.totals == null ) {
			return json.data;
		}

		if ( 0 == json.data.totals.errors && 0 == json.data.totals.warnings ) {
			return;
		}

		var wrapper = document.createElement('div');
		wrapper.className = "report-file-item";

		var heading = document.createElement('div');
		heading.className = "report-file-heading";

		var table = document.createElement('table');
		table.className = "report-table";

		$.each(json.data.files, function( files, value ){
			var filepath = files.split('/themes/');
			heading.textContent = filepath[1];

			$.each( value.messages, function( index, value ){

				var row = document.createElement('tr');
				if ( 'ERROR' == value.type ) {
					row.className = "item-type-error";
				} else {
					row.className = "item-type-warning";
				}

				var line = document.createElement('td');
				line.className = "td-line";
				line.textContent = value.line;
				row.appendChild(line);

				var type = document.createElement('td');
				type.className = "td-type";
				type.textContent = value.type;
				row.appendChild(type);

				var message = document.createElement('td');
				message.className = "td-message";
				message.textContent = value.message;
				row.appendChild(message);

				table.appendChild(row);
			} );

		});

		wrapper.appendChild(heading);
		wrapper.appendChild(table);

		return wrapper;

	}

	function bumpProgressBar(count, total_files){
		var percentComplete = (( count / total_files ) * 100).toFixed(2);
		$('.progress-bar').html( '<span>' + localization_object.percent_complete + percentComplete + '%</span>' ).append('<span class="meter" style="width: ' + percentComplete + '%"></span>');
	}
});
