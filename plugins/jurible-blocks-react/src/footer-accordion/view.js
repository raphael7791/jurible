/**
 * Footer Accordion - Frontend JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
	const accordions = document.querySelectorAll('.jurible-footer-accordion');

	accordions.forEach(accordion => {
		const header = accordion.querySelector('.jurible-footer-accordion__header');
		const content = accordion.querySelector('.jurible-footer-accordion__content');

		if (header && content) {
			header.addEventListener('click', function() {
				const isOpen = accordion.classList.contains('jurible-footer-accordion--open');

				if (isOpen) {
					accordion.classList.remove('jurible-footer-accordion--open');
					header.setAttribute('aria-expanded', 'false');
				} else {
					accordion.classList.add('jurible-footer-accordion--open');
					header.setAttribute('aria-expanded', 'true');
				}
			});
		}
	});
});
