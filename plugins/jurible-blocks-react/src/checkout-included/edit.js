import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { title, items } = attributes;

	const blockProps = useBlockProps({
		className: 'checkout-included'
	});

	const updateItem = (index, value) => {
		const newItems = [...items];
		newItems[index] = value;
		setAttributes({ items: newItems });
	};

	const addItem = () => {
		setAttributes({ items: [...items, 'Nouvel élément'] });
	};

	const removeItem = (index) => {
		const newItems = items.filter((_, i) => i !== index);
		setAttributes({ items: newItems });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Contenu" initialOpen={true}>
					<TextControl
						label="Titre"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
				</PanelBody>
				<PanelBody title="Éléments inclus" initialOpen={true}>
					{items.map((item, index) => (
						<div key={index} style={{ display: 'flex', gap: '8px', marginBottom: '8px', alignItems: 'center' }}>
							<TextControl
								value={item}
								onChange={(value) => updateItem(index, value)}
								style={{ flex: 1, marginBottom: 0 }}
							/>
							<Button
								icon="trash"
								isDestructive
								onClick={() => removeItem(index)}
								label="Supprimer"
							/>
						</div>
					))}
					<Button
						variant="secondary"
						onClick={addItem}
						style={{ marginTop: '8px' }}
					>
						+ Ajouter un élément
					</Button>
				</PanelBody>
			</InspectorControls>

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
		</>
	);
}
