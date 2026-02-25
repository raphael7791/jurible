import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { items } = attributes;

	const blockProps = useBlockProps({
		className: 'checkout-reassurance'
	});

	const updateItem = (index, field, value) => {
		const newItems = [...items];
		newItems[index] = { ...newItems[index], [field]: value };
		setAttributes({ items: newItems });
	};

	const addItem = () => {
		setAttributes({
			items: [...items, { icon: '✓', title: 'Nouveau', description: 'Description' }]
		});
	};

	const removeItem = (index) => {
		const newItems = items.filter((_, i) => i !== index);
		setAttributes({ items: newItems });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Éléments de réassurance" initialOpen={true}>
					{items.map((item, index) => (
						<div key={index} style={{ marginBottom: '16px', padding: '12px', background: '#f0f0f0', borderRadius: '4px' }}>
							<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
								<strong>Élément {index + 1}</strong>
								<Button
									icon="trash"
									isDestructive
									isSmall
									onClick={() => removeItem(index)}
									label="Supprimer"
								/>
							</div>
							<TextControl
								label="Icône (emoji)"
								value={item.icon}
								onChange={(value) => updateItem(index, 'icon', value)}
							/>
							<TextControl
								label="Titre"
								value={item.title}
								onChange={(value) => updateItem(index, 'title', value)}
							/>
							<TextControl
								label="Description"
								value={item.description}
								onChange={(value) => updateItem(index, 'description', value)}
							/>
						</div>
					))}
					<Button
						variant="secondary"
						onClick={addItem}
					>
						+ Ajouter un élément
					</Button>
				</PanelBody>
			</InspectorControls>

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
		</>
	);
}
