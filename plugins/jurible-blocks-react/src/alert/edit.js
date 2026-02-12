import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';

const alertTypes = [
	{ label: 'Info', value: 'info' },
	{ label: 'Success', value: 'success' },
	{ label: 'Warning', value: 'warning' },
	{ label: 'Error', value: 'error' },
	{ label: 'Accent', value: 'accent' }
];

const alertVariants = [
	{ label: 'Full (avec boutons)', value: 'full' },
	{ label: 'Inline (compact)', value: 'inline' }
];

const alertConfig = {
	info: { title: 'Information', icon: 'i' },
	success: { title: 'Succes', icon: '✓' },
	warning: { title: 'Attention', icon: '!' },
	error: { title: 'Erreur', icon: '✕' },
	accent: { title: 'Nouveau', icon: '★' }
};

export default function Edit({ attributes, setAttributes }) {
	const { type, variant, title, description, primaryButtonText, primaryButtonUrl, secondaryButtonText, secondaryButtonUrl, showClose } = attributes;
	const blockProps = useBlockProps({ className: `jurible-alert jurible-alert--${type} ${variant === 'inline' ? 'jurible-alert--inline' : ''}` });
	const config = alertConfig[type] || alertConfig.info;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Type d'alerte">
					<SelectControl
						label="Type"
						value={type}
						options={alertTypes}
						onChange={(value) => setAttributes({
							type: value,
							title: alertConfig[value].title
						})}
					/>
					<SelectControl
						label="Variante"
						value={variant}
						options={alertVariants}
						onChange={(value) => setAttributes({ variant: value })}
					/>
					<ToggleControl
						label="Afficher le bouton fermer"
						checked={showClose}
						onChange={(value) => setAttributes({ showClose: value })}
					/>
				</PanelBody>
				{variant === 'full' && (
					<PanelBody title="Boutons" initialOpen={false}>
						<TextControl
							label="Texte bouton principal"
							value={primaryButtonText}
							onChange={(value) => setAttributes({ primaryButtonText: value })}
							placeholder="Découvrir"
						/>
						<TextControl
							label="URL bouton principal"
							value={primaryButtonUrl}
							onChange={(value) => setAttributes({ primaryButtonUrl: value })}
							placeholder="https://..."
						/>
						<TextControl
							label="Texte bouton secondaire"
							value={secondaryButtonText}
							onChange={(value) => setAttributes({ secondaryButtonText: value })}
							placeholder="En savoir plus"
						/>
						<TextControl
							label="URL bouton secondaire"
							value={secondaryButtonUrl}
							onChange={(value) => setAttributes({ secondaryButtonUrl: value })}
							placeholder="https://..."
						/>
					</PanelBody>
				)}
			</InspectorControls>
			<div {...blockProps}>
				<span className="jurible-alert-icon">{config.icon}</span>
				<div className="jurible-alert-content">
					<RichText
						tagName="p"
						className="jurible-alert-title"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						placeholder="Titre de l'alerte..."
					/>
					{variant === 'full' && (
						<>
							<RichText
								tagName="p"
								className="jurible-alert-description"
								value={description}
								onChange={(value) => setAttributes({ description: value })}
								placeholder="Description de l'alerte..."
							/>
							<div className="jurible-alert-actions">
								<RichText
									tagName="span"
									className="jurible-alert-btn jurible-alert-btn--primary"
									value={primaryButtonText}
									onChange={(value) => setAttributes({ primaryButtonText: value })}
									placeholder="Bouton principal"
								/>
								<RichText
									tagName="span"
									className="jurible-alert-btn jurible-alert-btn--secondary"
									value={secondaryButtonText}
									onChange={(value) => setAttributes({ secondaryButtonText: value })}
									placeholder="Bouton secondaire"
								/>
							</div>
						</>
					)}
				</div>
				{showClose && (
					<span className="jurible-alert-close">✕</span>
				)}
			</div>
		</>
	);
}
