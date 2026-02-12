/**
 * Countdown Timer for Card Formule Réussite
 */
document.addEventListener('DOMContentLoaded', function() {
	const countdownWrappers = document.querySelectorAll('.card-reussite__countdown-wrapper[data-countdown-date]');

	countdownWrappers.forEach(function(wrapper) {
		const targetDate = new Date(wrapper.dataset.countdownDate).getTime();
		const daysEl = wrapper.querySelector('[data-countdown-days]');
		const hoursEl = wrapper.querySelector('[data-countdown-hours]');
		const minutesEl = wrapper.querySelector('[data-countdown-minutes]');
		const secondsEl = wrapper.querySelector('[data-countdown-seconds]');

		if (!daysEl || !hoursEl || !minutesEl || !secondsEl) {
			return;
		}

		function updateCountdown() {
			const now = new Date().getTime();
			const distance = targetDate - now;

			if (distance < 0) {
				daysEl.textContent = '0';
				hoursEl.textContent = '0';
				minutesEl.textContent = '0';
				secondsEl.textContent = '0';
				return;
			}

			const days = Math.floor(distance / (1000 * 60 * 60 * 24));
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((distance % (1000 * 60)) / 1000);

			daysEl.textContent = days;
			hoursEl.textContent = hours.toString().padStart(2, '0');
			minutesEl.textContent = minutes.toString().padStart(2, '0');
			secondsEl.textContent = seconds.toString().padStart(2, '0');
		}

		// Initial update
		updateCountdown();

		// Update every second
		setInterval(updateCountdown, 1000);
	});
});
