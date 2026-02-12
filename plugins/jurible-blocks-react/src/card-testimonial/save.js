import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const {
		variant,
		rating,
		quote,
		showBadge,
		badgeText,
		avatarType,
		avatarUrl,
		avatarInitials,
		authorName,
		authorSubtitle
	} = attributes;

	const blockProps = useBlockProps.save({
		className: `card-testimonial card-testimonial--${variant}`
	});

	const renderStars = () => {
		const stars = [];
		for (let i = 0; i < 5; i++) {
			stars.push(
				<span key={i} className={`card-testimonial__star ${i < rating ? 'card-testimonial__star--filled' : ''}`}>
					★
				</span>
			);
		}
		return stars;
	};

	const renderAvatar = () => {
		if (avatarType === 'photo' && avatarUrl) {
			return (
				<div
					className={`card-testimonial__avatar card-testimonial__avatar--photo ${variant === 'hero' ? 'card-testimonial__avatar--lg' : ''}`}
					style={{ backgroundImage: `url(${avatarUrl})` }}
				/>
			);
		}
		return (
			<div className={`card-testimonial__avatar card-testimonial__avatar--initials ${variant === 'hero' ? 'card-testimonial__avatar--lg' : ''}`}>
				{avatarInitials}
			</div>
		);
	};

	return (
		<div {...blockProps}>
			<div className="card-testimonial__stars">
				{renderStars()}
			</div>
			<RichText.Content
				tagName="p"
				className="card-testimonial__quote"
				value={quote}
			/>
			{showBadge && (
				<div className="card-testimonial__badge">
					✓ {badgeText}
				</div>
			)}
			<div className="card-testimonial__author">
				{renderAvatar()}
				<div className="card-testimonial__author-info">
					<h4 className="card-testimonial__author-name">{authorName}</h4>
					<p className="card-testimonial__author-subtitle">{authorSubtitle}</p>
				</div>
			</div>
		</div>
	);
}
