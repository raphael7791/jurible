import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { citation, source } = attributes;
	const blockProps = useBlockProps.save();

	if (!citation) {
		return null;
	}

	return (
		<div {...blockProps}>
			<div className="jurible-citation">
				<RichText.Content tagName="p" className="jurible-citation-text" value={citation} />
				{source && (
					<RichText.Content tagName="span" className="jurible-citation-source" value={source} />
				)}
			</div>
		</div>
	);
}