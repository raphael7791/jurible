import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const { assessmentId, assessmentTitle } = attributes;
    const blockProps = useBlockProps();

    const [assessments, setAssessments] = useState([]);
    const [loading, setLoading] = useState(true);

    // Charger les assessments disponibles
    useEffect(() => {
        fetch('/wp-json/jurible/v1/assessments')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    setAssessments(data.data);
                }
                setLoading(false);
            })
            .catch(err => {
                console.error('Erreur chargement assessments:', err);
                setLoading(false);
            });
    }, []);

    const handleAssessmentChange = (e) => {
        const id = parseInt(e.target.value) || 0;
        const assess = assessments.find(a => a.id === id);
        setAttributes({
            assessmentId: id,
            assessmentTitle: assess ? assess.title : '',
            courseId: assess ? assess.course_id : 0,
            lessonId: assess ? assess.lesson_id : 0
        });
    };

    if (loading) {
        return (
            <div {...blockProps}>
                <div className="assessment-editor-placeholder">
                    Chargement des assessments...
                </div>
            </div>
        );
    }

    return (
        <div {...blockProps}>
            <div className="assessment-editor">
                <div className="assessment-editor-header">
                    <span className="assessment-icon">📝</span>
                    <span className="assessment-title">Assessment Jurible</span>
                </div>

                <div className="assessment-editor-content">
                    <div className="assessment-field">
                        <label>Sélectionner un assessment</label>
                        <select value={assessmentId || ''} onChange={handleAssessmentChange}>
                            <option value="">-- Choisir un assessment --</option>
                            {assessments.map(assess => (
                                <option key={assess.id} value={assess.id}>
                                    {assess.title}
                                </option>
                            ))}
                        </select>
                    </div>

                    {assessmentId > 0 && (
                        <div className="assessment-info">
                            <strong>✅ {assessmentTitle}</strong>
                            <p>Ce bloc affichera le formulaire de soumission et la note de l'étudiant.</p>
                        </div>
                    )}

                    {assessments.length === 0 && (
                        <div className="assessment-warning">
                            ⚠️ Aucun assessment trouvé. Créez-en un dans le menu Assessments.
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}