// Flashcards View - Front-end interactivity
function initFlashcards(container) {
    if (container.dataset.initialized === 'true') return;
    container.dataset.initialized = 'true';
    
    const lessonId = container.dataset.lessonId;
    if (!lessonId) {
        container.innerHTML = '<div class="fc-error"><p>Aucune leçon sélectionnée</p></div>';
        return;
    }

    // Fetch flashcards
    (async () => {
        try {
            const res = await fetch(`/wp-json/jurible/v1/flashcards?lesson_id=${lessonId}`);
            const data = await res.json();
            
            if (!data.success || !data.data || data.data.length === 0) {
                container.innerHTML = '<div class="fc-empty"><p>Aucune flashcard disponible pour cette leçon</p></div>';
                return;
            }

            const flashcards = data.data;
            let currentIndex = 0;
            let showingAnswer = false;
            let streak = 0;
            let maxStreak = 0;
            let results = [];

        function render() {
            if (currentIndex >= flashcards.length) {
                renderResults();
                return;
            }

            const card = flashcards[currentIndex];
            const progress = ((currentIndex + 1) / flashcards.length) * 100;
            
            container.innerHTML = `
                <div class="fc-app">
                    <!-- Progress Bar -->
                    <div class="fc-progress-section">
                        <div class="fc-progress-row">
                            <span class="fc-progress-label">Progression</span>
                            <span class="fc-progress-count">${currentIndex + 1} / ${flashcards.length} cartes</span>
                        </div>
                        <div class="fc-progress-bar">
                            <div class="fc-progress-fill" style="width: ${progress}%"></div>
                        </div>
                    </div>

                    <!-- Card -->
                    ${showingAnswer ? renderAnswerCard(card) : renderQuestionCard(card)}

                    <!-- Buttons (only when showing answer) -->
                    ${showingAnswer ? `
                        <div class="fc-buttons">
                            <button class="fc-btn fc-btn-no" data-action="wrong">Je ne savais pas</button>
                            <button class="fc-btn fc-btn-yes" data-action="correct">Je savais</button>
                        </div>
                    ` : ''}
                </div>
            `;

            // Event listeners
            if (!showingAnswer) {
                container.querySelector('.fc-card').addEventListener('click', () => {
                    showingAnswer = true;
                    render();
                });
            } else {
                container.querySelector('[data-action="wrong"]').addEventListener('click', () => handleAnswer(false));
                container.querySelector('[data-action="correct"]').addEventListener('click', () => handleAnswer(true));
            }
        }

        function renderQuestionCard(card) {
            return `
                <div class="fc-card fc-card-question">
                    <div class="fc-card-header">
                        <span class="fc-card-label">Question</span>
                        <span class="fc-card-num">${currentIndex + 1}/${flashcards.length}</span>
                    </div>
                    <div class="fc-card-body">
                        <p>${card.question}</p>
                    </div>
                    <div class="fc-card-hint">
                        <span>👆 Cliquez pour voir la réponse</span>
                    </div>
                </div>
            `;
        }

        function renderAnswerCard(card) {
            return `
                <div class="fc-card fc-card-answer">
                    <div class="fc-card-header">
                        <span class="fc-card-label">Réponse</span>
                        <span class="fc-card-num">${currentIndex + 1}/${flashcards.length}</span>
                    </div>
                    <div class="fc-card-body">
                        <p>${card.reponse}</p>
                    </div>
                </div>
            `;
        }

        function handleAnswer(correct) {
            results.push({
                card: flashcards[currentIndex],
                correct: correct,
                index: currentIndex
            });

            if (correct) {
                streak++;
                if (streak > maxStreak) maxStreak = streak;
            } else {
                streak = 0;
            }

            currentIndex++;
            showingAnswer = false;
            render();
        }

        function renderResults() {
            const mastered = results.filter(r => r.correct);
            const toReview = results.filter(r => !r.correct);

            container.innerHTML = `
                <div class="fc-app">
                    <div class="fc-results">
                        <div class="fc-results-header">
                            <span>🎉 Récapitulatif fin de session</span>
                        </div>
                        
                        <div class="fc-results-icon">🎊</div>
                        <h2 class="fc-results-title">Session terminée !</h2>
                        <p class="fc-results-sub">Vous avez révisé ${results.length} cartes</p>

                        <div class="fc-results-stats">
                            <div class="fc-stat fc-stat-green">
                                <span class="fc-stat-icon">✅</span>
                                <span class="fc-stat-num">${mastered.length}</span>
                                <span class="fc-stat-label">cartes maîtrisées</span>
                            </div>
                            <div class="fc-stat fc-stat-orange">
                                <span class="fc-stat-icon">🔄</span>
                                <span class="fc-stat-num">${toReview.length}</span>
                                <span class="fc-stat-label">cartes à revoir</span>
                            </div>
                        </div>

                        <div class="fc-results-streak">
                            <span>🔥</span>
                            <span class="fc-stat-num">${maxStreak}</span>
                            <span class="fc-stat-label">meilleure série</span>
                        </div>

                        <div class="fc-results-actions">
                            <button class="fc-btn fc-btn-primary" data-action="restart">
                                Recommencer
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Event listeners
            const restartBtn = container.querySelector('[data-action="restart"]');

            restartBtn.addEventListener('click', () => {
                location.reload();
            });
        }

        function truncate(str, len) {
            if (str.length <= len) return str;
            return str.substring(0, len) + '...';
        }

        // Start
        render();

        } catch (error) {
            console.error('Flashcards error:', error);
            container.innerHTML = '<div class="fc-error"><p>Erreur lors du chargement des flashcards</p></div>';
        }
    })();
}

// Check for containers and initialize
function checkAndInit() {
    document.querySelectorAll('.jurible-flashcards-container').forEach(c => {
        if (!c.dataset.initialized) {
            initFlashcards(c);
        }
    });
}

// Initial check
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAndInit);
} else {
    checkAndInit();
}

// MutationObserver to detect new containers (SPA navigation)
const observer = new MutationObserver((mutations) => {
    let shouldCheck = false;
    for (const mutation of mutations) {
        if (mutation.addedNodes.length > 0) {
            shouldCheck = true;
            break;
        }
    }
    if (shouldCheck) {
        checkAndInit();
    }
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});