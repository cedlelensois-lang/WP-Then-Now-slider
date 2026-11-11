/* WP Then Now Slider – bloc Gutenberg (sans étape de build) */
(function (wp) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var useSelect = wp.data.useSelect;
	var ServerSideRender = wp.serverSideRender;
	var G = window.wptnBlock || { color: '#ffffff', focusColor: '', settingsUrl: '' };

	function pick(setAttributes, prefix) {
		return function (media) {
			var o = {};
			o[prefix + 'Id'] = media.id;
			o[prefix + 'Url'] = media.url;
			setAttributes(o);
		};
	}

	function MediaButton(props) {
		return el(be.MediaUploadCheck, null,
			el(be.MediaUpload, {
				onSelect: props.onSelect,
				allowedTypes: ['image'],
				value: props.value,
				render: function (o) {
					return el(c.Button, { variant: props.url ? 'secondary' : 'primary', onClick: o.open }, props.label);
				}
			})
		);
	}

	function Knob() {
		return el('div', { className: 'wptn-handle' },
			el('span', { className: 'wptn-line' }),
			el('span', { className: 'wptn-knob' },
				el('svg', { viewBox: '0 0 24 24', width: 22, height: 22 },
					el('path', { d: 'M9 6l-6 6 6 6M15 6l6 6-6 6', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' })
				)
			)
		);
	}

	wp.blocks.registerBlockType('wptn/slider', {
		apiVersion: 2,
		title: __('Before / After', 'wp-then-now-slider'),
		description: __('Display a before / after photo comparison.', 'wp-then-now-slider'),
		icon: 'image-flip-horizontal',
		category: 'media',
		keywords: ['avant', 'après', 'before', 'after', 'comparaison', 'slider'],
		supports: { align: ['wide', 'full'] },

		edit: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = be.useBlockProps();

			// Comparaisons enregistrées
			var comparisons = useSelect(function (select) {
				return select('core').getEntityRecords('postType', 'wptn_comparison', { per_page: -1, status: 'publish,draft,private', orderby: 'title', order: 'asc' });
			}, []);

			var options = [{ label: __('— Custom images —', 'wp-then-now-slider'), value: 0 }];
			(comparisons || []).forEach(function (p) {
				options.push({ label: (p.title && p.title.rendered) || ('#' + p.id), value: p.id });
			});

			var source = el(c.PanelBody, { title: __('Comparison', 'wp-then-now-slider') },
				el(c.SelectControl, {
					label: __('Saved comparison', 'wp-then-now-slider'),
					value: a.comparisonId,
					options: options,
					onChange: function (v) { set({ comparisonId: parseInt(v, 10) || 0 }); },
					help: comparisons === null ? __('Loading…', 'wp-then-now-slider') : __('Manage your comparisons in the “Before / After” admin menu.', 'wp-then-now-slider')
				}),
				a.comparisonId ? el(c.ExternalLink, { href: 'post.php?post=' + a.comparisonId + '&action=edit' }, __('Edit this comparison', 'wp-then-now-slider')) : null
			);

			/* ---- Mode : comparaison enregistrée ---- */
			if (a.comparisonId) {
				return el('div', blockProps,
					el(be.InspectorControls, null, source),
					el(ServerSideRender, { block: 'wptn/slider', attributes: { comparisonId: a.comparisonId } })
				);
			}

			/* ---- Mode : images personnalisées ---- */
			var vertical = a.orientation === 'vertical';
			var ready = a.beforeUrl && a.afterUrl;

			var inspector = el(be.InspectorControls, null,
				source,
				el(c.PanelBody, { title: __('Images', 'wp-then-now-slider') },
					el('div', { className: 'wptn-editor-actions' },
						el(MediaButton, { label: a.beforeUrl ? __('Replace “before”', 'wp-then-now-slider') : __('Before image', 'wp-then-now-slider'), url: a.beforeUrl, value: a.beforeId, onSelect: pick(set, 'before') }),
						el(MediaButton, { label: a.afterUrl ? __('Replace “after”', 'wp-then-now-slider') : __('After image', 'wp-then-now-slider'), url: a.afterUrl, value: a.afterId, onSelect: pick(set, 'after') })
					)
				),
				el(c.PanelBody, { title: __('Settings', 'wp-then-now-slider') },
					el(c.SelectControl, {
						label: __('Orientation', 'wp-then-now-slider'),
						value: a.orientation,
						options: [
							{ label: __('Horizontal (left / right)', 'wp-then-now-slider'), value: 'horizontal' },
							{ label: __('Vertical (top / bottom)', 'wp-then-now-slider'), value: 'vertical' }
						],
						onChange: function (v) { set({ orientation: v }); }
					}),
					el(c.RangeControl, {
						label: __('Starting position (%)', 'wp-then-now-slider'),
						value: a.start, min: 0, max: 100,
						onChange: function (v) { set({ start: v }); }
					}),
					el(c.ToggleControl, {
						label: __('Follow the mouse on hover', 'wp-then-now-slider'),
						checked: a.hover,
						onChange: function (v) { set({ hover: v }); }
					}),
					el(c.ToggleControl, {
						label: __('Show labels', 'wp-then-now-slider'),
						checked: a.showLabels,
						onChange: function (v) { set({ showLabels: v }); }
					}),
					a.showLabels && el(c.TextControl, {
						label: __('Before label', 'wp-then-now-slider'),
						value: a.beforeLabel,
						onChange: function (v) { set({ beforeLabel: v }); }
					}),
					a.showLabels && el(c.TextControl, {
						label: __('After label', 'wp-then-now-slider'),
						value: a.afterLabel,
						onChange: function (v) { set({ afterLabel: v }); }
					}),
					el(c.ToggleControl, {
						label: __('Use global colors', 'wp-then-now-slider'),
						checked: a.useGlobal,
						onChange: function (v) { set({ useGlobal: v }); },
						help: a.useGlobal ? __('Colors set in Before / After > Settings.', 'wp-then-now-slider') : __('Colors specific to this block. Empty = global color.', 'wp-then-now-slider')
					}),
					a.useGlobal && G.settingsUrl ? el(c.ExternalLink, { href: G.settingsUrl }, __('Edit global colors', 'wp-then-now-slider')) : null,
					!a.useGlobal && el('p', null, __('Handle color', 'wp-then-now-slider')),
					!a.useGlobal && el(c.ColorPalette, {
						value: a.color,
						clearable: true,
						onChange: function (v) { set({ color: v || '' }); }
					}),
					!a.useGlobal && el('p', null, __('Selection color', 'wp-then-now-slider')),
					!a.useGlobal && el(c.ColorPalette, {
						value: a.focusColor,
						clearable: true,
						onChange: function (v) { set({ focusColor: v || '' }); }
					})
				)
			);

			if (!ready) {
				return el('div', blockProps, inspector,
					el(c.Placeholder, {
						icon: 'image-flip-horizontal',
						label: __('Before / After', 'wp-then-now-slider'),
						instructions: __('Choose a saved comparison, or two images.', 'wp-then-now-slider')
					},
						el('div', { style: { width: '100%' } },
							(comparisons && comparisons.length) ? el(c.SelectControl, {
								label: __('Saved comparison', 'wp-then-now-slider'),
								value: 0,
								options: options,
								onChange: function (v) { set({ comparisonId: parseInt(v, 10) || 0 }); }
							}) : null,
							el('p', null, __('Or custom images:', 'wp-then-now-slider')),
							el('div', { className: 'wptn-placeholder-row' },
								el('div', null,
									a.beforeUrl && el('img', { src: a.beforeUrl, alt: '' }),
									el(MediaButton, { label: __('Before image', 'wp-then-now-slider'), url: a.beforeUrl, value: a.beforeId, onSelect: pick(set, 'before') })
								),
								el('div', null,
									a.afterUrl && el('img', { src: a.afterUrl, alt: '' }),
									el(MediaButton, { label: __('After image', 'wp-then-now-slider'), url: a.afterUrl, value: a.afterId, onSelect: pick(set, 'after') })
								)
							)
						)
					)
				);
			}

			var preview = el('div', {
				className: 'wptn-slider wptn-editor-preview wptn-' + (vertical ? 'vertical' : 'horizontal'),
				style: (function () {
					var col = (!a.useGlobal && a.color) || G.color || '#fff';
					var foc = (!a.useGlobal && a.focusColor) || G.focusColor;
					var st = { '--wptn-pos': a.start + '%', '--wptn-color': col, cursor: 'default' };
					if (foc) st['--wptn-focus'] = foc;
					return st;
				})()
			},
				el('img', { className: 'wptn-img wptn-after', src: a.afterUrl, alt: '' }),
				el('img', { className: 'wptn-img wptn-before', src: a.beforeUrl, alt: '' }),
				a.showLabels && a.beforeLabel && el('span', { className: 'wptn-label wptn-label-before' }, a.beforeLabel),
				a.showLabels && a.afterLabel && el('span', { className: 'wptn-label wptn-label-after' }, a.afterLabel),
				el(Knob)
			);

			return el('div', blockProps, inspector, preview,
				el(c.RangeControl, {
					label: __('Position preview', 'wp-then-now-slider'),
					value: a.start, min: 0, max: 100,
					onChange: function (v) { set({ start: v }); }
				})
			);
		},

		save: function () {
			return null; // Rendu côté serveur (PHP)
		}
	});
})(window.wp);
