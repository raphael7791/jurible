import { useBlockProps, RichText } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { citation, source } = attributes;
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<div className="jurible-citation">
				<RichText
					tagName="p"
					className="jurible-citation-text"
					value={citation}
					onChange={(value) => setAttributes({ citation: value })}
					placeholder="Saisissez votre citation..."
				/>
				<RichText
					tagName="span"
					className="jurible-citation-source"
					value={source}
					onChange={(value) => setAttributes({ source: value })}
					placeholder="Source (optionnelle)"
				/>
			</div>
		</div>
	);
}