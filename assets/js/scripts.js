jQuery(document).ready(function($) {
	$('body').on('change', '#cherry-generator-attr-bg_type', function() {
		var $bg_type_values = $(this),
			bg_type = $bg_type_values.val(),
			$container = $('.cherry-generator-attr-right-container');
		// Load new options
		window.cherry_generator_get_bg_type = $.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'custom_shortcodes_generator_get_bg_type',
				bg_type: bg_type,
				noselect: true
			},
			dataType: 'html',
			beforeSend: function() {
				// Check previous requests
				if (typeof window.cherry_generator_get_bg_type === 'object') window.cherry_generator_get_bg_type.abort();
				// Show loading animation
				$container.append('<div class="cherry-generator-loading"></aiv>');
			},
			success: function(data) {
				// Remove previous options
				$('#custom-shortcodes-bg-type-atts').remove();
				// Append new options
				$container.append(data);
				// Hide loading animation
				$('.cherry-generator-loading').remove();

				// Init color pickers
				if ( $.isFunction( jQuery.fn.farbtastic ) ) {
					$('.cherry-generator-select-color').each(function(index) {
						$(this).find('.cherry-generator-select-color-wheel').filter(':first').farbtastic('.cherry-generator-select-color-value:eq(' + index + ')');
						$(this).find('.cherry-generator-select-color-value').focus(function() {
							$('.cherry-generator-select-color-wheel:eq(' + index + ')').show();
						});
						$(this).find('.cherry-generator-select-color-value').blur(function() {
							$('.cherry-generator-select-color-wheel:eq(' + index + ')').hide();
						});
					});
				} else {
					$('.cherry-generator-select-color').each(function(index) {
						$(this).find('.cherry-generator-select-color-value').wpColorPicker();
					});
				}

				// Init media buttons
				$('.cherry-generator-upload-button').each(function() {
					var $button = $(this),
						$val = $(this).parents('.cherry-generator-attr-container').find('input:text'),
						file;
					$button.on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						// If the frame already exists, reopen it
						if (typeof(file) !== 'undefined') file.close();
						// Create WP media frame.
						file = wp.media.frames.su_media_frame_2 = wp.media({
							// Title of media manager frame
							title: cherry_shortcodes_generator.upload_title,
							button: {
								//Button text
								text: cherry_shortcodes_generator.upload_insert
							},
							// Do not allow multiple files, if you want multiple, set true
							multiple: false
						});
						//callback for selected image
						file.on('select', function() {
							var attachment = file.state().get('selection').first().toJSON();
							$val.val(attachment.url).trigger('change');
						});
						// Open modal
						file.open();
					});
				});
			}
		});
	});
});