import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const {
		variant,
		icon,
		title,
		subtitle,
		rating,
		reviewCount
	} = attributes;

	const blockProps = useBlockProps.save({
		className: `badge-trust ${variant === 'compact' ? 'badge-trust--compact' : ''}`
	});

	return (
		<div {...blockProps}>
			<div className="badge-trust__icon">{icon}</div>
			<div className="badge-trust__content">
				<h4 className="badge-trust__title">{title}</h4>
				{variant === 'full' && subtitle && (
					<p className="badge-trust__subtitle">{subtitle}</p>
				)}
			</div>
			<div className="badge-trust__rating">
				<div className="badge-trust__stars">
					<span className="badge-trust__star">★</span>
					<span className="badge-trust__star">★</span>
					<span className="badge-trust__star">★</span>
					<span className="badge-trust__star">★</span>
					<span className="badge-trust__star">★</span>
				</div>
				<span className="badge-trust__score">{rating}/5</span>
				{variant === 'full' && reviewCount && (
					<span className="badge-trust__count">({reviewCount})</span>
				)}
			</div>
		</div>
	);
}
