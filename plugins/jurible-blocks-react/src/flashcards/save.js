import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { courseId, lessonId, courseTitle, lessonTitle } = attributes;
	const blockProps = useBlockProps.save();

	if (!lessonId) {
		return null;
	}

	return (
		<div {...blockProps}>
			<div 
				className="jurible-flashcards-container" 
				data-course-id={courseId}
				data-lesson-id={lessonId}
				data-course-title={courseTitle}
				data-lesson-title={lessonTitle}
			>
				<div className="flashcards-loading">
					Chargement des flashcards...
				</div>
			</div>
		</div>
	);
}