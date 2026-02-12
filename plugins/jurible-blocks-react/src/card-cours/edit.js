import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const {
		imageUrl,
		imageId,
		badgeIcon,
		showBadge,
		title,
		videosCount,
		fichesCount,
		qcmCount,
		flashcardsCount,
		annalesCount,
		mindmapsCount,
		showIncludedLabel,
		includedLabelText,
		linkText,
		linkUrl
	} = attributes;

	const blockProps = useBlockProps({ className: 'card-cours' });

	const onSelectImage = (media) => {
		setAttributes({
			imageUrl: media.url,
			imageId: media.id
		});
	};

	const onRemoveImage = () => {
		setAttributes({
			imageUrl: '',
			imageId: 0
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Image" initialOpen={true}>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={onSelectImage}
							allowedTypes={['image']}
							value={imageId}
							render={({ open }) => (
								<div style={{ marginBottom: '16px' }}>
									{imageUrl ? (
										<>
											<img
												src={imageUrl}
												alt=""
												style={{ width: '100%', marginBottom: '8px', borderRadius: '8px' }}
											/>
											<Button variant="secondary" onClick={open} style={{ marginRight: '8px' }}>
												Changer l'image
											</Button>
											<Button variant="link" isDestructive onClick={onRemoveImage}>
												Supprimer
											</Button>
										</>
									) : (
										<Button variant="secondary" onClick={open}>
											Choisir une image
										</Button>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelBody title="Badge" initialOpen={false}>
					<ToggleControl
						label="Afficher le badge"
						checked={showBadge}
						onChange={(value) => setAttributes({ showBadge: value })}
					/>
					{showBadge && (
						<TextControl
							label="Icone du badge (emoji)"
							value={badgeIcon}
							onChange={(value) => setAttributes({ badgeIcon: value })}
							placeholder="Ex: fire, star"
							help="Emojis suggeres : fire, star, zap, sparkles"
						/>
					)}
				</PanelBody>

				<PanelBody title="Statistiques" initialOpen={false}>
					<RangeControl
						label="Videos"
						value={videosCount}
						onChange={(value) => setAttributes({ videosCount: value })}
						min={0}
						max={999}
					/>
					<RangeControl
						label="Fiches"
						value={fichesCount}
						onChange={(value) => setAttributes({ fichesCount: value })}
						min={0}
						max={999}
					/>
					<RangeControl
						label="QCM"
						value={qcmCount}
						onChange={(value) => setAttributes({ qcmCount: value })}
						min={0}
						max={999}
					/>
					<RangeControl
						label="Flashcards"
						value={flashcardsCount}
						onChange={(value) => setAttributes({ flashcardsCount: value })}
						min={0}
						max={999}
					/>
					<RangeControl
						label="Annales"
						value={annalesCount}
						onChange={(value) => setAttributes({ annalesCount: value })}
						min={0}
						max={999}
					/>
					<RangeControl
						label="Mindmaps"
						value={mindmapsCount}
						onChange={(value) => setAttributes({ mindmapsCount: value })}
						min={0}
						max={999}
					/>
				</PanelBody>

				<PanelBody title="Label inclus" initialOpen={false}>
					<ToggleControl
						label="Afficher le label"
						checked={showIncludedLabel}
						onChange={(value) => setAttributes({ showIncludedLabel: value })}
					/>
					{showIncludedLabel && (
						<TextControl
							label="Texte du label"
							value={includedLabelText}
							onChange={(value) => setAttributes({ includedLabelText: value })}
						/>
					)}
				</PanelBody>

				<PanelBody title="Lien" initialOpen={false}>
					<TextControl
						label="URL du lien"
						value={linkUrl}
						onChange={(value) => setAttributes({ linkUrl: value })}
						type="url"
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div
					className="card-cours__image"
					style={imageUrl ? { backgroundImage: `url(${imageUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}
				>
					{!imageUrl && <span className="card-cours__image-icon">🎬</span>}
					{showBadge && badgeIcon && (
						<span className="card-cours__badge">{badgeIcon}</span>
					)}
				</div>
				<div className="card-cours__content">
					<RichText
						tagName="h3"
						className="card-cours__title"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						placeholder="Titre du cours"
					/>
					<div className="card-cours__stats">
						{videosCount > 0 && (
							<span className="card-cours__stat">
								<span className="card-cours__stat-icon">🎬</span>
								{videosCount} videos
							</span>
						)}
						{fichesCount > 0 && (
							<span className="card-cours__stat">
								<span className="card-cours__stat-icon">📄</span>
								{fichesCount} fiches
							</span>
						)}
						{qcmCount > 0 && (
							<span className="card-cours__stat">
								<span className="card-cours__stat-icon">❓</span>
								{qcmCount} QCM
							</span>
						)}
						{flashcardsCount > 0 && (
							<span className="card-cours__stat">
								<span className="card-cours__stat-icon">🎴</span>
								{flashcardsCount} flashcards
							</span>
						)}
						{annalesCount > 0 && (
							<span className="card-cours__stat">
								<span className="card-cours__stat-icon">📝</span>
								{annalesCount} annales
							</span>
						)}
						{mindmapsCount > 0 && (
							<span className="card-cours__stat">
								<span className="card-cours__stat-icon">🧠</span>
								{mindmapsCount} mindmaps
							</span>
						)}
					</div>
					{showIncludedLabel && (
						<div className="card-cours__included">
							✓ {includedLabelText}
						</div>
					)}
					<div className="card-cours__footer">
						<RichText
							tagName="span"
							className="card-cours__link"
							value={linkText}
							onChange={(value) => setAttributes({ linkText: value })}
							placeholder="Voir le cours"
						/>
						<span className="card-cours__link-arrow">→</span>
					</div>
				</div>
			</div>
		</>
	);
}
