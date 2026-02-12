import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { text, url, style } = attributes;
	const blockProps = useBlockProps();

	const buttonClass = `jurible-bouton jurible-bouton-${style}`;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Paramètres du bouton">
					<TextControl
						label="Texte du bouton"
						value={text}
						onChange={(value) => setAttributes({ text: value })}
					/>
					<TextControl
						label="URL du lien"
						value={url}
						onChange={(value) => setAttributes({ url: value })}
						placeholder="https://..."
					/>
					<SelectControl
						label="Style"
						value={style}
						options={[
							{ label: 'Primary', value: 'primary' },
							{ label: 'Gradient', value: 'gradient' },
							{ label: 'Secondary', value: 'secondary' }
						]}
						onChange={(value) => setAttributes({ style: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className={buttonClass}>
					{text}
				</div>
			</div>
		</>
	);
}