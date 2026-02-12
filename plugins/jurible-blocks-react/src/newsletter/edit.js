import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { title, description, placeholder, buttonText, variant, layout } = attributes;
	const blockProps = useBlockProps({
		className: `jurible-newsletter jurible-newsletter--${variant} jurible-newsletter--${layout}`
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Paramètres Newsletter">
					<TextControl
						label="Titre"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<TextControl
						label="Description"
						value={description}
						onChange={(value) => setAttributes({ description: value })}
					/>
					<TextControl
						label="Placeholder de l'input"
						value={placeholder}
						onChange={(value) => setAttributes({ placeholder: value })}
					/>
					<TextControl
						label="Texte du bouton"
						value={buttonText}
						onChange={(value) => setAttributes({ buttonText: value })}
					/>
					<SelectControl
						label="Variante"
						value={variant}
						options={[
							{ label: 'Sombre (footer)', value: 'dark' },
							{ label: 'Clair', value: 'light' }
						]}
						onChange={(value) => setAttributes({ variant: value })}
					/>
					<SelectControl
						label="Disposition"
						value={layout}
						options={[
							{ label: 'Horizontal', value: 'horizontal' },
							{ label: 'Vertical (mobile)', value: 'vertical' }
						]}
						onChange={(value) => setAttributes({ layout: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{title && <h4 className="jurible-newsletter__title">{title}</h4>}
				{description && <p className="jurible-newsletter__description">{description}</p>}
				<form className="jurible-newsletter__form">
					<input
						type="email"
						className="jurible-newsletter__input"
						placeholder={placeholder}
						disabled
					/>
					<button type="button" className="jurible-newsletter__btn">
						{buttonText}
					</button>
				</form>
			</div>
		</>
	);
}
