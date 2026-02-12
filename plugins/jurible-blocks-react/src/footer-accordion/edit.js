import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import './editor.scss';

const ALLOWED_BLOCKS = ['core/list', 'core/paragraph', 'core/navigation', 'jurible/bouton'];

export default function Edit({ attributes, setAttributes }) {
	const { title, isOpenByDefault } = attributes;
	const blockProps = useBlockProps({
		className: `jurible-footer-accordion ${isOpenByDefault ? 'jurible-footer-accordion--open' : ''}`
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Paramètres Accordéon">
					<TextControl
						label="Titre de la section"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<ToggleControl
						label="Ouvert par défaut"
						checked={isOpenByDefault}
						onChange={(value) => setAttributes({ isOpenByDefault: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="jurible-footer-accordion__header">
					<span className="jurible-footer-accordion__title">{title}</span>
					<span className="jurible-footer-accordion__icon">▼</span>
				</div>
				<div className="jurible-footer-accordion__content">
					<InnerBlocks
						allowedBlocks={ALLOWED_BLOCKS}
						template={[['core/list', {}]]}
					/>
				</div>
			</div>
		</>
	);
}
