import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { icon, label, stats, rating, score, reviewCount } = attributes;

	const blockProps = useBlockProps.save({
		className: 'checkout-social-proof'
	});

	const renderStars = () => {
		const stars = [];
		for (let i = 0; i < 5; i++) {
			stars.push(
				<span key={i} className={`checkout-social-proof__star ${i < rating ? 'checkout-social-proof__star--filled' : ''}`}>
					★
				</span>
			);
		}
		return stars;
	};

	return (
		<div {...blockProps}>
			<div className="checkout-social-proof__header">
				<span className="checkout-social-proof__icon">{icon}</span>
				<div className="checkout-social-proof__info">
					<div className="checkout-social-proof__label">{label}</div>
					<div className="checkout-social-proof__stats">{stats}</div>
				</div>
			</div>
			<div className="checkout-social-proof__rating">
				<span className="checkout-social-proof__stars">{renderStars()}</span>
				<span className="checkout-social-proof__score">{score}</span>
				<span className="checkout-social-proof__count">{reviewCount}</span>
			</div>
		</div>
	);
}
