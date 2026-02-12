import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const blockProps = useBlockProps.save({
		className: 'pricing-duration-selector'
	});

	const renderDurationCard = (optionNum) => {
		const prefix = `option${optionNum}`;
		const isPopular = attributes[`${prefix}IsPopular`];
		const hasSavings = attributes[`${prefix}SavingsPercent`] || attributes[`${prefix}SavingsAmount`];

		return (
			<a
				href={attributes[`${prefix}CtaUrl`]}
				className={`duration-card ${isPopular ? 'duration-card--popular' : ''}`}
			>
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
				<span className={`duration-card__cta ${isPopular ? 'duration-card__cta--gradient' : 'duration-card__cta--outline'}`}>
					{attributes[`${prefix}CtaText`]}
				</span>
			</a>
		);
	};

	return (
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
	);
}
