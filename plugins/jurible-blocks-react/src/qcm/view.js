// QCM View - Front-end interactivity
function initQcm(container) {
    if (container.dataset.initialized === 'true') return;
    container.dataset.initialized = 'true';

    const rawQuestions = container.dataset.questions;
    if (!rawQuestions) {
        container.innerHTML = '<div class="qcm-error"><p>Aucune question disponible</p></div>';
        return;
    }

    let questions;
    try {
        questions = JSON.parse(rawQuestions);
    } catch (e) {
        container.innerHTML = '<div class="qcm-error"><p>Erreur de chargement du quiz</p></div>';
        return;
    }

    if (!questions || questions.length === 0) {
        container.innerHTML = '<div class="qcm-error"><p>Aucune question disponible</p></div>';
        return;
    }

    const shouldShuffle = container.dataset.shuffle !== 'false';
    const showExplanations = container.dataset.explanations !== 'false';
    const title = container.dataset.title || 'Quiz';

    // Prepare questions with shuffled answers
    const preparedQuestions = questions.map(q => {
        const indexed = q.answers.map((text, i) => ({ text, originalIndex: i }));
        if (shouldShuffle) {
            for (let i = indexed.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [indexed[i], indexed[j]] = [indexed[j], indexed[i]];
            }
        }
        return {
            question: q.question,
            answers: indexed,
            correctOriginalIndex: q.correctIndex,
            explanation: q.explanation || '',
        };
    });

    let currentIndex = 0;
    let answered = false;
    let selectedIndex = null;
    let results = [];

    function render() {
        if (currentIndex >= preparedQuestions.length) {
            renderResults();
            return;
        }

        const q = preparedQuestions[currentIndex];
        const progress = ((currentIndex) / preparedQuestions.length) * 100;

        container.innerHTML = `
            <div class="qcm-app">
                <!-- Progress -->
                <div class="qcm-progress-section">
                    <div class="qcm-progress-row">
                        <span class="qcm-progress-title">${escapeHtml(title)}</span>
                        <span class="qcm-progress-count">${currentIndex + 1}/${preparedQuestions.length}</span>
                    </div>
                    <div class="qcm-progress-bar">
                        <div class="qcm-progress-fill" style="width: ${progress}%"></div>
                    </div>
                </div>

                <!-- Question Card -->
                <div class="qcm-card">
                    <div class="qcm-card-header">
                        <span class="qcm-card-label">Question ${currentIndex + 1}</span>
                        <span class="qcm-card-num">${currentIndex + 1}/${preparedQuestions.length}</span>
                    </div>
                    <div class="qcm-card-body">
                        <p class="qcm-question-text">${escapeHtml(q.question)}</p>
                    </div>
                </div>

                <!-- Answers -->
                <div class="qcm-answers-grid">
                    ${q.answers.map((a, i) => {
                        let stateClass = '';
                        if (answered) {
                            if (a.originalIndex === q.correctOriginalIndex) {
                                stateClass = 'qcm-answer-correct';
                            } else if (i === selectedIndex) {
                                stateClass = 'qcm-answer-incorrect';
                            } else {
                                stateClass = 'qcm-answer-disabled';
                            }
                        }
                        return `
                            <button class="qcm-answer-btn ${stateClass}" data-index="${i}" ${answered ? 'disabled' : ''}>
                                <span class="qcm-answer-letter">${String.fromCharCode(65 + i)}</span>
                                <span class="qcm-answer-text">${escapeHtml(a.text)}</span>
                            </button>
                        `;
                    }).join('')}
                </div>

                <!-- Explanation -->
                ${answered && showExplanations && q.explanation ? `
                    <div class="qcm-explanation">
                        <span class="qcm-explanation-icon">ℹ️</span>
                        <p>${escapeHtml(q.explanation)}</p>
                    </div>
                ` : ''}

                <!-- Next button -->
                ${answered ? `
                    <div class="qcm-next-wrapper">
                        <button class="qcm-next-btn">
                            ${currentIndex < preparedQuestions.length - 1 ? 'Question suivante →' : 'Voir les résultats →'}
                        </button>
                    </div>
                ` : ''}
            </div>
        `;

        // Event listeners
        if (!answered) {
            container.querySelectorAll('.qcm-answer-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.index);
                    handleAnswer(idx);
                });
            });
        } else {
            const nextBtn = container.querySelector('.qcm-next-btn');
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentIndex++;
                    answered = false;
                    selectedIndex = null;
                    render();
                });
            }
        }
    }

    function handleAnswer(index) {
        if (answered) return;
        answered = true;
        selectedIndex = index;

        const q = preparedQuestions[currentIndex];
        const isCorrect = q.answers[index].originalIndex === q.correctOriginalIndex;

        results.push({
            question: q.question,
            correct: isCorrect,
        });

        render();
    }

    function renderResults() {
        const correct = results.filter(r => r.correct).length;
        const total = results.length;
        const percentage = Math.round((correct / total) * 100);

        let emoji = '🎉';
        let message = 'Excellent !';
        if (percentage < 50) {
            emoji = '📚';
            message = 'Continuez à réviser !';
        } else if (percentage < 80) {
            emoji = '👍';
            message = 'Bien joué !';
        }

        container.innerHTML = `
            <div class="qcm-app">
                <div class="qcm-results">
                    <div class="qcm-results-header">
                        <span>${escapeHtml(title)} - Résultats</span>
                    </div>

                    <div class="qcm-results-icon">${emoji}</div>
                    <h2 class="qcm-results-title">${message}</h2>
                    <p class="qcm-results-sub">Vous avez répondu correctement à ${correct} question${correct > 1 ? 's' : ''} sur ${total}</p>

                    <div class="qcm-score-ring">
                        <span class="qcm-score-percent">${percentage}%</span>
                    </div>

                    <div class="qcm-results-stats">
                        <div class="qcm-stat qcm-stat-green">
                            <span class="qcm-stat-icon">✅</span>
                            <span class="qcm-stat-num">${correct}</span>
                            <span class="qcm-stat-label">correcte${correct > 1 ? 's' : ''}</span>
                        </div>
                        <div class="qcm-stat qcm-stat-orange">
                            <span class="qcm-stat-icon">❌</span>
                            <span class="qcm-stat-num">${total - correct}</span>
                            <span class="qcm-stat-label">incorrecte${(total - correct) > 1 ? 's' : ''}</span>
                        </div>
                    </div>

                    <div class="qcm-next-wrapper qcm-restart-wrapper">
                        <button class="qcm-next-btn" data-action="restart">
                            Recommencer →
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.querySelector('[data-action="restart"]').addEventListener('click', () => {
            currentIndex = 0;
            answered = false;
            selectedIndex = null;
            results = [];
            // Re-shuffle answers
            if (shouldShuffle) {
                preparedQuestions.forEach(q => {
                    for (let i = q.answers.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [q.answers[i], q.answers[j]] = [q.answers[j], q.answers[i]];
                    }
                });
            }
            render();
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Start
    render();
}

// Check for containers and initialize
function checkAndInitQcm() {
    document.querySelectorAll('.jurible-qcm-container').forEach(c => {
        if (!c.dataset.initialized) {
            initQcm(c);
        }
    });
}

// Initial check
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAndInitQcm);
} else {
    checkAndInitQcm();
}

// MutationObserver for SPA navigation
const qcmObserver = new MutationObserver((mutations) => {
    let shouldCheck = false;
    for (const mutation of mutations) {
        if (mutation.addedNodes.length > 0) {
            shouldCheck = true;
            break;
        }
    }
    if (shouldCheck) {
        checkAndInitQcm();
    }
});

qcmObserver.observe(document.body, {
    childList: true,
    subtree: true
});
