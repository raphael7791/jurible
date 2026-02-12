import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { courseId, lessonId, courseTitle, lessonTitle } = attributes;
	const blockProps = useBlockProps();

	const [courses, setCourses] = useState([]);
	const [lessons, setLessons] = useState([]);
	const [loading, setLoading] = useState(true);
	const [flashcardsCount, setFlashcardsCount] = useState(0);

	// Charger les cours
	useEffect(() => {
		fetch('/wp-json/fluent-community/v2/courses')
			.then(res => res.json())
			.then(data => {
				let coursesList = [];
				if (data.courses && data.courses.data) {
					coursesList = data.courses.data;
				} else if (data.data) {
					coursesList = data.data;
				}
				setCourses(coursesList);
				setLoading(false);
			})
			.catch(err => {
				console.error('Erreur chargement cours:', err);
				setLoading(false);
			});
	}, []);

	// Charger les leçons quand le cours change
	useEffect(() => {
		if (!courseId) {
			setLessons([]);
			return;
		}

		fetch(`/wp-json/fluent-community/v2/courses/${courseId}`)
			.then(res => res.json())
			.then(data => {
				const sections = data.sections || [];
				let allLessons = [];
				sections.forEach(section => {
					const sectionLessons = section.lessons || [];
					sectionLessons.forEach(lesson => {
						allLessons.push({
							...lesson,
							sectionTitle: section.title
						});
					});
				});
				setLessons(allLessons);
			})
			.catch(err => console.error('Erreur chargement leçons:', err));
	}, [courseId]);

	// Charger le nombre de flashcards
	useEffect(() => {
		if (!lessonId) {
			setFlashcardsCount(0);
			return;
		}

		fetch(`/wp-json/jurible/v1/flashcards?lesson_id=${lessonId}`)
			.then(res => res.json())
			.then(data => {
				setFlashcardsCount(data.total || 0);
			})
			.catch(err => console.error('Erreur chargement flashcards:', err));
	}, [lessonId]);

	const handleCourseChange = (e) => {
		const id = parseInt(e.target.value) || 0;
		const course = courses.find(c => c.id === id);
		setAttributes({
			courseId: id,
			courseTitle: course ? course.title : '',
			lessonId: 0,
			lessonTitle: ''
		});
	};

	const handleLessonChange = (e) => {
		const id = parseInt(e.target.value) || 0;
		const lesson = lessons.find(l => l.id === id);
		setAttributes({
			lessonId: id,
			lessonTitle: lesson ? lesson.title : ''
		});
	};

	if (loading) {
		return (
			<div {...blockProps}>
				<div className="flashcards-editor-placeholder">
					Chargement...
				</div>
			</div>
		);
	}

	return (
		<div {...blockProps}>
			<div className="flashcards-editor">
				<div className="flashcards-editor-header">
					<span className="flashcards-icon">🎴</span>
					<span className="flashcards-title">Flashcards Jurible</span>
				</div>

				<div className="flashcards-editor-content">
					<div className="flashcards-field">
						<label>Cours</label>
						<select value={courseId || ''} onChange={handleCourseChange}>
							<option value="">-- Sélectionner un cours --</option>
							{courses.map(course => (
								<option key={course.id} value={course.id}>
									{course.title}
								</option>
							))}
						</select>
					</div>

					{courseId > 0 && (
						<div className="flashcards-field">
							<label>Leçon</label>
							<select value={lessonId || ''} onChange={handleLessonChange}>
								<option value="">-- Sélectionner une leçon --</option>
								{lessons.map(lesson => (
									<option key={lesson.id} value={lesson.id}>
										{lesson.sectionTitle} → {lesson.title}
									</option>
								))}
							</select>
						</div>
					)}

					{lessonId > 0 && (
						<div className="flashcards-info">
							<strong>{flashcardsCount}</strong> flashcard{flashcardsCount > 1 ? 's' : ''} disponible{flashcardsCount > 1 ? 's' : ''}
						</div>
					)}
				</div>
			</div>
		</div>
	);
}