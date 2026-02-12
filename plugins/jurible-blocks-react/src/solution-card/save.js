import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const blockProps = useBlockProps.save({
		className: `solution-card solution-card--${attributes.layout}`
	});

	return (
		<div {...blockProps}>
			<div className="solution-card__icon">{attributes.icon}</div>
			<div className="solution-card__content">
				<h4 className="solution-card__title">{attributes.title}</h4>
				<p className="solution-card__description">{attributes.description}</p>
			</div>
		</div>
	);
}
