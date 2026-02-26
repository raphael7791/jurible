import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Notice,
} from '@wordpress/components';
import { useState } from '@wordpress/element';

// Tab configuration (fixed)
const TABS = [
	{ id: 'video', icon: '🎬', label: 'Vidéo', isDynamic: true },
	{ id: 'cours', icon: '📄', label: 'Cours écrit', isDynamic: false },
	{ id: 'mindmap', icon: '🗺️', label: 'Mindmap', isDynamic: false },
	{ id: 'qcm', icon: '✅', label: 'QCM', isDynamic: false },
	{ id: 'flashcard', icon: '🃏', label: 'Flashcard', isDynamic: false },
	{ id: 'annale', icon: '📚', label: 'Annale', isDynamic: false },
	{ id: 'fiche-video', icon: '🎥', label: 'Fiche vidéo', isDynamic: false },
];

export default function Edit({ attributes, setAttributes }) {
	const {
		sectionBadge,
		sectionTitle,
		sectionSubtitle,
		videoUrl,
		ctaText,
		ctaUrl,
	} = attributes;

	const [activeTab, setActiveTab] = useState(0);
	const blockProps = useBlockProps({
		className: 'wp-block-jurible-method-tabs',
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('En-tête de section', 'jurible-blocks-react')} initialOpen={true}>
					<TextControl
						label={__('Badge', 'jurible-blocks-react')}
						value={sectionBadge}
						onChange={(value) => setAttributes({ sectionBadge: value })}
					/>
					<TextControl
						label={__('Titre (utiliser <mark> pour le gradient)', 'jurible-blocks-react')}
						value={sectionTitle}
						onChange={(value) => setAttributes({ sectionTitle: value })}
					/>
					<TextControl
						label={__('Sous-titre', 'jurible-blocks-react')}
						value={sectionSubtitle}
						onChange={(value) => setAttributes({ sectionSubtitle: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Onglet Vidéo (dynamique)', 'jurible-blocks-react')} initialOpen={true}>
					<TextControl
						label={__('URL YouTube', 'jurible-blocks-react')}
						value={videoUrl}
						onChange={(value) => setAttributes({ videoUrl: value })}
						placeholder="https://youtube.com/watch?v=... ou https://youtu.be/..."
						help={__('Seul cet onglet est personnalisable. Les autres affichent des exemples fixes.', 'jurible-blocks-react')}
					/>
				</PanelBody>

				<PanelBody title={__('Bouton CTA', 'jurible-blocks-react')}>
					<TextControl
						label={__('Texte du bouton', 'jurible-blocks-react')}
						value={ctaText}
						onChange={(value) => setAttributes({ ctaText: value })}
					/>
					<TextControl
						label={__('URL', 'jurible-blocks-react')}
						value={ctaUrl}
						onChange={(value) => setAttributes({ ctaUrl: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Informations', 'jurible-blocks-react')} initialOpen={false}>
					<Notice status="info" isDismissible={false}>
						<strong>7 onglets fixes :</strong>
						<ul style={{ margin: '8px 0 0 16px', padding: 0 }}>
							<li>🎬 Vidéo — <em>URL personnalisable</em></li>
							<li>📄 Cours écrit — exemple fixe</li>
							<li>🗺️ Mindmap — exemple fixe</li>
							<li>✅ QCM — exemple interactif</li>
							<li>🃏 Flashcard — exemple interactif</li>
							<li>📚 Annale — exemple fixe</li>
							<li>🎥 Fiche vidéo — exemple fixe</li>
						</ul>
						<p style={{ marginTop: '8px', fontSize: '12px', color: '#666' }}>
							Les onglets avec exemples affichent le bandeau "Voici un exemple en droit constitutionnel"
						</p>
					</Notice>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{/* Header */}
				<div className="method-tabs__header">
					<span className="method-tabs__badge method-tabs__badge--green">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" width="14" height="14">
							<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
							<polyline points="7 10 12 15 17 10"/>
							<line x1="12" y1="15" x2="12" y2="3"/>
						</svg>
						{sectionBadge}
					</span>
					<RichText
						tagName="h2"
						className="method-tabs__title"
						value={sectionTitle}
						onChange={(value) => setAttributes({ sectionTitle: value })}
						placeholder={__('Titre de la section', 'jurible-blocks-react')}
						allowedFormats={['core/bold', 'core/italic']}
					/>
					<p className="method-tabs__subtitle">{sectionSubtitle}</p>
				</div>

				{/* Tabs Navigation */}
				<div className="method-tabs__nav">
					{TABS.map((tab, index) => (
						<button
							key={tab.id}
							className={`method-tabs__tab ${activeTab === index ? 'is-active' : ''}`}
							onClick={() => setActiveTab(index)}
							type="button"
						>
							<span className="method-tabs__tab-icon">{tab.icon}</span>
							<span className="method-tabs__tab-label">{tab.label}</span>
						</button>
					))}
				</div>

				{/* Preview Card */}
				<div className="method-tabs__preview-card">
					<div className="method-tabs__preview-content">
						{activeTab === 0 ? (
							// Video tab preview
							<div className="method-tabs__preview-video">
								{videoUrl ? (
									<div className="method-tabs__video-set">
										<span className="method-tabs__play-btn">▶</span>
										<p>Vidéo YouTube configurée</p>
									</div>
								) : (
									<div className="method-tabs__video-empty">
										<span className="method-tabs__play-btn">▶</span>
										<p>{__('Ajoutez une URL YouTube dans les paramètres', 'jurible-blocks-react')}</p>
									</div>
								)}
							</div>
						) : (
							// Other tabs preview
							<div className="method-tabs__preview-example">
								<div className="method-tabs__example-badge">
									Voici un exemple en droit constitutionnel
								</div>
								<div className="method-tabs__example-icon">{TABS[activeTab].icon}</div>
								<p className="method-tabs__example-label">{TABS[activeTab].label}</p>
								<p className="method-tabs__example-note">
									{__('Contenu d\'exemple fixe (non modifiable)', 'jurible-blocks-react')}
								</p>
							</div>
						)}
					</div>
				</div>

				{/* CTA */}
				<div className="method-tabs__cta-wrapper">
					<p className="method-tabs__cta-intro">⭐ {__('Vous aimez ce contenu ?', 'jurible-blocks-react')}</p>
					<span className="method-tabs__cta">{ctaText}</span>
				</div>
			</div>
		</>
	);
}
