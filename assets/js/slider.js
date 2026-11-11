/* WP Then Now Slider – front-end */
(function () {
	'use strict';

	function initSlider(el) {
		if (el.dataset.wptnReady) return;
		el.dataset.wptnReady = '1';

		var vertical = el.dataset.orientation === 'vertical';
		var hoverMode = el.dataset.hover === '1';
		var knob = el.querySelector('.wptn-knob');
		var labelBefore = el.querySelector('.wptn-label-before');
		var labelAfter = el.querySelector('.wptn-label-after');
		var pos = parseFloat(getComputedStyle(el).getPropertyValue('--wptn-pos')) || 50;
		var dragging = false;
		var frame = null;

		function render() {
			frame = null;
			el.style.setProperty('--wptn-pos', pos + '%');
			if (knob) knob.setAttribute('aria-valuenow', Math.round(pos));
			// Masque une étiquette quand le curseur passe dessus
			if (labelBefore) labelBefore.classList.toggle('is-hidden', pos < 15);
			if (labelAfter) labelAfter.classList.toggle('is-hidden', pos > 85);
		}

		function setPos(value) {
			pos = Math.max(0, Math.min(100, value));
			if (!frame) frame = requestAnimationFrame(render);
		}

		function posFromEvent(e) {
			var r = el.getBoundingClientRect();
			return vertical
				? ((e.clientY - r.top) / r.height) * 100
				: ((e.clientX - r.left) / r.width) * 100;
		}

		el.addEventListener('pointerdown', function (e) {
			if (e.button !== undefined && e.button !== 0) return;
			dragging = true;
			el.classList.add('is-dragging');
			try { el.setPointerCapture(e.pointerId); } catch (err) {}
			setPos(posFromEvent(e));
			if (knob) knob.focus({ preventScroll: true });
		});

		el.addEventListener('pointermove', function (e) {
			if (dragging || (hoverMode && e.pointerType === 'mouse')) {
				setPos(posFromEvent(e));
			}
		});

		function stop(e) {
			if (!dragging) return;
			dragging = false;
			el.classList.remove('is-dragging');
			try { el.releasePointerCapture(e.pointerId); } catch (err) {}
		}
		el.addEventListener('pointerup', stop);
		el.addEventListener('pointercancel', stop);

		// Empêche le glisser-déposer natif des images
		el.addEventListener('dragstart', function (e) { e.preventDefault(); });

		if (knob) {
			knob.addEventListener('keydown', function (e) {
				var step = e.shiftKey ? 10 : 1;
				switch (e.key) {
					case 'ArrowLeft':
					case 'ArrowUp':
						setPos(pos - step); break;
					case 'ArrowRight':
					case 'ArrowDown':
						setPos(pos + step); break;
					case 'PageUp':
						setPos(pos - 10); break;
					case 'PageDown':
						setPos(pos + 10); break;
					case 'Home':
						setPos(0); break;
					case 'End':
						setPos(100); break;
					default:
						return;
				}
				e.preventDefault();
			});
		}

		render();
	}

	function init(root) {
		(root || document).querySelectorAll('.wptn-slider:not(.wptn-editor-preview)').forEach(initSlider);
	}

	window.WPThenNowSlider = { init: init };

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(); });
	} else {
		init();
	}
})();
