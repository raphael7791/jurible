import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: `cta-banner cta-banner--${attributes.variant}`
	});

	// Classe du bouton selon la variante
	const buttonClass = attributes.variant === 'gradient'
		? 'cta-banner__btn cta-banner__btn--white'
		: 'cta-banner__btn cta-banner__btn--primary';

	return (
		<>
			<InspectorControls>
				<PanelBody title="Style" initialOpen={true}>
					<SelectControl
						label="Variante"
						value={attributes.variant}
						options={[
							{ label: 'Gradient (fond dégradé)', value: 'gradient' },
							{ label: 'Blanc (fond clair)', value: 'white' },
							{ label: 'Noir (fond sombre)', value: 'dark' }
						]}
						onChange={(value) => setAttributes({ variant: value })}
					/>
				</PanelBody>
				<PanelBody title="Contenu" initialOpen={true}>
					<TextControl
						label="Icône (emoji)"
						value={attributes.icon}
						onChange={(value) => setAttributes({ icon: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<TextareaControl
						label="Description"
						value={attributes.description}
						onChange={(value) => setAttributes({ description: value })}
					/>
				</PanelBody>
				<PanelBody title="Bouton" initialOpen={true}>
					<TextControl
						label="Texte du bouton"
						value={attributes.buttonText}
						onChange={(value) => setAttributes({ buttonText: value })}
					/>
					<TextControl
						label="URL du bouton"
						value={attributes.buttonUrl}
						onChange={(value) => setAttributes({ buttonUrl: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="cta-banner__icon">{attributes.icon}</div>
				<div className="cta-banner__content">
					<p className="cta-banner__title">{attributes.title}</p>
					<p className="cta-banner__desc">{attributes.description}</p>
				</div>
				<div className="cta-banner__action">
					<span className={buttonClass}>{attributes.buttonText}</span>
				</div>
			</div>
		</>
	);
}
