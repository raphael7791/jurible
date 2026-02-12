import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { title, isOpenByDefault } = attributes;
	const blockProps = useBlockProps.save({
		className: `jurible-footer-accordion ${isOpenByDefault ? 'jurible-footer-accordion--open' : ''}`,
		'data-open': isOpenByDefault ? 'true' : 'false'
	});

	return (
		<div {...blockProps}>
			<button
				className="jurible-footer-accordion__header"
				aria-expanded={isOpenByDefault ? 'true' : 'false'}
				type="button"
			>
				<span className="jurible-footer-accordion__title">{title}</span>
				<span className="jurible-footer-accordion__icon">▼</span>
			</button>
			<div className="jurible-footer-accordion__content">
				<InnerBlocks.Content />
			</div>
		</div>
	);
}
