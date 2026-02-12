/**
 * Frontend interactivity for Method Tabs block
 */
document.addEventListener('DOMContentLoaded', function () {
	const methodTabsBlocks = document.querySelectorAll('.wp-block-jurible-method-tabs');

	methodTabsBlocks.forEach((block) => {
		const tabs = block.querySelectorAll('.method-tabs__tab');
		const panels = block.querySelectorAll('.method-tabs__panel');

		tabs.forEach((tab) => {
			tab.addEventListener('click', function () {
				const tabIndex = this.getAttribute('data-tab-index');

				// Update tabs
				tabs.forEach((t) => {
					t.classList.remove('is-active');
					t.setAttribute('aria-selected', 'false');
				});
				this.classList.add('is-active');
				this.setAttribute('aria-selected', 'true');

				// Update panels
				panels.forEach((panel) => {
					const panelIndex = panel.getAttribute('data-panel-index');
					if (panelIndex === tabIndex) {
						panel.classList.add('is-active');
						panel.setAttribute('aria-hidden', 'false');
					} else {
						panel.classList.remove('is-active');
						panel.setAttribute('aria-hidden', 'true');
					}
				});
			});

			// Keyboard navigation
			tab.addEventListener('keydown', function (e) {
				const currentIndex = parseInt(this.getAttribute('data-tab-index'));
				let newIndex;

				switch (e.key) {
					case 'ArrowRight':
						newIndex = currentIndex + 1;
						if (newIndex >= tabs.length) newIndex = 0;
						tabs[newIndex].focus();
						tabs[newIndex].click();
						e.preventDefault();
						break;
					case 'ArrowLeft':
						newIndex = currentIndex - 1;
						if (newIndex < 0) newIndex = tabs.length - 1;
						tabs[newIndex].focus();
						tabs[newIndex].click();
						e.preventDefault();
						break;
					case 'Home':
						tabs[0].focus();
						tabs[0].click();
						e.preventDefault();
						break;
					case 'End':
						tabs[tabs.length - 1].focus();
						tabs[tabs.length - 1].click();
						e.preventDefault();
						break;
				}
			});
		});
	});
});
