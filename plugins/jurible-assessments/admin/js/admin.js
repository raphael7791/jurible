/**
 * Jurible Assessments - Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        
        // ==================== VARIABLES ====================
        
        var fluentApi = '/wp-json/fluent-community/v2';
        var juribleApi = '/wp-json/jurible/v1';
        var coursesCache = {};
        var lessonsCache = {};
        
        // ==================== COURSE/LESSON SELECTORS ====================
        
        // Charger les cours au démarrage
        if ($('#assess-course-select').length) {
            loadCourses();
        }
        
        function loadCourses() {
            var $select = $('#assess-course-select');
            $select.prop('disabled', true).html('<option value="">Chargement...</option>');
            
            $.ajax({
                url: fluentApi + '/courses',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', juribleAssess.nonce);
                }
            })
            .done(function(response) {
                var courses = [];
                if (response.courses && response.courses.data) {
                    courses = response.courses.data;
                } else if (response.data) {
                    courses = response.data;
                }
                
                coursesCache = {};
                var html = '<option value="">-- Sélectionner un cours --</option>';
                courses.forEach(function(course) {
                    coursesCache[course.id] = course;
                    html += '<option value="' + course.id + '">' + escapeHtml(course.title) + '</option>';
                });
                
                $select.html(html).prop('disabled', false);
                
                // Si une valeur était pré-sélectionnée
                var preselected = $select.data('selected');
                if (preselected) {
                    $select.val(preselected).trigger('change');
                }
            })
            .fail(function(xhr) {
                console.error('Erreur chargement cours:', xhr);
                $select.html('<option value="">Erreur de chargement</option>');
            });
        }
        
        // Quand on change de cours, charger les leçons
        $('#assess-course-select').on('change', function() {
            var courseId = $(this).val();
            var $lessonSelect = $('#assess-lesson-select');
            
            if (!courseId) {
                $lessonSelect.html('<option value="">-- Sélectionner d\'abord un cours --</option>').prop('disabled', true);
                return;
            }
            
            $lessonSelect.prop('disabled', true).html('<option value="">Chargement...</option>');
            
            $.ajax({
                url: fluentApi + '/courses/' + courseId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', juribleAssess.nonce);
                }
            })
            .done(function(response) {
                var sections = response.sections || [];
                lessonsCache = {};
                
                var html = '<option value="">-- Sélectionner une leçon --</option>';
                sections.forEach(function(section) {
                    var lessons = section.lessons || [];
                    if (lessons.length > 0) {
                        html += '<optgroup label="' + escapeHtml(section.title) + '">';
                        lessons.forEach(function(lesson) {
                            lessonsCache[lesson.id] = lesson;
                            html += '<option value="' + lesson.id + '">' + escapeHtml(lesson.title) + '</option>';
                        });
                        html += '</optgroup>';
                    }
                });
                
                $lessonSelect.html(html).prop('disabled', false);
                
                // Si une valeur était pré-sélectionnée
                var preselected = $lessonSelect.data('selected');
                if (preselected) {
                    $lessonSelect.val(preselected);
                }
            })
            .fail(function(xhr) {
                console.error('Erreur chargement leçons:', xhr);
                $lessonSelect.html('<option value="">Erreur de chargement</option>');
            });
        });
        
        // ==================== FILTERS ====================
        
        // Filtres sur la page inbox
        $('.assess-filter-select').on('change', function() {
            var status = $('#filter-status').val();
            var course = $('#filter-course').val();
            
            var url = new URL(window.location.href);
            
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            
            if (course) {
                url.searchParams.set('course_id', course);
            } else {
                url.searchParams.delete('course_id');
            }
            
            window.location.href = url.toString();
        });
        
        // ==================== CLAIM SUBMISSION ====================
        
        $('.assess-btn-claim').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var submissionId = $btn.data('id');
            
            $btn.prop('disabled', true).text('...');
            
            $.ajax({
                url: juribleApi + '/submissions/' + submissionId + '/claim',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', juribleAssess.nonce);
                }
            })
            .done(function(response) {
                if (response.success) {
                    // Rediriger vers la page de correction
                    window.location.href = juribleAssess.adminUrl + 'admin.php?page=jurible-assessments-correction&id=' + submissionId;
                } else {
                    alert('Erreur: ' + (response.message || 'Impossible de prendre en charge'));
                    $btn.prop('disabled', false).text('Prendre en charge');
                }
            })
            .fail(function(xhr) {
                alert('Erreur de connexion');
                $btn.prop('disabled', false).text('Prendre en charge');
            });
        });
        
        // ==================== GRADE FORM ====================
        
        $('#assess-grade-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var submissionId = $form.data('submission-id');
            
            // Validation
            var score = parseFloat($('#assess-score').val());
            var maxScore = parseFloat($('#assess-max-score').val());
            var feedback = $('#assess-feedback').val().trim();
            
            if (isNaN(score) || score < 0 || score > maxScore) {
                alert('Veuillez entrer une note valide entre 0 et ' + maxScore);
                return;
            }
            
            if (feedback.length < 10) {
                alert('Le feedback doit contenir au moins 10 caractères');
                return;
            }
            
            $btn.prop('disabled', true).text('Enregistrement...');
            
            $.ajax({
                url: juribleApi + '/submissions/' + submissionId + '/grade',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    score: score,
                    feedback: feedback,
                    video_url: $('#assess-video-url').val().trim()
                }),
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', juribleAssess.nonce);
                }
            })
            .done(function(response) {
                if (response.success) {
                    alert('Correction enregistrée ! L\'étudiant a été notifié.');
                    window.location.href = juribleAssess.adminUrl + 'admin.php?page=jurible-assessments';
                } else {
                    alert('Erreur: ' + (response.message || 'Impossible d\'enregistrer'));
                    $btn.prop('disabled', false).text('Enregistrer et notifier');
                }
            })
            .fail(function(xhr) {
                alert('Erreur de connexion');
                $btn.prop('disabled', false).text('Enregistrer et notifier');
            });
        });
        
        // ==================== DELETE ASSESSMENT ====================
        
        $('.assess-btn-delete').on('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet assessment ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
        
        // ==================== SETTINGS ====================
        
        // Toggle switches
        $('.assess-toggle input').on('change', function() {
            var $toggle = $(this);
            var setting = $toggle.attr('name');
            var value = $toggle.is(':checked') ? 1 : 0;
            
            // Auto-save setting
            $.ajax({
                url: juribleApi + '/assessments/settings',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    setting: setting,
                    value: value
                }),
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', juribleAssess.nonce);
                }
            })
            .done(function(response) {
                // Feedback visuel subtil
                $toggle.closest('.assess-toggle-row').addClass('saved');
                setTimeout(function() {
                    $toggle.closest('.assess-toggle-row').removeClass('saved');
                }, 1000);
            })
            .fail(function(xhr) {
                console.error('Erreur sauvegarde setting:', xhr);
                // Revert
                $toggle.prop('checked', !$toggle.is(':checked'));
            });
        });
        
        // ==================== HELPERS ====================
        
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // ==================== FILE UPLOAD PREVIEW ====================
        
        // Pour le formulaire de création d'assessment (upload PDF)
        $('.assess-file-input').on('change', function() {
            var $input = $(this);
            var $preview = $input.siblings('.assess-file-preview');
            var file = this.files[0];
            
            if (file) {
                $preview.html('<span class="assess-file-name">📄 ' + escapeHtml(file.name) + '</span>');
            } else {
                $preview.html('');
            }
        });
        
        // ==================== AUTO-RESIZE TEXTAREA ====================
        
        $('textarea.auto-resize').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
    });
    
})(jQuery);