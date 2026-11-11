/* WP Then Now Slider – écran d'édition d'une comparaison */
jQuery(function ($) {
	'use strict';

	// Sélecteur de médias
	$('.wptn-media-field').each(function () {
		var $field = $(this);
		var $input = $field.find('input[type=hidden]');
		var $preview = $field.find('.wptn-media-preview');
		var $remove = $field.find('.wptn-media-remove');
		var frame;

		$field.on('click', '.wptn-media-select', function (e) {
			e.preventDefault();
			if (!frame) {
				frame = wp.media({
					title: wptnAdmin.title,
					button: { text: wptnAdmin.button },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
					$input.val(att.id);
					$preview.html($('<img alt="">').attr('src', url));
					$remove.prop('hidden', false);
				});
				frame.on('open', function () {
					var id = parseInt($input.val(), 10);
					if (id) {
						var a = wp.media.attachment(id);
						a.fetch();
						frame.state().get('selection').reset([a]);
					}
				});
			}
			frame.open();
		});

		$field.on('click', '.wptn-media-remove', function (e) {
			e.preventDefault();
			$input.val('');
			$preview.html($('<span class="wptn-media-empty">').text(wptnAdmin.empty));
			$remove.prop('hidden', true);
		});
	});

	// Sélecteur de couleur
	$('.wptn-color-field').wpColorPicker();

	// Couleurs globales / individuelles
	function toggleColors() {
		var custom = $('input[name="wptn[color_mode]"]:checked').val() === 'custom';
		$('.wptn-custom-color').toggle(custom);
	}
	$(document).on('change', 'input[name="wptn[color_mode]"]', toggleColors);
	toggleColors();

	// Bouton copier
	$(document).on('click', '.wptn-copy', function () {
		var $btn = $(this);
		var code = $btn.data('code');
		var done = function () {
			var old = $btn.text();
			$btn.text(wptnAdmin.copied);
			setTimeout(function () { $btn.text(old); }, 1500);
		};
		if (navigator.clipboard) {
			navigator.clipboard.writeText(code).then(done);
		} else {
			$('.wptn-shortcode-input').first().trigger('select');
			document.execCommand('copy');
			done();
		}
	});
});
