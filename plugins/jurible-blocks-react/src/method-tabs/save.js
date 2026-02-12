import { useBlockProps } from '@wordpress/block-editor';

// Helper to convert YouTube URL to embed URL
const getYouTubeEmbedUrl = (url) => {
	if (!url) return null;

	const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
	const match = url.match(regExp);

	if (match && match[2].length === 11) {
		return `https://www.youtube.com/embed/${match[2]}`;
	}
	return null;
};

export default function save({ attributes }) {
	const {
		sectionBadge,
		sectionTitle,
		sectionSubtitle,
		activeTab,
		tabs,
		ctaText,
		ctaSubtext,
		ctaUrl,
	} = attributes;

	const blockProps = useBlockProps.save();

	return (
		<div {...blockProps}>
			<div className="method-tabs__header">
				<span className="method-tabs__badge">{sectionBadge}</span>
				<h2 className="method-tabs__title" dangerouslySetInnerHTML={{ __html: sectionTitle }} />
				<p className="method-tabs__subtitle">{sectionSubtitle}</p>
			</div>

			<div className="method-tabs__nav" role="tablist">
				{tabs.map((tab, index) => (
					<button
						key={index}
						className={`method-tabs__tab${activeTab === index ? ' is-active' : ''}`}
						role="tab"
						aria-selected={activeTab === index}
						aria-controls={`method-tab-panel-${index}`}
						data-tab-index={index}
						type="button"
					>
						<span className="method-tabs__tab-icon">{tab.icon}</span>
						<span className="method-tabs__tab-label">{tab.label}</span>
					</button>
				))}
			</div>

			<div className="method-tabs__panels">
				{tabs.map((tab, index) => {
					const embedUrl = getYouTubeEmbedUrl(tab.videoUrl);

					return (
						<div
							key={index}
							className={`method-tabs__panel${activeTab === index ? ' is-active' : ''}`}
							role="tabpanel"
							id={`method-tab-panel-${index}`}
							aria-hidden={activeTab !== index}
							data-panel-index={index}
						>
							<div className="method-tabs__content">
								<div className="method-tabs__media">
									{embedUrl ? (
										<div className="method-tabs__video-wrapper">
											<iframe
												src={embedUrl}
												title={tab.videoTitle || tab.contentTitle}
												frameBorder="0"
												allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
												allowFullScreen
												loading="lazy"
											></iframe>
											{tab.videoTitle && (
												<div className="method-tabs__video-info">
													<span className="method-tabs__video-title">{tab.videoTitle}</span>
												</div>
											)}
										</div>
									) : (
										<div className="method-tabs__placeholder">
											<span className="method-tabs__placeholder-icon">{tab.icon}</span>
										</div>
									)}
								</div>
								<div className="method-tabs__info">
									<h3 className="method-tabs__content-title">{tab.contentTitle}</h3>
									<p className="method-tabs__content-desc">{tab.contentDescription}</p>
									{tab.linkText && tab.linkUrl && (
										<a href={tab.linkUrl} className="method-tabs__link">
											{tab.linkText}
										</a>
									)}
								</div>
							</div>
						</div>
					);
				})}
			</div>

			<div className="method-tabs__cta-wrapper">
				<p className="method-tabs__cta-intro">Vous aimez ce contenu ?</p>
				<a href={ctaUrl} className="method-tabs__cta">
					{ctaText}
					{ctaSubtext && <span className="method-tabs__cta-subtext">{ctaSubtext}</span>}
				</a>
			</div>
		</div>
	);
}
