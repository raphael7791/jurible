import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const blockProps = useBlockProps.save({
		className: `cta-banner cta-banner--${attributes.variant}`
	});

	// Classe du bouton selon la variante
	const buttonClass = attributes.variant === 'gradient'
		? 'cta-banner__btn cta-banner__btn--white'
		: 'cta-banner__btn cta-banner__btn--primary';

	return (
		<div {...blockProps}>
			<div className="cta-banner__icon">{attributes.icon}</div>
			<div className="cta-banner__content">
				<p className="cta-banner__title">{attributes.title}</p>
				<p className="cta-banner__desc">{attributes.description}</p>
			</div>
			<div className="cta-banner__action">
				<a href={attributes.buttonUrl} className={buttonClass}>
					{attributes.buttonText}
				</a>
			</div>
		</div>
	);
}
