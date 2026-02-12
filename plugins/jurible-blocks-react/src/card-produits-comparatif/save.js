import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const blockProps = useBlockProps.save({
		className: 'card-produits-comparatif-wrapper'
	});

	const renderFeaturesList = (featuresText) => {
		return featuresText.split('\n').filter(Boolean).map((item, index) => (
			<div key={index} className="product-card__feature">✅ {item}</div>
		));
	};

	return (
		<div {...blockProps}>
			<div className="products-grid">
				{/* Card 1 - Académie (Featured) */}
				<div className="product-card product-card--featured">
					<div className="product-card__ribbon">⭐ {attributes.card1RibbonText}</div>
					<div className="product-card__content">
						<span className="product-card__type product-card__type--subscription">{attributes.card1TypeText}</span>
						<h3 className="product-card__title">{attributes.card1Title}</h3>
						<p className="product-card__description">{attributes.card1Description}</p>
						<div className="product-card__features">
							{renderFeaturesList(attributes.card1Features)}
						</div>
						<div className="product-card__pricing">
							<div className="product-card__price product-card__price--primary">
								{attributes.card1Price}<span className="product-card__price-suffix">{attributes.card1PriceSuffix}</span>
							</div>
							{attributes.card1PriceInfo && (
								<p className="product-card__price-info">{attributes.card1PriceInfo}</p>
							)}
						</div>
						{attributes.card1ShowSocialProof && (
							<div className="product-card__social">👥 {attributes.card1SocialProof}</div>
						)}
						<div className="product-card__target">
							<p className="product-card__target-label">👤 Pour qui ?</p>
							<p className="product-card__target-text">{attributes.card1TargetText}</p>
						</div>
						<a href={attributes.card1CtaUrl} className="product-card__cta product-card__cta--gradient">{attributes.card1CtaText}</a>
					</div>
				</div>

				{/* Card 2 - Prépas */}
				<div className="product-card">
					<span className="product-card__type product-card__type--formation">{attributes.card2TypeText}</span>
					<h3 className="product-card__title">{attributes.card2Title}</h3>
					<p className="product-card__description">{attributes.card2Description}</p>
					<div className="product-card__features">
						{renderFeaturesList(attributes.card2Features)}
					</div>
					<div className="product-card__pricing">
						<div className="product-card__price product-card__price--secondary">{attributes.card2Price}</div>
						{attributes.card2PriceInfo && (
							<p className="product-card__price-info">{attributes.card2PriceInfo}</p>
						)}
					</div>
					<div className="product-card__target">
						<p className="product-card__target-label">👤 Pour qui ?</p>
						<p className="product-card__target-text">{attributes.card2TargetText}</p>
					</div>
					<a href={attributes.card2CtaUrl} className="product-card__cta product-card__cta--outline product-card__cta--outline-secondary">{attributes.card2CtaText}</a>
				</div>

				{/* Card 3 - Fiches PDF */}
				<div className="product-card">
					<span className="product-card__type product-card__type--purchase">{attributes.card3TypeText}</span>
					<h3 className="product-card__title">{attributes.card3Title}</h3>
					<p className="product-card__description">{attributes.card3Description}</p>
					<div className="product-card__features">
						{renderFeaturesList(attributes.card3Features)}
					</div>
					<div className="product-card__pricing">
						<div className="product-card__price product-card__price--success">{attributes.card3Price}</div>
						{attributes.card3PriceInfo && (
							<p className="product-card__price-info">{attributes.card3PriceInfo}</p>
						)}
					</div>
					<div className="product-card__target">
						<p className="product-card__target-label">👤 Pour qui ?</p>
						<p className="product-card__target-text">{attributes.card3TargetText}</p>
					</div>
					<a href={attributes.card3CtaUrl} className="product-card__cta product-card__cta--outline product-card__cta--outline-success">{attributes.card3CtaText}</a>
				</div>

				{/* Card 4 - Minos */}
				<div className="product-card">
					<span className="product-card__type product-card__type--credits">{attributes.card4TypeText}</span>
					<h3 className="product-card__title">{attributes.card4Title}</h3>
					<p className="product-card__description">{attributes.card4Description}</p>
					<div className="product-card__features">
						{renderFeaturesList(attributes.card4Features)}
					</div>
					<div className="product-card__pricing">
						<div className="product-card__price product-card__price--warning">{attributes.card4Price}</div>
						{attributes.card4PriceInfo && (
							<p className="product-card__price-info">{attributes.card4PriceInfo}</p>
						)}
						{attributes.card4ShowBonus && (
							<span className="product-card__bonus">🎁 {attributes.card4BonusText}</span>
						)}
					</div>
					<div className="product-card__target">
						<p className="product-card__target-label">👤 Pour qui ?</p>
						<p className="product-card__target-text">{attributes.card4TargetText}</p>
					</div>
					<a href={attributes.card4CtaUrl} className="product-card__cta product-card__cta--outline product-card__cta--outline-warning">{attributes.card4CtaText}</a>
				</div>
			</div>
		</div>
	);
}
