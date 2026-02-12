import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, TextareaControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: 'card-pricing-suite-ia-wrapper'
	});

	const renderIncludesList = (includesText) => {
		return includesText.split('\n').filter(Boolean).map((item, index) => (
			<div key={index} className="minos-card__include-item">✅ {item}</div>
		));
	};

	return (
		<>
			<InspectorControls>
				{/* Card 1 - Standard */}
				<PanelBody title="Card 1 - Standard" initialOpen={true}>
					<TextControl
						label="Badge crédits"
						value={attributes.card1CreditsText}
						onChange={(value) => setAttributes({ card1CreditsText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card1Title}
						onChange={(value) => setAttributes({ card1Title: value })}
					/>
					<ToggleControl
						label="Afficher badge titre"
						checked={attributes.card1ShowBadge}
						onChange={(value) => setAttributes({ card1ShowBadge: value })}
					/>
					{attributes.card1ShowBadge && (
						<TextControl
							label="Texte badge"
							value={attributes.card1BadgeText}
							onChange={(value) => setAttributes({ card1BadgeText: value })}
						/>
					)}
					<TextControl
						label="Description"
						value={attributes.card1Description}
						onChange={(value) => setAttributes({ card1Description: value })}
					/>
					<TextControl
						label="Prix"
						value={attributes.card1Price}
						onChange={(value) => setAttributes({ card1Price: value })}
					/>
					<TextareaControl
						label="Inclus (une ligne par item)"
						value={attributes.card1Includes}
						onChange={(value) => setAttributes({ card1Includes: value })}
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

				{/* Card 2 - Pro */}
				<PanelBody title="Card 2 - Pro (Featured)" initialOpen={false}>
					<TextControl
						label="Texte ribbon"
						value={attributes.card2RibbonText}
						onChange={(value) => setAttributes({ card2RibbonText: value })}
					/>
					<TextControl
						label="Badge crédits"
						value={attributes.card2CreditsText}
						onChange={(value) => setAttributes({ card2CreditsText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card2Title}
						onChange={(value) => setAttributes({ card2Title: value })}
					/>
					<TextControl
						label="Description"
						value={attributes.card2Description}
						onChange={(value) => setAttributes({ card2Description: value })}
					/>
					<TextControl
						label="Prix"
						value={attributes.card2Price}
						onChange={(value) => setAttributes({ card2Price: value })}
					/>
					<ToggleControl
						label="Afficher discount"
						checked={attributes.card2ShowDiscount}
						onChange={(value) => setAttributes({ card2ShowDiscount: value })}
					/>
					{attributes.card2ShowDiscount && (
						<TextControl
							label="Texte discount"
							value={attributes.card2DiscountText}
							onChange={(value) => setAttributes({ card2DiscountText: value })}
						/>
					)}
					<TextareaControl
						label="Inclus (une ligne par item)"
						value={attributes.card2Includes}
						onChange={(value) => setAttributes({ card2Includes: value })}
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

				{/* Card 3 - Abonnés */}
				<PanelBody title="Card 3 - Abonnés" initialOpen={false}>
					<TextControl
						label="Badge crédits"
						value={attributes.card3CreditsText}
						onChange={(value) => setAttributes({ card3CreditsText: value })}
					/>
					<TextControl
						label="Titre"
						value={attributes.card3Title}
						onChange={(value) => setAttributes({ card3Title: value })}
					/>
					<TextControl
						label="Description"
						value={attributes.card3Description}
						onChange={(value) => setAttributes({ card3Description: value })}
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
					<TextareaControl
						label="Inclus (une ligne par item)"
						value={attributes.card3Includes}
						onChange={(value) => setAttributes({ card3Includes: value })}
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
					<ToggleControl
						label="Afficher note"
						checked={attributes.card3ShowNote}
						onChange={(value) => setAttributes({ card3ShowNote: value })}
					/>
					{attributes.card3ShowNote && (
						<>
							<TextControl
								label="Texte note"
								value={attributes.card3NoteText}
								onChange={(value) => setAttributes({ card3NoteText: value })}
							/>
							<TextControl
								label="Texte lien"
								value={attributes.card3NoteLinkText}
								onChange={(value) => setAttributes({ card3NoteLinkText: value })}
							/>
							<TextControl
								label="URL lien"
								value={attributes.card3NoteLinkUrl}
								onChange={(value) => setAttributes({ card3NoteLinkUrl: value })}
								type="url"
							/>
						</>
					)}
				</PanelBody>
			</InspectorControls>

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
						<button className="minos-card__cta minos-card__cta--outline">{attributes.card1CtaText}</button>
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
						<button className="minos-card__cta minos-card__cta--gradient">{attributes.card2CtaText}</button>
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
						<button className="minos-card__cta minos-card__cta--outline minos-card__cta--outline-primary">{attributes.card3CtaText}</button>
						{attributes.card3ShowNote && (
							<p className="minos-card__note">
								{attributes.card3NoteText} <a href={attributes.card3NoteLinkUrl}>{attributes.card3NoteLinkText}</a>
							</p>
						)}
					</div>
				</div>
			</div>
		</>
	);
}
