import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { assessmentId, assessmentTitle, courseId, lessonId } = attributes;
    const blockProps = useBlockProps.save();

    if (!assessmentId) {
        return null;
    }

    return (
        <div {...blockProps}>
            <div 
                className="jurible-assessment-container" 
                data-assessment-id={assessmentId}
                data-assessment-title={assessmentTitle}
                data-course-id={courseId}
                data-lesson-id={lessonId}
            >
                <div className="assessment-loading">
                    Chargement...
                </div>
            </div>
        </div>
    );
}