document.addEventListener('DOMContentLoaded', function() {
	const closeButtons = document.querySelectorAll('.jurible-alert-close');

	closeButtons.forEach(function(button) {
		button.addEventListener('click', function() {
			const alert = this.closest('.jurible-alert');
			if (alert) {
				alert.style.opacity = '0';
				alert.style.transform = 'translateY(-10px)';
				setTimeout(function() {
					alert.style.display = 'none';
				}, 150);
			}
		});
	});
});
