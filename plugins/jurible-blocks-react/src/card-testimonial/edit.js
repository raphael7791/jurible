import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl, Button, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const {
		variant,
		rating,
		quote,
		showBadge,
		badgeText,
		avatarType,
		avatarUrl,
		avatarId,
		avatarInitials,
		authorName,
		authorSubtitle
	} = attributes;

	const blockProps = useBlockProps({
		className: `card-testimonial card-testimonial--${variant}`
	});

	const onSelectAvatar = (media) => {
		setAttributes({
			avatarUrl: media.url,
			avatarId: media.id
		});
	};

	const onRemoveAvatar = () => {
		setAttributes({
			avatarUrl: '',
			avatarId: 0
		});
	};

	const renderStars = () => {
		const stars = [];
		for (let i = 0; i < 5; i++) {
			stars.push(
				<span key={i} className={`card-testimonial__star ${i < rating ? 'card-testimonial__star--filled' : ''}`}>
					★
				</span>
			);
		}
		return stars;
	};

	const renderAvatar = () => {
		if (avatarType === 'photo' && avatarUrl) {
			return (
				<div
					className={`card-testimonial__avatar card-testimonial__avatar--photo ${variant === 'hero' ? 'card-testimonial__avatar--lg' : ''}`}
					style={{ backgroundImage: `url(${avatarUrl})` }}
				/>
			);
		}
		return (
			<div className={`card-testimonial__avatar card-testimonial__avatar--initials ${variant === 'hero' ? 'card-testimonial__avatar--lg' : ''}`}>
				{avatarInitials}
			</div>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Variante" initialOpen={true}>
					<SelectControl
						label="Type de carte"
						value={variant}
						options={[
							{ label: 'Standard (360px)', value: 'standard' },
							{ label: 'Hero (480px)', value: 'hero' }
						]}
						onChange={(value) => setAttributes({ variant: value })}
					/>
				</PanelBody>

				<PanelBody title="Note" initialOpen={false}>
					<RangeControl
						label="Étoiles"
						value={rating}
						onChange={(value) => setAttributes({ rating: value })}
						min={1}
						max={5}
					/>
				</PanelBody>

				<PanelBody title="Badge résultat" initialOpen={false}>
					<ToggleControl
						label="Afficher le badge"
						checked={showBadge}
						onChange={(value) => setAttributes({ showBadge: value })}
					/>
					{showBadge && (
						<TextControl
							label="Texte du badge"
							value={badgeText}
							onChange={(value) => setAttributes({ badgeText: value })}
						/>
					)}
				</PanelBody>

				<PanelBody title="Avatar" initialOpen={false}>
					<SelectControl
						label="Type d'avatar"
						value={avatarType}
						options={[
							{ label: 'Initiales', value: 'initials' },
							{ label: 'Photo', value: 'photo' }
						]}
						onChange={(value) => setAttributes({ avatarType: value })}
					/>
					{avatarType === 'initials' && (
						<TextControl
							label="Initiales"
							value={avatarInitials}
							onChange={(value) => setAttributes({ avatarInitials: value.toUpperCase().slice(0, 2) })}
							maxLength={2}
							help="2 caractères maximum"
						/>
					)}
					{avatarType === 'photo' && (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={onSelectAvatar}
								allowedTypes={['image']}
								value={avatarId}
								render={({ open }) => (
									<div style={{ marginBottom: '16px' }}>
										{avatarUrl ? (
											<>
												<img
													src={avatarUrl}
													alt=""
													style={{ width: '64px', height: '64px', borderRadius: '50%', objectFit: 'cover', marginBottom: '8px' }}
												/>
												<div>
													<Button variant="secondary" onClick={open} style={{ marginRight: '8px' }}>
														Changer
													</Button>
													<Button variant="link" isDestructive onClick={onRemoveAvatar}>
														Supprimer
													</Button>
												</div>
											</>
										) : (
											<Button variant="secondary" onClick={open}>
												Choisir une photo
											</Button>
										)}
									</div>
								)}
							/>
						</MediaUploadCheck>
					)}
				</PanelBody>

				<PanelBody title="Auteur" initialOpen={false}>
					<TextControl
						label="Nom"
						value={authorName}
						onChange={(value) => setAttributes({ authorName: value })}
					/>
					<TextControl
						label="Sous-titre"
						value={authorSubtitle}
						onChange={(value) => setAttributes({ authorSubtitle: value })}
						help="Ex: L1 Droit — Paris 1"
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="card-testimonial__stars">
					{renderStars()}
				</div>
				<RichText
					tagName="p"
					className="card-testimonial__quote"
					value={quote}
					onChange={(value) => setAttributes({ quote: value })}
					placeholder="Citation du témoignage..."
				/>
				{showBadge && (
					<div className="card-testimonial__badge">
						✓ {badgeText}
					</div>
				)}
				<div className="card-testimonial__author">
					{renderAvatar()}
					<div className="card-testimonial__author-info">
						<h4 className="card-testimonial__author-name">{authorName}</h4>
						<p className="card-testimonial__author-subtitle">{authorSubtitle}</p>
					</div>
				</div>
			</div>
		</>
	);
}
