import { useBlockProps, RichText } from '@wordpress/block-editor';

const infoboxConfig = {
	retenir: { icon: '🌟' },
	attention: { icon: '⚠️' },
	exemple: { icon: '💡' },
	important: { icon: '📌' },
	astuce: { icon: '🎯' },
	definition: { icon: '📖' },
	conditions: { icon: '📌', title: 'Conditions' }
};

export default function save({ attributes }) {
	const { type, title, content } = attributes;
	const blockProps = useBlockProps.save({ className: `jurible-infobox jurible-infobox-${type}` });
	const config = infoboxConfig[type] || infoboxConfig.retenir;

	return (
		<div {...blockProps}>
			<div className="jurible-infobox-header">
				<span className="jurible-infobox-icon">{config.icon}</span>
				<RichText.Content tagName="span" className="jurible-infobox-title" value={title} />
			</div>
			<RichText.Content tagName="p" className="jurible-infobox-content" value={content} />
		</div>
	);
}