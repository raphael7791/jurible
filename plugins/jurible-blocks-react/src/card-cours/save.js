import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const {
		imageUrl,
		badgeIcon,
		showBadge,
		title,
		videosCount,
		fichesCount,
		qcmCount,
		flashcardsCount,
		annalesCount,
		mindmapsCount,
		showIncludedLabel,
		includedLabelText,
		linkText,
		linkUrl
	} = attributes;

	const blockProps = useBlockProps.save({ className: 'card-cours' });

	const imageStyle = imageUrl
		? { backgroundImage: `url(${imageUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' }
		: {};

	return (
		<a href={linkUrl} {...blockProps}>
			<div className="card-cours__image" style={imageStyle}>
				{!imageUrl && <span className="card-cours__image-icon">🎬</span>}
				{showBadge && badgeIcon && (
					<span className="card-cours__badge">{badgeIcon}</span>
				)}
			</div>
			<div className="card-cours__content">
				<RichText.Content
					tagName="h3"
					className="card-cours__title"
					value={title}
				/>
				<div className="card-cours__stats">
					{videosCount > 0 && (
						<span className="card-cours__stat">
							<span className="card-cours__stat-icon">🎬</span>
							{videosCount} videos
						</span>
					)}
					{fichesCount > 0 && (
						<span className="card-cours__stat">
							<span className="card-cours__stat-icon">📄</span>
							{fichesCount} fiches
						</span>
					)}
					{qcmCount > 0 && (
						<span className="card-cours__stat">
							<span className="card-cours__stat-icon">❓</span>
							{qcmCount} QCM
						</span>
					)}
					{flashcardsCount > 0 && (
						<span className="card-cours__stat">
							<span className="card-cours__stat-icon">🎴</span>
							{flashcardsCount} flashcards
						</span>
					)}
					{annalesCount > 0 && (
						<span className="card-cours__stat">
							<span className="card-cours__stat-icon">📝</span>
							{annalesCount} annales
						</span>
					)}
					{mindmapsCount > 0 && (
						<span className="card-cours__stat">
							<span className="card-cours__stat-icon">🧠</span>
							{mindmapsCount} mindmaps
						</span>
					)}
				</div>
				{showIncludedLabel && (
					<div className="card-cours__included">
						✓ {includedLabelText}
					</div>
				)}
				<div className="card-cours__footer">
					<span className="card-cours__link">
						<RichText.Content tagName="span" value={linkText} /> →
					</span>
				</div>
			</div>
		</a>
	);
}
