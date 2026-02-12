import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

const infoboxTypes = [
	{ label: 'À retenir', value: 'retenir' },
	{ label: 'Attention', value: 'attention' },
	{ label: 'Exemple', value: 'exemple' },
	{ label: 'Important', value: 'important' },
	{ label: 'Astuce', value: 'astuce' },
	{ label: 'Définition', value: 'definition' },
	{ label: 'Conditions', value: 'conditions' }
];

const infoboxConfig = {
	retenir: { icon: '🌟', title: 'À retenir' },
	attention: { icon: '⚠️', title: 'Attention' },
	exemple: { icon: '💡', title: 'Exemple' },
	important: { icon: '📌', title: 'Important' },
	astuce: { icon: '🎯', title: 'Astuce' },
	definition: { icon: '📖', title: 'Définition' },
	conditions: { icon: '📌', title: 'Conditions' }
};

export default function Edit({ attributes, setAttributes }) {
	const { type, title, content } = attributes;
	const blockProps = useBlockProps({ className: `jurible-infobox jurible-infobox-${type}` });
	const config = infoboxConfig[type] || infoboxConfig.retenir;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Type d'infobox">
					<SelectControl
						label="Style"
						value={type}
						options={infoboxTypes}
						onChange={(value) => setAttributes({ 
							type: value,
							title: infoboxConfig[value].title
						})}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="jurible-infobox-header">
					<span className="jurible-infobox-icon">{config.icon}</span>
					<RichText
						tagName="span"
						className="jurible-infobox-title"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						placeholder="Titre..."
					/>
				</div>
				<RichText
					tagName="p"
					className="jurible-infobox-content"
					value={content}
					onChange={(value) => setAttributes({ content: value })}
					placeholder="Contenu de l'infobox..."
				/>
			</div>
		</>
	);
}