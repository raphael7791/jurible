import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { rating, quote, authorName, authorRole } = attributes;

	const blockProps = useBlockProps.save({
		className: 'checkout-testimonial'
	});

	const renderStars = () => {
		const stars = [];
		for (let i = 0; i < 5; i++) {
			stars.push(
				<span key={i} className={`checkout-testimonial__star ${i < rating ? 'checkout-testimonial__star--filled' : ''}`}>
					★
				</span>
			);
		}
		return stars;
	};

	return (
		<div {...blockProps}>
			<div className="checkout-testimonial__stars">
				{renderStars()}
			</div>
			<RichText.Content
				tagName="p"
				className="checkout-testimonial__quote"
				value={quote}
			/>
			<div className="checkout-testimonial__author">{authorName}</div>
			<div className="checkout-testimonial__role">{authorRole}</div>
		</div>
	);
}
