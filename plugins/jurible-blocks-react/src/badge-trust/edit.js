import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const {
		variant,
		icon,
		title,
		subtitle,
		rating,
		reviewCount
	} = attributes;

	const blockProps = useBlockProps({
		className: `badge-trust ${variant === 'compact' ? 'badge-trust--compact' : ''}`
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Variante" initialOpen={true}>
					<SelectControl
						label="Type de badge"
						value={variant}
						options={[
							{ label: 'Complet', value: 'full' },
							{ label: 'Compact', value: 'compact' }
						]}
						onChange={(value) => setAttributes({ variant: value })}
					/>
				</PanelBody>

				<PanelBody title="Contenu" initialOpen={true}>
					<TextControl
						label="Icône (emoji)"
						value={icon}
						onChange={(value) => setAttributes({ icon: value })}
						help="Ex: 🎓, ⭐, 🏆"
					/>
					<TextControl
						label="Titre"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					{variant === 'full' && (
						<TextControl
							label="Sous-titre"
							value={subtitle}
							onChange={(value) => setAttributes({ subtitle: value })}
						/>
					)}
				</PanelBody>

				<PanelBody title="Note" initialOpen={false}>
					<TextControl
						label="Note"
						value={rating}
						onChange={(value) => setAttributes({ rating: value })}
						help="Ex: 4.8"
					/>
					{variant === 'full' && (
						<TextControl
							label="Nombre d'avis"
							value={reviewCount}
							onChange={(value) => setAttributes({ reviewCount: value })}
							help="Ex: +150 avis"
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="badge-trust__icon">{icon}</div>
				<div className="badge-trust__content">
					<h4 className="badge-trust__title">{title}</h4>
					{variant === 'full' && subtitle && (
						<p className="badge-trust__subtitle">{subtitle}</p>
					)}
				</div>
				<div className="badge-trust__rating">
					<div className="badge-trust__stars">
						<span className="badge-trust__star">★</span>
						<span className="badge-trust__star">★</span>
						<span className="badge-trust__star">★</span>
						<span className="badge-trust__star">★</span>
						<span className="badge-trust__star">★</span>
					</div>
					<span className="badge-trust__score">{rating}/5</span>
					{variant === 'full' && reviewCount && (
						<span className="badge-trust__count">({reviewCount})</span>
					)}
				</div>
			</div>
		</>
	);
}
