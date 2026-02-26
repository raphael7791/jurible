/**
 * Frontend interactivity for Method Tabs block
 */
document.addEventListener('DOMContentLoaded', function () {
	const methodTabsBlocks = document.querySelectorAll('.wp-block-jurible-method-tabs');

	methodTabsBlocks.forEach((block) => {
		initTabs(block);
		initQCM(block);
		initFlashcards(block);
	});
});

/**
 * Initialize tab switching
 */
function initTabs(block) {
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
}

/**
 * Initialize QCM interactivity
 */
function initQCM(block) {
	const qcmOptions = block.querySelectorAll('.qcm-option');

	qcmOptions.forEach((option) => {
		option.addEventListener('click', function () {
			// Check if already answered
			const qcmContainer = this.closest('.method-tabs__qcm');
			if (qcmContainer && qcmContainer.classList.contains('is-answered')) {
				return;
			}

			// Mark as answered
			if (qcmContainer) {
				qcmContainer.classList.add('is-answered');
			}

			// Get all options in this question
			const allOptions = this.parentElement.querySelectorAll('.qcm-option');

			// Disable all options and show correct/incorrect states
			allOptions.forEach((opt) => {
				opt.classList.add('is-disabled');

				if (opt.getAttribute('data-correct') === 'true') {
					opt.classList.add('is-correct');
				} else if (opt === this && opt.getAttribute('data-correct') !== 'true') {
					opt.classList.add('is-incorrect');
				}
			});

			// Show feedback
			const feedback = qcmContainer ? qcmContainer.querySelector('.qcm-feedback') : null;
			if (feedback) {
				const isCorrect = this.getAttribute('data-correct') === 'true';
				feedback.classList.add('is-visible');
				feedback.classList.add(isCorrect ? 'is-correct' : 'is-incorrect');
				feedback.textContent = isCorrect
					? 'Bonne réponse !'
					: 'Mauvaise réponse. La bonne réponse est indiquée en vert.';
			}
		});
	});
}

/**
 * Initialize Flashcard flip
 */
function initFlashcards(block) {
	const flashcards = block.querySelectorAll('.method-tabs__fc-card');

	flashcards.forEach((card) => {
		card.addEventListener('click', function () {
			this.classList.toggle('is-flipped');

			// Show action buttons when flipped
			const actions = block.querySelector('.method-tabs__fc-actions');
			if (actions) {
				if (this.classList.contains('is-flipped')) {
					actions.classList.add('is-visible');
				} else {
					actions.classList.remove('is-visible');
				}
			}
		});

		// Keyboard support
		card.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				this.click();
			}
		});
	});
}
