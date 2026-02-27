/**
 * Hero Dashboard - Frontend JavaScript
 * Optimized: throttled events, requestAnimationFrame, GPU-accelerated transforms
 */

(function () {
	'use strict';

	// Throttle utility
	function throttle(fn, wait) {
		let lastTime = 0;
		return function (...args) {
			const now = Date.now();
			if (now - lastTime >= wait) {
				lastTime = now;
				fn.apply(this, args);
			}
		};
	}

	// Animate counter with easing
	function animateCounter(element, start, end, suffix = '', duration = 1200) {
		const startTime = performance.now();

		function update(currentTime) {
			const elapsed = currentTime - startTime;
			const progress = Math.min(elapsed / duration, 1);
			// Ease out cubic
			const eased = 1 - Math.pow(1 - progress, 3);
			element.textContent = Math.round(start + (end - start) * eased) + suffix;
			if (progress < 1) {
				requestAnimationFrame(update);
			}
		}

		requestAnimationFrame(update);
	}

	// Initialize a single dashboard instance
	function initDashboard(container) {
		const wrapper = container.querySelector('.hero-dashboard__wrapper');
		const floatCards = container.querySelectorAll('.hero-dashboard__float');
		const hasParallax = container.dataset.parallax === 'true';
		const hasAnimations = container.classList.contains('has-animations');

		// Parallax effect on mouse move (desktop only)
		if (hasParallax && window.matchMedia('(min-width: 641px)').matches) {
			const handleMouseMove = throttle(function (e) {
				const rect = container.getBoundingClientRect();
				const centerX = rect.left + rect.width / 2;
				const centerY = rect.top + rect.height / 2;

				// Calculate relative position (-1 to 1)
				const x = (e.clientX - centerX) / (window.innerWidth / 2);
				const y = (e.clientY - centerY) / (window.innerHeight / 2);

				// Apply 3D rotation to wrapper
				if (wrapper) {
					wrapper.style.transform = `rotateY(${-4 + x * 3}deg) rotateX(${2 + y * -2}deg)`;
				}

				// Apply parallax to floating cards
				floatCards.forEach((card) => {
					const speed = parseFloat(card.dataset.parallaxSpeed) || 0.02;
					const translateX = x * speed * 100;
					const translateY = y * speed * 80;
					card.style.transform = `translate(${translateX}px, ${translateY}px)`;
				});
			}, 16); // ~60fps

			document.addEventListener('mousemove', handleMouseMove, { passive: true });
		}

		// Counter animations on intersection
		if (hasAnimations) {
			const counters = container.querySelectorAll('[data-counter]');
			const progressBar = container.querySelector('.hero-dashboard__progress-fill');

			if (counters.length > 0 || progressBar) {
				const observer = new IntersectionObserver(
					(entries) => {
						entries.forEach((entry) => {
							if (entry.isIntersecting) {
								// Animate counters with staggered delays
								counters.forEach((counter, index) => {
									const target = parseInt(counter.dataset.counter, 10);
									const suffix = counter.dataset.suffix || '';
									setTimeout(() => {
										animateCounter(counter, 0, target, suffix);
									}, 1000 + index * 100);
								});

								// Progress bar animation is handled by CSS transition
								// Just ensure width is set
								if (progressBar) {
									const progress = container.dataset.progress || 67;
									setTimeout(() => {
										progressBar.style.width = progress + '%';
									}, 1500);
								}

								observer.disconnect();
							}
						});
					},
					{ threshold: 0.3 }
				);

				observer.observe(container);
			}
		}

		// Fade floating cards on scroll
		if (floatCards.length > 0 && hasAnimations) {
			const handleScroll = throttle(function () {
				const rect = container.getBoundingClientRect();
				const viewportHeight = window.innerHeight;

				// Start fading when container top reaches 30% from top
				const fadeStart = viewportHeight * 0.3;
				const fadeEnd = viewportHeight * 0.1;

				if (rect.top < fadeStart && rect.top > -rect.height) {
					const progress = Math.max(0, (fadeStart - rect.top) / (fadeStart - fadeEnd));
					const opacity = Math.max(0, 1 - progress * 0.5);

					floatCards.forEach((card) => {
						card.style.opacity = opacity;
					});
				}
			}, 16);

			window.addEventListener('scroll', handleScroll, { passive: true });
		}
	}

	// Initialize all dashboard instances on DOM ready
	function init() {
		const dashboards = document.querySelectorAll('.hero-dashboard');
		dashboards.forEach(initDashboard);
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
