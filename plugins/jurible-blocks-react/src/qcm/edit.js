import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
	const { title, questions, shuffleAnswers, showExplanations } = attributes;
	const blockProps = useBlockProps();

	const [importMode, setImportMode] = useState(questions.length === 0);
	const [importText, setImportText] = useState('');
	const [importError, setImportError] = useState('');
	const [openQuestion, setOpenQuestion] = useState(null);

	// Parse import text into questions array
	function parseImportText(text) {
		const parsed = [];
		const blocks = text.split(/\n\s*\n/).filter(b => b.trim());

		for (const block of blocks) {
			const lines = block.split('\n').map(l => l.trim()).filter(l => l);
			const questionLine = lines.find(l => l.startsWith('Q:'));
			if (!questionLine) continue;

			const question = questionLine.replace(/^Q:\s*/, '').trim();
			const answerLines = lines.filter(l => l.startsWith('-'));
			const explanationLine = lines.find(l => l.startsWith('E:'));

			if (answerLines.length < 2) continue;

			let correctIndex = -1;
			const answers = answerLines.map((line, i) => {
				const isCorrect = line.includes('*');
				if (isCorrect) correctIndex = i;
				return line.replace(/^-\s*/, '').replace(/\s*\*\s*$/, '').replace(/\*\s*/, '').trim();
			});

			if (correctIndex === -1) correctIndex = 0;

			parsed.push({
				question,
				answers,
				correctIndex,
				explanation: explanationLine ? explanationLine.replace(/^E:\s*/, '').trim() : '',
			});
		}

		return parsed;
	}

	function handleImport() {
		const parsed = parseImportText(importText);
		if (parsed.length === 0) {
			setImportError('Aucune question valide trouvée. Vérifiez le format.');
			return;
		}
		setAttributes({ questions: parsed });
		setImportError('');
		setImportText('');
		setImportMode(false);
	}

	function updateQuestion(index, field, value) {
		const updated = [...questions];
		updated[index] = { ...updated[index], [field]: value };
		setAttributes({ questions: updated });
	}

	function updateAnswer(qIndex, aIndex, value) {
		const updated = [...questions];
		const answers = [...updated[qIndex].answers];
		answers[aIndex] = value;
		updated[qIndex] = { ...updated[qIndex], answers };
		setAttributes({ questions: updated });
	}

	function deleteQuestion(index) {
		const updated = questions.filter((_, i) => i !== index);
		setAttributes({ questions: updated });
		if (updated.length === 0) setImportMode(true);
	}

	function moveQuestion(index, direction) {
		const newIndex = index + direction;
		if (newIndex < 0 || newIndex >= questions.length) return;
		const updated = [...questions];
		[updated[index], updated[newIndex]] = [updated[newIndex], updated[index]];
		setAttributes({ questions: updated });
		setOpenQuestion(newIndex);
	}

	function addQuestion() {
		const updated = [...questions, {
			question: '',
			answers: ['', '', '', ''],
			correctIndex: 0,
			explanation: '',
		}];
		setAttributes({ questions: updated });
		setOpenQuestion(updated.length - 1);
	}

	function addAnswer(qIndex) {
		const updated = [...questions];
		const answers = [...updated[qIndex].answers, ''];
		updated[qIndex] = { ...updated[qIndex], answers };
		setAttributes({ questions: updated });
	}

	function removeAnswer(qIndex, aIndex) {
		const updated = [...questions];
		if (updated[qIndex].answers.length <= 2) return;
		const answers = updated[qIndex].answers.filter((_, i) => i !== aIndex);
		let { correctIndex } = updated[qIndex];
		if (aIndex === correctIndex) correctIndex = 0;
		else if (aIndex < correctIndex) correctIndex--;
		updated[qIndex] = { ...updated[qIndex], answers, correctIndex };
		setAttributes({ questions: updated });
	}

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Paramètres du QCM">
					<TextControl
						label="Titre du QCM"
						value={title}
						onChange={(val) => setAttributes({ title: val })}
					/>
					<ToggleControl
						label="Mélanger les réponses"
						checked={shuffleAnswers}
						onChange={(val) => setAttributes({ shuffleAnswers: val })}
					/>
					<ToggleControl
						label="Afficher les explications"
						checked={showExplanations}
						onChange={(val) => setAttributes({ showExplanations: val })}
					/>
					<p className="qcm-editor-count">
						{questions.length} question{questions.length > 1 ? 's' : ''}
					</p>
				</PanelBody>
			</InspectorControls>

			<div className="qcm-editor">
				<div className="qcm-editor-header">
					<span className="qcm-icon">📝</span>
					<span className="qcm-title">{title || 'QCM Jurible'}</span>
					{questions.length > 0 && (
						<span className="qcm-badge">{questions.length} question{questions.length > 1 ? 's' : ''}</span>
					)}
				</div>

				{importMode ? (
					<div className="qcm-import">
						<p className="qcm-import-help">
							Collez vos questions au format suivant :
						</p>
						<pre className="qcm-import-format">{`Q: Votre question ici ?
- Réponse incorrecte
- Bonne réponse *
- Autre réponse incorrecte
- Encore une réponse
E: Explication optionnelle

Q: Question suivante...`}</pre>
						<textarea
							className="qcm-import-textarea"
							value={importText}
							onChange={(e) => setImportText(e.target.value)}
							placeholder="Collez vos questions ici..."
							rows={12}
						/>
						{importError && (
							<p className="qcm-import-error">{importError}</p>
						)}
						<div className="qcm-import-actions">
							<Button variant="primary" onClick={handleImport} disabled={!importText.trim()}>
								Importer les questions
							</Button>
							{questions.length > 0 && (
								<Button variant="secondary" onClick={() => setImportMode(false)}>
									Annuler
								</Button>
							)}
						</div>
					</div>
				) : (
					<div className="qcm-visual">
						<div className="qcm-questions-list">
							{questions.map((q, qIndex) => (
								<div key={qIndex} className={`qcm-question-item ${openQuestion === qIndex ? 'is-open' : ''}`}>
									<div
										className="qcm-question-header"
										onClick={() => setOpenQuestion(openQuestion === qIndex ? null : qIndex)}
									>
										<span className="qcm-question-num">Q{qIndex + 1}</span>
										<span className="qcm-question-preview">
											{q.question || '(Question vide)'}
										</span>
										<span className="qcm-question-toggle">
											{openQuestion === qIndex ? '▲' : '▼'}
										</span>
									</div>

									{openQuestion === qIndex && (
										<div className="qcm-question-body">
											<TextControl
												label="Question"
												value={q.question}
												onChange={(val) => updateQuestion(qIndex, 'question', val)}
											/>

											<div className="qcm-answers">
												<label className="qcm-answers-label">Réponses (cochez la bonne)</label>
												{q.answers.map((answer, aIndex) => (
													<div key={aIndex} className="qcm-answer-row">
														<input
															type="radio"
															name={`correct-${qIndex}`}
															checked={q.correctIndex === aIndex}
															onChange={() => updateQuestion(qIndex, 'correctIndex', aIndex)}
														/>
														<input
															type="text"
															className="qcm-answer-input"
															value={answer}
															onChange={(e) => updateAnswer(qIndex, aIndex, e.target.value)}
															placeholder={`Réponse ${aIndex + 1}`}
														/>
														{q.answers.length > 2 && (
															<button
																className="qcm-answer-remove"
																onClick={() => removeAnswer(qIndex, aIndex)}
																title="Supprimer cette réponse"
															>
																×
															</button>
														)}
													</div>
												))}
												<Button
													variant="link"
													className="qcm-add-answer"
													onClick={() => addAnswer(qIndex)}
												>
													+ Ajouter une réponse
												</Button>
											</div>

											<TextControl
												label="Explication (optionnel)"
												value={q.explanation}
												onChange={(val) => updateQuestion(qIndex, 'explanation', val)}
											/>

											<div className="qcm-question-actions">
												<Button
													variant="secondary"
													size="small"
													onClick={() => moveQuestion(qIndex, -1)}
													disabled={qIndex === 0}
												>
													↑ Monter
												</Button>
												<Button
													variant="secondary"
													size="small"
													onClick={() => moveQuestion(qIndex, 1)}
													disabled={qIndex === questions.length - 1}
												>
													↓ Descendre
												</Button>
												<Button
													variant="link"
													isDestructive
													size="small"
													onClick={() => deleteQuestion(qIndex)}
												>
													Supprimer
												</Button>
											</div>
										</div>
									)}
								</div>
							))}
						</div>

						<div className="qcm-visual-actions">
							<Button variant="secondary" onClick={addQuestion}>
								+ Ajouter une question
							</Button>
							<Button variant="tertiary" onClick={() => setImportMode(true)}>
								Ré-importer
							</Button>
						</div>
					</div>
				)}
			</div>
		</div>
	);
}
