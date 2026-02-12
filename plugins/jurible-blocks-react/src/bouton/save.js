import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { text, url, style } = attributes;
	const blockProps = useBlockProps.save();

	if (!url || !text) {
		return null;
	}

	return (
		<div {...blockProps}>
			<a 
				href={url} 
				className={`jurible-bouton jurible-bouton-${style}`}
			>
				{text}
			</a>
		</div>
	);
}