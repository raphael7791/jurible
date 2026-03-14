import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { recipientEmail, description, buttonText, successMessage, subjects } = attributes;
	const subjectList = subjects ? subjects.split(',').map((s) => s.trim()) : [];
	const blockProps = useBlockProps({
		className: 'jurible-contact-form'
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Paramètres du formulaire">
					<TextControl
						label="Email destinataire"
						help="Adresse email qui recevra les messages. Si vide, l'email admin du site sera utilisé."
						value={recipientEmail}
						onChange={(value) => setAttributes({ recipientEmail: value })}
						type="email"
					/>
					<TextareaControl
						label="Description"
						value={description}
						onChange={(value) => setAttributes({ description: value })}
					/>
					<TextControl
						label="Texte du bouton"
						value={buttonText}
						onChange={(value) => setAttributes({ buttonText: value })}
					/>
					<TextareaControl
						label="Message de succès"
						value={successMessage}
						onChange={(value) => setAttributes({ successMessage: value })}
					/>
					<TextareaControl
						label="Sujets (séparés par des virgules)"
						help="Liste des sujets disponibles dans le menu déroulant."
						value={subjects}
						onChange={(value) => setAttributes({ subjects: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{description && <p className="jurible-contact-form__description">{description}</p>}
				<form className="jurible-contact-form__form">
					<div className="jurible-contact-form__row">
						<div className="jurible-contact-form__field">
							<label className="jurible-contact-form__label">
								Prénom <span className="jurible-contact-form__required">*</span>
							</label>
							<input
								type="text"
								className="jurible-contact-form__input"
								placeholder="Jean"
								disabled
							/>
						</div>
						<div className="jurible-contact-form__field">
							<label className="jurible-contact-form__label">
								Nom <span className="jurible-contact-form__required">*</span>
							</label>
							<input
								type="text"
								className="jurible-contact-form__input"
								placeholder="Dupont"
								disabled
							/>
						</div>
					</div>
					<div className="jurible-contact-form__field">
						<label className="jurible-contact-form__label">
							Email <span className="jurible-contact-form__required">*</span>
						</label>
						<input
							type="email"
							className="jurible-contact-form__input"
							placeholder="jean.dupont@email.com"
							disabled
						/>
					</div>
					<div className="jurible-contact-form__field">
						<label className="jurible-contact-form__label">Sujet</label>
						<select className="jurible-contact-form__select" disabled>
							<option>Choisir un sujet...</option>
							{subjectList.map((subject, index) => (
								<option key={index}>{subject}</option>
							))}
						</select>
					</div>
					<div className="jurible-contact-form__field">
						<label className="jurible-contact-form__label">
							Message <span className="jurible-contact-form__required">*</span>
						</label>
						<textarea
							className="jurible-contact-form__textarea"
							placeholder="Décrivez votre demande..."
							rows="5"
							disabled
						/>
					</div>
					<button type="button" className="jurible-contact-form__btn">
						{buttonText}
					</button>
				</form>
			</div>
		</>
	);
}
