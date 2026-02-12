import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: `solution-card solution-card--${attributes.layout}`
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Configuration" initialOpen={true}>
					<SelectControl
						label="Layout"
						value={attributes.layout}
						options={[
							{ label: 'Horizontal (icône à gauche)', value: 'horizontal' },
							{ label: 'Centré (icône en haut)', value: 'centered' }
						]}
						onChange={(value) => setAttributes({ layout: value })}
					/>
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
			</InspectorControls>

			<div {...blockProps}>
				<div className="solution-card__icon">{attributes.icon}</div>
				<div className="solution-card__content">
					<h4 className="solution-card__title">{attributes.title}</h4>
					<p className="solution-card__description">{attributes.description}</p>
				</div>
			</div>
		</>
	);
}
