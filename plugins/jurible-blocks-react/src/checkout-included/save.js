import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { title, items } = attributes;

	const blockProps = useBlockProps.save({
		className: 'checkout-included'
	});

	return (
		<div {...blockProps}>
			<p className="checkout-included__title">✅ {title}</p>
			<ul className="checkout-included__list">
				{items.map((item, index) => (
					<li key={index} className="checkout-included__item">
						<span className="checkout-included__check">✓</span>
						<span className="checkout-included__text">{item}</span>
					</li>
				))}
			</ul>
		</div>
	);
}
