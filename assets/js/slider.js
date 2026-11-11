/* WP Then Now Slider – front-end */
(function () {
	'use strict';

	/* ---------- Plein écran ---------- */
	function initFullscreen(fig) {
		var btn = fig.querySelector('.wptn-fs-btn');
		if (!btn) return;

		var request = fig.requestFullscreen || fig.webkitRequestFullscreen;
		var native = false;

		function nativeElement() {
			return document.fullscreenElement || document.webkitFullscreenElement;
		}

		function setState(on, pseudo) {
			fig.classList.toggle('wptn-fs', on);
			document.documentElement.classList.toggle('wptn-fs-lock', on && pseudo);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			btn.setAttribute('aria-label', on ? btn.dataset.exit : btn.dataset.enter);
		}

		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			if (fig.classList.contains('wptn-fs')) {
				if (native) {
					(document.exitFullscreen || document.webkitExitFullscreen).call(document);
				} else {
					setState(false, true);
				}
				return;
			}
			if (request) {
				try {
					var p = request.call(fig);
					if (p && p.catch) p.catch(function () { setState(true, true); });
				} catch (err) {
					setState(true, true);
				}
			} else {
				// iPhone : pas d'API plein écran pour les éléments → plein écran simulé.
				setState(true, true);
			}
		});

		['fullscreenchange', 'webkitfullscreenchange'].forEach(function (ev) {
			document.addEventListener(ev, function () {
				if (nativeElement() === fig) {
					native = true;
					setState(true, false);
				} else if (native) {
					native = false;
					setState(false, false);
				}
			});
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !native && fig.classList.contains('wptn-fs')) {
				setState(false, true);
			}
		});
	}

	/* ---------- Mode « clic pour basculer » ---------- */
	function initToggle(el) {
		function toggle() {
			var on = !el.classList.contains('is-toggled');
			el.classList.toggle('is-toggled', on);
			el.setAttribute('aria-pressed', on ? 'true' : 'false');
		}
		el.addEventListener('click', function (e) {
			if (e.target.closest('.wptn-fs-btn, a')) return;
			toggle();
		});
		el.addEventListener('keydown', function (e) {
			if (e.target !== el) return;
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				toggle();
			}
		});
	}

	/* ---------- Modes « glisser » et « survol » ---------- */
	function initDrag(fig, el) {
		var vertical = fig.dataset.orientation === 'vertical';
		var hoverMode = fig.dataset.mode === 'hover';
		var knob = el.querySelector('.wptn-knob');
		var labelFirst = el.querySelector('.wptn-label-first');
		var labelSecond = el.querySelector('.wptn-label-second');
		var pos = parseFloat(getComputedStyle(fig).getPropertyValue('--wptn-pos')) || 50;
		var dragging = false;
		var frame = null;

		function render() {
			frame = null;
			fig.style.setProperty('--wptn-pos', pos + '%');
			if (knob) knob.setAttribute('aria-valuenow', Math.round(pos));
			// Masque une étiquette quand le curseur passe dessus
			if (labelFirst) labelFirst.classList.toggle('is-hidden', pos < 15);
			if (labelSecond) labelSecond.classList.toggle('is-hidden', pos > 85);
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
			if (e.target.closest('.wptn-fs-btn, a')) return;
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

	function initSlider(fig) {
		if (fig.dataset.wptnReady) return;
		fig.dataset.wptnReady = '1';

		var el = fig.querySelector('.wptn-slider');
		if (!el) return;

		// Empêche le glisser-déposer natif des images
		el.addEventListener('dragstart', function (e) { e.preventDefault(); });

		initFullscreen(fig);

		var mode = fig.dataset.mode || 'slider';
		if (mode === 'click') {
			initToggle(el);
		} else if (mode === 'slider' || mode === 'hover') {
			initDrag(fig, el);
		}
	}

	function init(root) {
		(root || document).querySelectorAll('figure.wptn').forEach(initSlider);
	}

	window.WPThenNowSlider = { init: init };

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(); });
	} else {
		init();
	}
})();
