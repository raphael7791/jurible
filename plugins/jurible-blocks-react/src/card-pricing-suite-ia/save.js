import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const blockProps = useBlockProps.save({
		className: 'card-pricing-suite-ia-wrapper'
	});

	const renderIncludesList = (includesText) => {
		return includesText.split('\n').filter(Boolean).map((item, index) => (
			<div key={index} className="minos-card__include-item">✅ {item}</div>
		));
	};

	return (
		<div {...blockProps}>
			<div className="minos-grid">
				{/* Card 1 - Standard */}
				<div className="minos-card">
					<span className="minos-card__credits minos-card__credits--standard">{attributes.card1CreditsText}</span>
					<div className="minos-card__header">
						<h3 className="minos-card__title">{attributes.card1Title}</h3>
						{attributes.card1ShowBadge && (
							<span className="minos-card__badge minos-card__badge--popular">{attributes.card1BadgeText}</span>
						)}
					</div>
					<p className="minos-card__description">{attributes.card1Description}</p>
					<div className="minos-card__pricing">
						<div className="minos-card__price-row">
							<span className="minos-card__price">{attributes.card1Price}</span>
						</div>
					</div>
					<div className="minos-card__includes">
						<p className="minos-card__includes-label">Inclus</p>
						<div className="minos-card__includes-list">
							{renderIncludesList(attributes.card1Includes)}
						</div>
					</div>
					<a href={attributes.card1CtaUrl} className="minos-card__cta minos-card__cta--outline">{attributes.card1CtaText}</a>
				</div>

				{/* Card 2 - Pro (Featured) */}
				<div className="minos-card minos-card--featured">
					<div className="minos-card__ribbon">💎 {attributes.card2RibbonText}</div>
					<span className="minos-card__credits minos-card__credits--pro">{attributes.card2CreditsText}</span>
					<div className="minos-card__header">
						<h3 className="minos-card__title">{attributes.card2Title}</h3>
					</div>
					<p className="minos-card__description">{attributes.card2Description}</p>
					<div className="minos-card__pricing">
						<div className="minos-card__price-row">
							<span className="minos-card__price">{attributes.card2Price}</span>
							{attributes.card2ShowDiscount && (
								<span className="minos-card__discount">{attributes.card2DiscountText}</span>
							)}
						</div>
					</div>
					<div className="minos-card__includes">
						<p className="minos-card__includes-label">Inclus</p>
						<div className="minos-card__includes-list">
							{renderIncludesList(attributes.card2Includes)}
						</div>
					</div>
					<a href={attributes.card2CtaUrl} className="minos-card__cta minos-card__cta--gradient">{attributes.card2CtaText}</a>
				</div>

				{/* Card 3 - Abonnés */}
				<div className="minos-card">
					<span className="minos-card__credits minos-card__credits--bonus">🎁 {attributes.card3CreditsText}</span>
					<div className="minos-card__header">
						<h3 className="minos-card__title">{attributes.card3Title}</h3>
					</div>
					<p className="minos-card__description">{attributes.card3Description}</p>
					<div className="minos-card__pricing">
						<div className="minos-card__price-row">
							<span className="minos-card__price minos-card__price--success">{attributes.card3Price}</span>
						</div>
						{attributes.card3PriceInfo && (
							<p className="minos-card__price-info">{attributes.card3PriceInfo}</p>
						)}
					</div>
					<div className="minos-card__includes">
						<p className="minos-card__includes-label">Inclus</p>
						<div className="minos-card__includes-list">
							{renderIncludesList(attributes.card3Includes)}
						</div>
					</div>
					<a href={attributes.card3CtaUrl} className="minos-card__cta minos-card__cta--outline minos-card__cta--outline-primary">{attributes.card3CtaText}</a>
					{attributes.card3ShowNote && (
						<p className="minos-card__note">
							{attributes.card3NoteText} <a href={attributes.card3NoteLinkUrl}>{attributes.card3NoteLinkText}</a>
						</p>
					)}
				</div>
			</div>
		</div>
	);
}
