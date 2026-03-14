document.addEventListener('DOMContentLoaded', function () {
	const blocks = document.querySelectorAll('.jurible-contact-form');

	blocks.forEach(function (block) {
		const form = block.querySelector('.jurible-contact-form__form');
		const statusEl = block.querySelector('.jurible-contact-form__status');
		const submitBtn = block.querySelector('.jurible-contact-form__btn');
		const successMessage = block.dataset.successMessage;
		const recipient = block.dataset.recipient;

		if (!form) return;

		form.addEventListener('submit', function (e) {
			e.preventDefault();

			// Clear previous status
			statusEl.className = 'jurible-contact-form__status';
			statusEl.textContent = '';

			// Get form data
			const firstName = form.querySelector('input[name="firstName"]').value.trim();
			const lastName = form.querySelector('input[name="lastName"]').value.trim();
			const email = form.querySelector('input[name="email"]').value.trim();
			const subject = form.querySelector('select[name="subject"]').value;
			const message = form.querySelector('textarea[name="message"]').value.trim();
			const website = form.querySelector('input[name="website"]').value;
			// Client-side validation
			if (!firstName || !lastName || !email || !message) {
				statusEl.className = 'jurible-contact-form__status jurible-contact-form__status--error';
				statusEl.textContent = 'Veuillez remplir tous les champs obligatoires.';
				return;
			}

			if (!isValidEmail(email)) {
				statusEl.className = 'jurible-contact-form__status jurible-contact-form__status--error';
				statusEl.textContent = 'Veuillez entrer une adresse email valide.';
				return;
			}

			// Loading state
			submitBtn.disabled = true;
			submitBtn.dataset.originalText = submitBtn.textContent;
			submitBtn.textContent = 'Envoi en cours...';

			// Send AJAX request
			fetch('/wp-json/jurible/v1/contact', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					firstName: firstName,
					lastName: lastName,
					email: email,
					subject: subject,
					message: message,
					website: website,
					recipient: recipient,
				}),
			})
				.then(function (response) {
					return response.json().then(function (data) {
						return { ok: response.ok, data: data };
					});
				})
				.then(function (result) {
					if (result.ok) {
						statusEl.className = 'jurible-contact-form__status jurible-contact-form__status--success';
						statusEl.textContent = successMessage;
						form.reset();
					} else {
						statusEl.className = 'jurible-contact-form__status jurible-contact-form__status--error';
						statusEl.textContent = result.data.message || 'Une erreur est survenue. Veuillez réessayer.';
					}
				})
				.catch(function () {
					statusEl.className = 'jurible-contact-form__status jurible-contact-form__status--error';
					statusEl.textContent = 'Une erreur est survenue. Veuillez réessayer.';
				})
				.finally(function () {
					submitBtn.disabled = false;
					submitBtn.textContent = submitBtn.dataset.originalText;
				});
		});
	});

	function isValidEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}
});
