import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, TextareaControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: 'card-produits-comparatif-wrapper'
	});

	const renderFeaturesList = (featuresText) => {
		return featuresText.split('\n').filter(Boolean).map((item, index) => (
			<div key={index} className="product-card__feature">✅ {item}</div>
		));
	};

	return (
		<>
			<InspectorControls>
				{/* Card 1 - Académie */}
				<PanelBody title="Card 1 - Académie (Featured)" initialOpen={true}>
					<TextControl
						label="Texte ribbon"
						value={attributes.card1RibbonText}
						onChange={(value) => setAttributes({ card1RibbonText: value })}
					/>
					<TextControl
						label="Badge type"
						value={attributes.card1TypeText}
						onChange={(value) => setAttributes({ card1TypeText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card1Title}
						onChange={(value) => setAttributes({ card1Title: value })}
					/>
					<TextareaControl
						label="Description"
						value={attributes.card1Description}
						onChange={(value) => setAttributes({ card1Description: value })}
					/>
					<TextareaControl
						label="Features (une par ligne)"
						value={attributes.card1Features}
						onChange={(value) => setAttributes({ card1Features: value })}
					/>
					<TextControl
						label="Prix"
						value={attributes.card1Price}
						onChange={(value) => setAttributes({ card1Price: value })}
					/>
					<TextControl
						label="Suffixe prix"
						value={attributes.card1PriceSuffix}
						onChange={(value) => setAttributes({ card1PriceSuffix: value })}
					/>
					<TextControl
						label="Info prix"
						value={attributes.card1PriceInfo}
						onChange={(value) => setAttributes({ card1PriceInfo: value })}
					/>
					<ToggleControl
						label="Afficher social proof"
						checked={attributes.card1ShowSocialProof}
						onChange={(value) => setAttributes({ card1ShowSocialProof: value })}
					/>
					{attributes.card1ShowSocialProof && (
						<TextControl
							label="Social proof"
							value={attributes.card1SocialProof}
							onChange={(value) => setAttributes({ card1SocialProof: value })}
						/>
					)}
					<TextControl
						label="Pour qui ?"
						value={attributes.card1TargetText}
						onChange={(value) => setAttributes({ card1TargetText: value })}
					/>
					<TextControl
						label="Texte CTA"
						value={attributes.card1CtaText}
						onChange={(value) => setAttributes({ card1CtaText: value })}
					/>
					<TextControl
						label="URL CTA"
						value={attributes.card1CtaUrl}
						onChange={(value) => setAttributes({ card1CtaUrl: value })}
						type="url"
					/>
				</PanelBody>

				{/* Card 2 - Prépas */}
				<PanelBody title="Card 2 - Prépas" initialOpen={false}>
					<TextControl
						label="Badge type"
						value={attributes.card2TypeText}
						onChange={(value) => setAttributes({ card2TypeText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card2Title}
						onChange={(value) => setAttributes({ card2Title: value })}
					/>
					<TextareaControl
						label="Description"
						value={attributes.card2Description}
						onChange={(value) => setAttributes({ card2Description: value })}
					/>
					<TextareaControl
						label="Features (une par ligne)"
						value={attributes.card2Features}
						onChange={(value) => setAttributes({ card2Features: value })}
					/>
					<TextControl
						label="Prix"
						value={attributes.card2Price}
						onChange={(value) => setAttributes({ card2Price: value })}
					/>
					<TextControl
						label="Info prix"
						value={attributes.card2PriceInfo}
						onChange={(value) => setAttributes({ card2PriceInfo: value })}
					/>
					<TextControl
						label="Pour qui ?"
						value={attributes.card2TargetText}
						onChange={(value) => setAttributes({ card2TargetText: value })}
					/>
					<TextControl
						label="Texte CTA"
						value={attributes.card2CtaText}
						onChange={(value) => setAttributes({ card2CtaText: value })}
					/>
					<TextControl
						label="URL CTA"
						value={attributes.card2CtaUrl}
						onChange={(value) => setAttributes({ card2CtaUrl: value })}
						type="url"
					/>
				</PanelBody>

				{/* Card 3 - Fiches PDF */}
				<PanelBody title="Card 3 - Fiches PDF" initialOpen={false}>
					<TextControl
						label="Badge type"
						value={attributes.card3TypeText}
						onChange={(value) => setAttributes({ card3TypeText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card3Title}
						onChange={(value) => setAttributes({ card3Title: value })}
					/>
					<TextareaControl
						label="Description"
						value={attributes.card3Description}
						onChange={(value) => setAttributes({ card3Description: value })}
					/>
					<TextareaControl
						label="Features (une par ligne)"
						value={attributes.card3Features}
						onChange={(value) => setAttributes({ card3Features: value })}
					/>
					<TextControl
						label="Prix"
						value={attributes.card3Price}
						onChange={(value) => setAttributes({ card3Price: value })}
					/>
					<TextControl
						label="Info prix"
						value={attributes.card3PriceInfo}
						onChange={(value) => setAttributes({ card3PriceInfo: value })}
					/>
					<TextControl
						label="Pour qui ?"
						value={attributes.card3TargetText}
						onChange={(value) => setAttributes({ card3TargetText: value })}
					/>
					<TextControl
						label="Texte CTA"
						value={attributes.card3CtaText}
						onChange={(value) => setAttributes({ card3CtaText: value })}
					/>
					<TextControl
						label="URL CTA"
						value={attributes.card3CtaUrl}
						onChange={(value) => setAttributes({ card3CtaUrl: value })}
						type="url"
					/>
				</PanelBody>

				{/* Card 4 - Minos */}
				<PanelBody title="Card 4 - Minos" initialOpen={false}>
					<TextControl
						label="Badge type"
						value={attributes.card4TypeText}
						onChange={(value) => setAttributes({ card4TypeText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card4Title}
						onChange={(value) => setAttributes({ card4Title: value })}
					/>
					<TextareaControl
						label="Description"
						value={attributes.card4Description}
						onChange={(value) => setAttributes({ card4Description: value })}
					/>
					<TextareaControl
						label="Features (une par ligne)"
						value={attributes.card4Features}
						onChange={(value) => setAttributes({ card4Features: value })}
					/>
					<TextControl
						label="Prix"
						value={attributes.card4Price}
						onChange={(value) => setAttributes({ card4Price: value })}
					/>
					<TextControl
						label="Info prix"
						value={attributes.card4PriceInfo}
						onChange={(value) => setAttributes({ card4PriceInfo: value })}
					/>
					<ToggleControl
						label="Afficher bonus"
						checked={attributes.card4ShowBonus}
						onChange={(value) => setAttributes({ card4ShowBonus: value })}
					/>
					{attributes.card4ShowBonus && (
						<TextControl
							label="Texte bonus"
							value={attributes.card4BonusText}
							onChange={(value) => setAttributes({ card4BonusText: value })}
						/>
					)}
					<TextControl
						label="Pour qui ?"
						value={attributes.card4TargetText}
						onChange={(value) => setAttributes({ card4TargetText: value })}
					/>
					<TextControl
						label="Texte CTA"
						value={attributes.card4CtaText}
						onChange={(value) => setAttributes({ card4CtaText: value })}
					/>
					<TextControl
						label="URL CTA"
						value={attributes.card4CtaUrl}
						onChange={(value) => setAttributes({ card4CtaUrl: value })}
						type="url"
					/>
				</PanelBody>
			</InspectorControls>

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
							<button className="product-card__cta product-card__cta--gradient">{attributes.card1CtaText}</button>
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
						<button className="product-card__cta product-card__cta--outline product-card__cta--outline-secondary">{attributes.card2CtaText}</button>
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
						<button className="product-card__cta product-card__cta--outline product-card__cta--outline-success">{attributes.card3CtaText}</button>
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
						<button className="product-card__cta product-card__cta--outline product-card__cta--outline-warning">{attributes.card4CtaText}</button>
					</div>
				</div>
			</div>
		</>
	);
}
