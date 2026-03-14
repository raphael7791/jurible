import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { title, questions, shuffleAnswers, showExplanations } = attributes;
	const blockProps = useBlockProps.save();

	if (!questions || questions.length === 0) {
		return null;
	}

	return (
		<div {...blockProps}>
			<div
				className="jurible-qcm-container"
				data-questions={JSON.stringify(questions)}
				data-shuffle={shuffleAnswers ? 'true' : 'false'}
				data-explanations={showExplanations ? 'true' : 'false'}
				data-title={title}
			>
				<div className="qcm-seo-content">
					<h3 className="qcm-seo-title">{title}</h3>
					{questions.map((q, i) => (
						<details key={i} className="qcm-seo-question">
							<summary>{q.question}</summary>
							<ul>
								{q.answers.map((answer, j) => (
									<li key={j} className={j === q.correctIndex ? 'qcm-correct' : ''}>
										{answer}
										{j === q.correctIndex && ' ✓'}
									</li>
								))}
							</ul>
							{q.explanation && (
								<p className="qcm-seo-explanation">{q.explanation}</p>
							)}
						</details>
					))}
				</div>
			</div>
		</div>
	);
}
