import { useBlockProps, RichText } from '@wordpress/block-editor';

const alertConfig = {
	info: { icon: 'i' },
	success: { icon: '✓' },
	warning: { icon: '!' },
	error: { icon: '✕' },
	accent: { icon: '★' }
};

export default function save({ attributes }) {
	const { type, variant, title, description, primaryButtonText, primaryButtonUrl, secondaryButtonText, secondaryButtonUrl, showClose } = attributes;
	const blockProps = useBlockProps.save({ className: `jurible-alert jurible-alert--${type} ${variant === 'inline' ? 'jurible-alert--inline' : ''}` });
	const config = alertConfig[type] || alertConfig.info;

	return (
		<div {...blockProps}>
			<span className="jurible-alert-icon">{config.icon}</span>
			<div className="jurible-alert-content">
				<RichText.Content tagName="p" className="jurible-alert-title" value={title} />
				{variant === 'full' && description && (
					<RichText.Content tagName="p" className="jurible-alert-description" value={description} />
				)}
				{variant === 'full' && (primaryButtonText || secondaryButtonText) && (
					<div className="jurible-alert-actions">
						{primaryButtonText && primaryButtonUrl && (
							<a href={primaryButtonUrl} className="jurible-alert-btn jurible-alert-btn--primary">{primaryButtonText}</a>
						)}
						{secondaryButtonText && secondaryButtonUrl && (
							<a href={secondaryButtonUrl} className="jurible-alert-btn jurible-alert-btn--secondary">{secondaryButtonText}</a>
						)}
					</div>
				)}
			</div>
			{showClose && (
				<button className="jurible-alert-close" aria-label="Fermer">✕</button>
			)}
		</div>
	);
}
