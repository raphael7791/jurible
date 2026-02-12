import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { totalSteps, currentStep, showLabels, labels } = attributes;
	const blockProps = useBlockProps({ className: 'jurible-steps' });

	// Update labels array when totalSteps changes
	const updateTotalSteps = (value) => {
		const newLabels = [...labels];
		while (newLabels.length < value) {
			newLabels.push(`Étape ${newLabels.length + 1}`);
		}
		setAttributes({
			totalSteps: value,
			labels: newLabels.slice(0, value),
			currentStep: Math.min(currentStep, value)
		});
	};

	// Update a specific label
	const updateLabel = (index, value) => {
		const newLabels = [...labels];
		while (newLabels.length <= index) {
			newLabels.push(`Étape ${newLabels.length + 1}`);
		}
		newLabels[index] = value;
		setAttributes({ labels: newLabels });
	};

	// Render steps
	const renderSteps = () => {
		const steps = [];
		for (let i = 1; i <= totalSteps; i++) {
			const isCompleted = i < currentStep;
			const isActive = i === currentStep;

			let stepClass = 'jurible-step';
			if (isCompleted) stepClass += ' jurible-step--completed';
			if (isActive) stepClass += ' jurible-step--active';
			if (showLabels) stepClass += ' jurible-step--with-label';

			steps.push(
				<div key={i} className={stepClass}>
					{showLabels ? (
						<>
							{i > 1 && <div className="jurible-step__line"></div>}
							<div className="jurible-step__circle-wrapper">
								<div className="jurible-step__circle">
									{isCompleted ? '✓' : i}
								</div>
								{labels[i - 1] && (
									<span className="jurible-step__label">{labels[i - 1]}</span>
								)}
							</div>
						</>
					) : (
						<>
							{i > 1 && <div className="jurible-step__line"></div>}
							<div className="jurible-step__circle">
								{isCompleted ? '✓' : i}
							</div>
						</>
					)}
				</div>
			);
		}
		return steps;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Configuration">
					<RangeControl
						label="Nombre d'étapes"
						value={totalSteps}
						onChange={updateTotalSteps}
						min={2}
						max={10}
					/>
					<RangeControl
						label="Étape active"
						value={currentStep}
						onChange={(value) => setAttributes({ currentStep: value })}
						min={1}
						max={totalSteps}
					/>
					<ToggleControl
						label="Afficher les labels"
						checked={showLabels}
						onChange={(value) => setAttributes({ showLabels: value })}
					/>
				</PanelBody>
				{showLabels && (
					<PanelBody title="Labels" initialOpen={false}>
						{Array.from({ length: totalSteps }, (_, i) => (
							<TextControl
								key={i}
								label={`Étape ${i + 1}`}
								value={labels[i] || `Étape ${i + 1}`}
								onChange={(value) => updateLabel(i, value)}
							/>
						))}
					</PanelBody>
				)}
			</InspectorControls>
			<div {...blockProps}>
				{renderSteps()}
			</div>
		</>
	);
}
