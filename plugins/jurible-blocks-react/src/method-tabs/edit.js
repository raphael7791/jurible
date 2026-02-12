import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	Button,
	Card,
	CardBody,
	CardHeader,
	__experimentalHStack as HStack,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
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

	const [editingTabIndex, setEditingTabIndex] = useState(null);

	const blockProps = useBlockProps({
		className: 'wp-block-jurible-method-tabs',
	});

	const updateTab = (index, field, value) => {
		const newTabs = [...tabs];
		newTabs[index] = { ...newTabs[index], [field]: value };
		setAttributes({ tabs: newTabs });
	};

	const addTab = () => {
		const newTabs = [
			...tabs,
			{
				icon: '📌',
				label: 'Nouveau',
				videoUrl: '',
				videoTitle: '',
				contentTitle: 'Nouveau contenu',
				contentDescription: 'Description du contenu',
				linkText: 'Voir plus',
				linkUrl: '#',
			},
		];
		setAttributes({ tabs: newTabs });
	};

	const removeTab = (index) => {
		const newTabs = tabs.filter((_, i) => i !== index);
		setAttributes({
			tabs: newTabs,
			activeTab: Math.min(activeTab, newTabs.length - 1),
		});
	};

	const currentTab = tabs[activeTab] || tabs[0];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Section Header', 'jurible-blocks-react')}>
					<TextControl
						label={__('Badge', 'jurible-blocks-react')}
						value={sectionBadge}
						onChange={(value) => setAttributes({ sectionBadge: value })}
					/>
					<TextControl
						label={__('Titre', 'jurible-blocks-react')}
						value={sectionTitle}
						onChange={(value) => setAttributes({ sectionTitle: value })}
					/>
					<TextControl
						label={__('Sous-titre', 'jurible-blocks-react')}
						value={sectionSubtitle}
						onChange={(value) => setAttributes({ sectionSubtitle: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Onglets', 'jurible-blocks-react')} initialOpen={true}>
					<VStack spacing={3}>
						{tabs.map((tab, index) => (
							<Card key={index} size="small">
								<CardHeader>
									<HStack>
										<span>{tab.icon} {tab.label}</span>
										<HStack spacing={1}>
											<Button
												variant="tertiary"
												size="small"
												onClick={() => setEditingTabIndex(editingTabIndex === index ? null : index)}
											>
												{editingTabIndex === index ? __('Fermer', 'jurible-blocks-react') : __('Éditer', 'jurible-blocks-react')}
											</Button>
											{tabs.length > 1 && (
												<Button
													variant="tertiary"
													size="small"
													isDestructive
													onClick={() => removeTab(index)}
												>
													{__('Suppr.', 'jurible-blocks-react')}
												</Button>
											)}
										</HStack>
									</HStack>
								</CardHeader>
								{editingTabIndex === index && (
									<CardBody>
										<VStack spacing={3}>
											<HStack>
												<TextControl
													label={__('Icône', 'jurible-blocks-react')}
													value={tab.icon}
													onChange={(value) => updateTab(index, 'icon', value)}
													style={{ width: '80px' }}
												/>
												<TextControl
													label={__('Label', 'jurible-blocks-react')}
													value={tab.label}
													onChange={(value) => updateTab(index, 'label', value)}
												/>
											</HStack>
											<TextControl
												label={__('URL Vidéo (YouTube)', 'jurible-blocks-react')}
												value={tab.videoUrl}
												onChange={(value) => updateTab(index, 'videoUrl', value)}
												placeholder="https://youtube.com/watch?v=..."
											/>
											<TextControl
												label={__('Titre vidéo', 'jurible-blocks-react')}
												value={tab.videoTitle}
												onChange={(value) => updateTab(index, 'videoTitle', value)}
											/>
											<TextControl
												label={__('Titre contenu', 'jurible-blocks-react')}
												value={tab.contentTitle}
												onChange={(value) => updateTab(index, 'contentTitle', value)}
											/>
											<TextareaControl
												label={__('Description', 'jurible-blocks-react')}
												value={tab.contentDescription}
												onChange={(value) => updateTab(index, 'contentDescription', value)}
											/>
											<HStack>
												<TextControl
													label={__('Texte lien', 'jurible-blocks-react')}
													value={tab.linkText}
													onChange={(value) => updateTab(index, 'linkText', value)}
												/>
												<TextControl
													label={__('URL lien', 'jurible-blocks-react')}
													value={tab.linkUrl}
													onChange={(value) => updateTab(index, 'linkUrl', value)}
												/>
											</HStack>
										</VStack>
									</CardBody>
								)}
							</Card>
						))}
						<Button variant="secondary" onClick={addTab}>
							{__('+ Ajouter un onglet', 'jurible-blocks-react')}
						</Button>
					</VStack>
				</PanelBody>

				<PanelBody title={__('Bouton CTA', 'jurible-blocks-react')}>
					<TextControl
						label={__('Texte du bouton', 'jurible-blocks-react')}
						value={ctaText}
						onChange={(value) => setAttributes({ ctaText: value })}
					/>
					<TextControl
						label={__('Sous-texte', 'jurible-blocks-react')}
						value={ctaSubtext}
						onChange={(value) => setAttributes({ ctaSubtext: value })}
					/>
					<TextControl
						label={__('URL', 'jurible-blocks-react')}
						value={ctaUrl}
						onChange={(value) => setAttributes({ ctaUrl: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{/* Header */}
				<div className="method-tabs__header">
					<span className="method-tabs__badge">{sectionBadge}</span>
					<RichText
						tagName="h2"
						className="method-tabs__title"
						value={sectionTitle}
						onChange={(value) => setAttributes({ sectionTitle: value })}
						placeholder={__('Titre de la section', 'jurible-blocks-react')}
					/>
					<p className="method-tabs__subtitle">{sectionSubtitle}</p>
				</div>

				{/* Tabs Navigation */}
				<div className="method-tabs__nav">
					{tabs.map((tab, index) => (
						<button
							key={index}
							className={`method-tabs__tab ${activeTab === index ? 'is-active' : ''}`}
							onClick={() => setAttributes({ activeTab: index })}
							type="button"
						>
							<span className="method-tabs__tab-icon">{tab.icon}</span>
							<span className="method-tabs__tab-label">{tab.label}</span>
						</button>
					))}
				</div>

				{/* Tab Content */}
				<div className="method-tabs__content">
					<div className="method-tabs__media">
						{currentTab.videoUrl ? (
							<div className="method-tabs__video-wrapper">
								<div className="method-tabs__video-placeholder">
									<span className="method-tabs__play-icon">▶</span>
									<p>{currentTab.videoTitle || 'Vidéo'}</p>
								</div>
							</div>
						) : (
							<div className="method-tabs__video-placeholder">
								<span className="method-tabs__play-icon">▶</span>
								<p>{__('Aucune vidéo', 'jurible-blocks-react')}</p>
							</div>
						)}
					</div>
					<div className="method-tabs__info">
						<h3 className="method-tabs__content-title">{currentTab.contentTitle}</h3>
						<p className="method-tabs__content-desc">{currentTab.contentDescription}</p>
						{currentTab.linkText && (
							<a href={currentTab.linkUrl} className="method-tabs__link">
								{currentTab.linkText}
							</a>
						)}
					</div>
				</div>

				{/* CTA */}
				<div className="method-tabs__cta-wrapper">
					<p className="method-tabs__cta-intro">{__('Vous aimez ce contenu ?', 'jurible-blocks-react')}</p>
					<a href={ctaUrl} className="method-tabs__cta">
						{ctaText} <span className="method-tabs__cta-subtext">{ctaSubtext}</span>
					</a>
				</div>
			</div>
		</>
	);
}
