import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { rating, quote, authorName, authorRole } = attributes;

	const blockProps = useBlockProps({
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
		<>
			<InspectorControls>
				<PanelBody title="Note" initialOpen={true}>
					<RangeControl
						label="Étoiles"
						value={rating}
						onChange={(value) => setAttributes({ rating: value })}
						min={1}
						max={5}
					/>
				</PanelBody>
				<PanelBody title="Auteur" initialOpen={true}>
					<TextControl
						label="Nom"
						value={authorName}
						onChange={(value) => setAttributes({ authorName: value })}
					/>
					<TextControl
						label="Rôle / Info"
						value={authorRole}
						onChange={(value) => setAttributes({ authorRole: value })}
						help="Ex: L3 Droit — Lyon III"
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="checkout-testimonial__stars">
					{renderStars()}
				</div>
				<RichText
					tagName="p"
					className="checkout-testimonial__quote"
					value={quote}
					onChange={(value) => setAttributes({ quote: value })}
					placeholder="Citation du témoignage..."
				/>
				<div className="checkout-testimonial__author">{authorName}</div>
				<div className="checkout-testimonial__role">{authorRole}</div>
			</div>
		</>
	);
}
