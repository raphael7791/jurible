import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { items } = attributes;

	const blockProps = useBlockProps.save({
		className: 'checkout-reassurance'
	});

	return (
		<div {...blockProps}>
			<div className="checkout-reassurance__list">
				{items.map((item, index) => (
					<div key={index} className="checkout-reassurance__item">
						<div className="checkout-reassurance__icon">{item.icon}</div>
						<div className="checkout-reassurance__content">
							<div className="checkout-reassurance__title">{item.title}</div>
							<div className="checkout-reassurance__desc">{item.description}</div>
						</div>
					</div>
				))}
			</div>
		</div>
	);
}
