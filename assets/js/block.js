/* WP Then Now Slider – bloc Gutenberg (sans étape de build).
 * Les panneaux de réglages sont générés depuis le schéma PHP (window.wptnBlock). */
(function (wp) {
	var el = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var useSelect = wp.data.useSelect;
	var ServerSideRender = wp.serverSideRender;
	var D = window.wptnBlock || { schema: {}, sections: {}, settingsUrl: '' };
	var KEYS = Object.keys(D.schema);

	/* ---------- Champ image ---------- */
	function MediaField(props) {
		var media = useSelect(function (select) {
			return props.value ? select('core').getMedia(props.value) : null;
		}, [props.value]);
		var thumb = media && (media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail
			? media.media_details.sizes.thumbnail.source_url
			: media.source_url);

		return el(c.BaseControl, { label: props.label, help: props.help, className: 'wptn-block-media' },
			thumb ? el('img', { src: thumb, alt: '' }) : null,
			el('div', { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' } },
				el(be.MediaUploadCheck, null,
					el(be.MediaUpload, {
						onSelect: function (m) { props.onChange(m.id); },
						allowedTypes: ['image'],
						value: props.value,
						render: function (o) {
							return el(c.Button, { variant: props.value ? 'secondary' : 'primary', onClick: o.open },
								props.value ? __('Replace', 'wp-then-now-slider') : __('Choose an image', 'wp-then-now-slider'));
						}
					})
				),
				props.value ? el(c.Button, { variant: 'link', isDestructive: true, onClick: function () { props.onChange(0); } }, __('Remove', 'wp-then-now-slider')) : null
			)
		);
	}

	/* ---------- Champ générique ---------- */
	function Field(props) {
		var f = props.field;
		var v = props.value;
		var set = props.onChange;
		var options = f.options ? Object.keys(f.options).map(function (k) { return { label: f.options[k], value: k }; }) : [];

		switch (f.type) {
			case 'media':
				return el(MediaField, { label: f.label, help: f.help, value: v, onChange: set });
			case 'text':
				return el(c.TextControl, { label: f.label, help: f.help, value: v || '', onChange: set });
			case 'textarea':
				return el(c.TextareaControl, { label: f.label, help: f.help, value: v || '', onChange: set });
			case 'bool':
				return el(c.ToggleControl, { label: f.label, help: f.help, checked: !!v, onChange: set });
			case 'select':
				return el(c.SelectControl, { label: f.label, help: f.help, value: v, options: options, onChange: set });
			case 'radio':
				return el(c.RadioControl, { label: f.label, help: f.help, selected: v, options: options, onChange: set });
			case 'range':
				return el(c.RangeControl, { label: f.label, help: f.help, value: v, min: f.min, max: f.max, onChange: set });
			case 'color':
				return el(c.BaseControl, { label: f.label, help: f.help },
					el(c.ColorPalette, { value: v || undefined, clearable: true, onChange: function (x) { set(x || ''); } })
				);
		}
		return null;
	}

	function isVisible(field, attrs) {
		if (!field.show_if) return true;
		return Object.keys(field.show_if).every(function (k) {
			return field.show_if[k].indexOf(attrs[k]) !== -1;
		});
	}

	function schemaAttrs(a) {
		var out = {};
		KEYS.forEach(function (k) { out[k] = a[k]; });
		return out;
	}

	wp.blocks.registerBlockType('wptn/slider', {
		apiVersion: 2,
		title: __('Before / After', 'wp-then-now-slider'),
		description: __('Display a before / after photo comparison.', 'wp-then-now-slider'),
		icon: 'image-flip-horizontal',
		category: 'media',
		keywords: ['avant', 'après', 'before', 'after', 'then', 'now', 'comparaison', 'comparison', 'slider'],
		supports: { align: ['wide', 'full'] },

		edit: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = be.useBlockProps();

			// Conversion des anciens attributs (v1.x / v2.0) à l'ouverture.
			useEffect(function () {
				var u = {};
				if (a.beforeId && !a.before) u.before = a.beforeId;
				if (a.afterId && !a.after) u.after = a.afterId;
				if (a.beforeLabel !== undefined) u.before_label = a.beforeLabel;
				if (a.afterLabel !== undefined) u.after_label = a.afterLabel;
				if (a.showLabels !== undefined) u.labels = a.showLabels;
				if (a.hover) u.mode = 'hover';
				if (a.useGlobal === false) u.color_mode = 'custom';
				if (a.focusColor !== undefined) u.focus_color = a.focusColor;
				if (Object.keys(u).length) {
					['beforeId', 'afterId', 'beforeUrl', 'afterUrl', 'beforeLabel', 'afterLabel', 'showLabels', 'hover', 'useGlobal', 'focusColor'].forEach(function (k) {
						if (u.before === undefined && (k === 'beforeUrl')) return; // garde l'URL si pas d'ID
						if (u.after === undefined && (k === 'afterUrl')) return;
						u[k] = undefined;
					});
					set(u);
				}
			}, []);

			// Comparaisons enregistrées
			var comparisons = useSelect(function (select) {
				return select('core').getEntityRecords('postType', 'wptn_comparison', { per_page: -1, status: 'publish,draft,private', orderby: 'title', order: 'asc' });
			}, []);
			var options = [{ label: __('— Custom images —', 'wp-then-now-slider'), value: 0 }];
			(comparisons || []).forEach(function (p) {
				options.push({ label: (p.title && p.title.rendered) || ('#' + p.id), value: p.id });
			});
			var pickComparison = el(c.SelectControl, {
				label: __('Saved comparison', 'wp-then-now-slider'),
				value: a.comparisonId,
				options: options,
				onChange: function (v) { set({ comparisonId: parseInt(v, 10) || 0 }); },
				help: comparisons === null ? __('Loading…', 'wp-then-now-slider') : __('Manage your comparisons in the “Before / After” admin menu.', 'wp-then-now-slider')
			});

			var panels = [
				el(c.PanelBody, { key: 'source', title: __('Comparison', 'wp-then-now-slider') },
					pickComparison,
					a.comparisonId ? el(c.ExternalLink, { href: 'post.php?post=' + a.comparisonId + '&action=edit' }, __('Edit this comparison', 'wp-then-now-slider')) : null
				)
			];

			if (!a.comparisonId) {
				Object.keys(D.sections).forEach(function (sec, i) {
					var fields = KEYS.filter(function (k) { return D.schema[k].section === sec && isVisible(D.schema[k], a); });
					if (!fields.length) return;
					panels.push(el(c.PanelBody, { key: sec, title: D.sections[sec], initialOpen: i === 0 },
						fields.map(function (k) {
							return el(Field, {
								key: k,
								field: D.schema[k],
								value: a[k],
								onChange: function (v) { var o = {}; o[k] = v; set(o); }
							});
						}),
						sec === 'colors' && D.settingsUrl ? el(c.ExternalLink, { href: D.settingsUrl }, __('Edit global colors', 'wp-then-now-slider')) : null
					));
				});
			}

			var inspector = el.apply(null, [be.InspectorControls, null].concat(panels));
			var body;

			if (a.comparisonId) {
				body = el(ServerSideRender, { block: 'wptn/slider', attributes: { comparisonId: a.comparisonId } });
			} else if ((a.before || a.beforeUrl) && (a.after || a.afterUrl)) {
				body = el(ServerSideRender, { block: 'wptn/slider', attributes: Object.assign(schemaAttrs(a), { beforeUrl: a.beforeUrl, afterUrl: a.afterUrl }) });
			} else {
				body = el(c.Placeholder, {
					icon: 'image-flip-horizontal',
					label: __('Before / After', 'wp-then-now-slider'),
					instructions: __('Choose a saved comparison, or two images.', 'wp-then-now-slider')
				},
					el('div', { style: { width: '100%' } },
						(comparisons && comparisons.length) ? pickComparison : null,
						el('div', { className: 'wptn-placeholder-row' },
							el(Field, { field: D.schema.before, value: a.before, onChange: function (v) { set({ before: v }); } }),
							el(Field, { field: D.schema.after, value: a.after, onChange: function (v) { set({ after: v }); } })
						)
					)
				);
			}

			return el('div', blockProps, inspector, body);
		},

		save: function () {
			return null; // Rendu côté serveur (PHP)
		}
	});
})(window.wp);
