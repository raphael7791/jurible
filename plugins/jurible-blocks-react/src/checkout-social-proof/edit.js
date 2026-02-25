import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { icon, label, stats, rating, score, reviewCount } = attributes;

	const blockProps = useBlockProps({
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
		<>
			<InspectorControls>
				<PanelBody title="Contenu" initialOpen={true}>
					<TextControl
						label="Icône (emoji)"
						value={icon}
						onChange={(value) => setAttributes({ icon: value })}
					/>
					<TextControl
						label="Label principal"
						value={label}
						onChange={(value) => setAttributes({ label: value })}
					/>
					<TextControl
						label="Statistique"
						value={stats}
						onChange={(value) => setAttributes({ stats: value })}
					/>
				</PanelBody>
				<PanelBody title="Note" initialOpen={true}>
					<RangeControl
						label="Étoiles"
						value={rating}
						onChange={(value) => setAttributes({ rating: value })}
						min={1}
						max={5}
					/>
					<TextControl
						label="Score affiché"
						value={score}
						onChange={(value) => setAttributes({ score: value })}
						help="Ex: 4.8/5"
					/>
					<TextControl
						label="Nombre d'avis"
						value={reviewCount}
						onChange={(value) => setAttributes({ reviewCount: value })}
						help="Ex: (150+ avis)"
					/>
				</PanelBody>
			</InspectorControls>

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
		</>
	);
}
