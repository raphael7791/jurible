import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: 'pricing-duration-selector'
	});

	const renderOptionPanel = (optionNum, title) => {
		const prefix = `option${optionNum}`;
		const isPopular = attributes[`${prefix}IsPopular`];

		const setPopular = (value) => {
			// Si on active "populaire", désactiver les autres
			const updates = { [`${prefix}IsPopular`]: value };
			if (value) {
				[1, 2, 3, 4].forEach(num => {
					if (num !== optionNum) {
						updates[`option${num}IsPopular`] = false;
					}
				});
			}
			setAttributes(updates);
		};

		return (
			<PanelBody title={title} initialOpen={optionNum === 3}>
				<TextControl
					label="Durée"
					value={attributes[`${prefix}Duration`]}
					onChange={(value) => setAttributes({ [`${prefix}Duration`]: value })}
				/>
				<TextControl
					label="Prix (sans €)"
					value={attributes[`${prefix}Price`]}
					onChange={(value) => setAttributes({ [`${prefix}Price`]: value })}
				/>
				<TextControl
					label="Prix barré (ex: 87€)"
					value={attributes[`${prefix}OriginalPrice`]}
					onChange={(value) => setAttributes({ [`${prefix}OriginalPrice`]: value })}
					help="Laisser vide si pas de réduction"
				/>
				<TextControl
					label="Prix par mois"
					value={attributes[`${prefix}MonthlyPrice`]}
					onChange={(value) => setAttributes({ [`${prefix}MonthlyPrice`]: value })}
				/>
				<TextControl
					label="Économie %"
					value={attributes[`${prefix}SavingsPercent`]}
					onChange={(value) => setAttributes({ [`${prefix}SavingsPercent`]: value })}
					help="Ex: -20%"
				/>
				<TextControl
					label="Économie montant"
					value={attributes[`${prefix}SavingsAmount`]}
					onChange={(value) => setAttributes({ [`${prefix}SavingsAmount`]: value })}
					help="Ex: Économisez 34€"
				/>
				<ToggleControl
					label="Option populaire"
					checked={isPopular}
					onChange={setPopular}
					help="Une seule option peut être populaire"
				/>
				<TextControl
					label="Texte du bouton"
					value={attributes[`${prefix}CtaText`]}
					onChange={(value) => setAttributes({ [`${prefix}CtaText`]: value })}
				/>
				<TextControl
					label="URL du bouton"
					value={attributes[`${prefix}CtaUrl`]}
					onChange={(value) => setAttributes({ [`${prefix}CtaUrl`]: value })}
					type="url"
				/>
			</PanelBody>
		);
	};

	const renderDurationCard = (optionNum) => {
		const prefix = `option${optionNum}`;
		const isPopular = attributes[`${prefix}IsPopular`];
		const hasSavings = attributes[`${prefix}SavingsPercent`] || attributes[`${prefix}SavingsAmount`];

		return (
			<div className={`duration-card ${isPopular ? 'duration-card--popular' : ''}`}>
				{isPopular && <span className="duration-card__badge">Populaire</span>}
				<p className="duration-card__duration">{attributes[`${prefix}Duration`]}</p>
				<div className="duration-card__price-wrapper">
					{attributes[`${prefix}OriginalPrice`] && (
						<p className="duration-card__price-old">{attributes[`${prefix}OriginalPrice`]}</p>
					)}
					<p className="duration-card__price">{attributes[`${prefix}Price`]}<span>€</span></p>
				</div>
				<p className="duration-card__monthly">{attributes[`${prefix}MonthlyPrice`]}</p>
				{hasSavings ? (
					<div className="duration-card__savings">
						{attributes[`${prefix}SavingsPercent`] && (
							<span className="duration-card__savings-percent">{attributes[`${prefix}SavingsPercent`]}</span>
						)}
						{attributes[`${prefix}SavingsAmount`] && (
							<span className="duration-card__savings-amount">{attributes[`${prefix}SavingsAmount`]}</span>
						)}
					</div>
				) : (
					<div className="duration-card__savings-placeholder"></div>
				)}
				<button className={`duration-card__cta ${isPopular ? 'duration-card__cta--gradient' : 'duration-card__cta--outline'}`}>
					{attributes[`${prefix}CtaText`]}
				</button>
			</div>
		);
	};

	return (
		<>
			<InspectorControls>
				{renderOptionPanel(1, "Option 1 - 1 mois")}
				{renderOptionPanel(2, "Option 2 - 3 mois")}
				{renderOptionPanel(3, "Option 3 - 6 mois (Populaire)")}
				{renderOptionPanel(4, "Option 4 - 12 mois")}
				<PanelBody title="Notice" initialOpen={false}>
					<TextControl
						label="Texte de la notice"
						value={attributes.noticeText}
						onChange={(value) => setAttributes({ noticeText: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="pricing-grid">
					{renderDurationCard(1)}
					{renderDurationCard(2)}
					{renderDurationCard(3)}
					{renderDurationCard(4)}
				</div>
				<p className="pricing-notice">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
						<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
						<polyline points="22 4 12 14.01 9 11.01"/>
					</svg>
					{attributes.noticeText}
				</p>
			</div>
		</>
	);
}
