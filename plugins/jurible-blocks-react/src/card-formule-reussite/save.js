import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const blockProps = useBlockProps.save({
		className: 'card-formule-reussite-wrapper'
	});

	const includeTags = attributes.includesTags.split(',').map(tag => tag.trim()).filter(Boolean);

	return (
		<div {...blockProps}>
			<div className="card-reussite">
				<div className="card-reussite__ribbon">{attributes.ribbonText}</div>

				<div className="card-reussite__content">
					{/* Colonne gauche */}
					<div className="card-reussite__left">
						<span className="card-reussite__badge">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="14" height="14">
								<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
							</svg>
							{attributes.badgeText}
						</span>

						<h2 className="card-reussite__title">{attributes.title}</h2>

						{attributes.showSeasonBadge && (
							<span className="card-reussite__season">{attributes.seasonBadgeText}</span>
						)}

						<div className="card-reussite__pricing">
							<div className="card-reussite__value">{attributes.originalValue}</div>
							<div className="card-reussite__price">
								<span className="card-reussite__price-amount">{attributes.price}€</span>
								<span className="card-reussite__price-period">{attributes.pricePeriod}</span>
							</div>
							<span className="card-reussite__savings">{attributes.savingsText}</span>
						</div>

						{attributes.showCountdown && (
							<div className="card-reussite__countdown-wrapper" data-countdown-date={attributes.countdownDate}>
								<p className="card-reussite__countdown-label">{attributes.countdownLabel}</p>
								<div className="card-reussite__countdown">
									<div className="countdown-item">
										<div className="countdown-item__value" data-countdown-days>--</div>
										<div className="countdown-item__label">Jours</div>
									</div>
									<div className="countdown-item">
										<div className="countdown-item__value" data-countdown-hours>--</div>
										<div className="countdown-item__label">Heures</div>
									</div>
									<div className="countdown-item">
										<div className="countdown-item__value" data-countdown-minutes>--</div>
										<div className="countdown-item__label">Min</div>
									</div>
									<div className="countdown-item">
										<div className="countdown-item__value" data-countdown-seconds>--</div>
										<div className="countdown-item__label">Sec</div>
									</div>
								</div>
							</div>
						)}

						<div className="card-reussite__social-proof">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="14" height="14">
								<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
								<circle cx="9" cy="7" r="4"/>
								<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
								<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
							</svg>
							<span>{attributes.socialProofCount} {attributes.socialProofText}</span>
						</div>
					</div>

					{/* Colonne droite */}
					<div className="card-reussite__right">
						<div className="card-reussite__header">
							<h3 className="card-reussite__header-title">{attributes.headerTitle}</h3>
							<p className="card-reussite__header-subtitle">{attributes.headerSubtitle}</p>
						</div>

						<div className="card-reussite__features">
							<div className="feature-item">
								<div className="feature-item__icon">{attributes.feature1Icon}</div>
								<div className="feature-item__content">
									<div className="feature-item__title">{attributes.feature1Title}</div>
									<div className="feature-item__desc">{attributes.feature1Desc}</div>
								</div>
							</div>
							<div className="feature-item">
								<div className="feature-item__icon">{attributes.feature2Icon}</div>
								<div className="feature-item__content">
									<div className="feature-item__title">{attributes.feature2Title}</div>
									<div className="feature-item__desc">{attributes.feature2Desc}</div>
								</div>
							</div>
							<div className="feature-item">
								<div className="feature-item__icon">{attributes.feature3Icon}</div>
								<div className="feature-item__content">
									<div className="feature-item__title">{attributes.feature3Title}</div>
									<div className="feature-item__desc">{attributes.feature3Desc}</div>
								</div>
							</div>
							<div className="feature-item">
								<div className="feature-item__icon">{attributes.feature4Icon}</div>
								<div className="feature-item__content">
									<div className="feature-item__title">{attributes.feature4Title}</div>
									<div className="feature-item__desc">{attributes.feature4Desc}</div>
								</div>
							</div>
						</div>

						<div className="card-reussite__includes">
							<p className="card-reussite__includes-title">{attributes.includesTitle}</p>
							<div className="card-reussite__includes-list">
								{includeTags.map((tag, index) => (
									<span key={index} className="include-tag">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="12" height="12">
											<polyline points="20 6 9 17 4 12"/>
										</svg>
										{tag}
									</span>
								))}
							</div>
						</div>

						<div className="card-reussite__cta-section">
							<a href={attributes.ctaUrl} className="card-reussite__cta">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="18" height="18">
									<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
									<polyline points="22 4 12 14.01 9 11.01"/>
								</svg>
								{attributes.ctaText}
							</a>
							<p className="card-reussite__notice">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="14" height="14">
									<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
									<path d="M13.73 21a2 2 0 0 1-3.46 0"/>
								</svg>
								{attributes.noticeText}
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}
