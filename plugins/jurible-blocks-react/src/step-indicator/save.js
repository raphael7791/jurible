import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { totalSteps, currentStep, showLabels, labels } = attributes;
	const blockProps = useBlockProps.save({ className: 'jurible-steps' });

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
		<div {...blockProps}>
			{renderSteps()}
		</div>
	);
}
