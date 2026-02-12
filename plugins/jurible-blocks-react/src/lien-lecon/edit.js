import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import { SelectControl, Spinner, Placeholder } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
    const { courseId, courseName, lessonId, lessonTitle, lessonUrl } = attributes;
    const blockProps = useBlockProps({ className: 'jurible-lien-lecon' });
    
    const [courses, setCourses] = useState([]);
    const [lessons, setLessons] = useState([]);
    const [loadingCourses, setLoadingCourses] = useState(true);
    const [loadingLessons, setLoadingLessons] = useState(false);

    useEffect(() => {
        setLoadingCourses(true);
        apiFetch({ path: '/fluent-community/v2/courses?per_page=100' })
            .then(data => {
                if (data.courses && data.courses.data) {
                    setCourses(data.courses.data);
                }
                setLoadingCourses(false);
            })
            .catch(err => {
                console.error('Erreur chargement cours:', err);
                setLoadingCourses(false);
            });
    }, []);

    useEffect(() => {
        if (courseId > 0) {
            setLoadingLessons(true);
            apiFetch({ path: `/fluent-community/v2/admin/courses/${courseId}/lessons` })
                .then(data => {
                    if (data.data) {
                        setLessons(data.data);
                    } else if (Array.isArray(data)) {
                        setLessons(data);
                    } else if (data.lessons) {
                        setLessons(data.lessons);
                    }
                    setLoadingLessons(false);
                })
                .catch(err => {
                    console.error('Erreur chargement leçons:', err);
                    setLoadingLessons(false);
                });
        } else {
            setLessons([]);
        }
    }, [courseId]);

    const onSelectCourse = (value) => {
        const id = parseInt(value);
        const course = courses.find(c => c.id === id);
        setAttributes({
            courseId: id,
            courseName: course ? course.title : '',
            lessonId: 0,
            lessonTitle: '',
            lessonUrl: ''
        });
    };

    const onSelectLesson = (value) => {
        const id = parseInt(value);
        const lesson = lessons.find(l => l.id === id);
        if (lesson) {
            const course = courses.find(c => c.id === courseId);
            const courseSlug = course ? course.slug : '';
            const lessonSlug = lesson.slug || '';
            const portalSlug = window.fluentCommunityPortalSlug || 'accueil';
            const url = `/${portalSlug}/course/${courseSlug}/lessons/${lessonSlug}/view`;
            
            setAttributes({
                lessonId: id,
                lessonTitle: lesson.title,
                lessonUrl: url
            });
        }
    };

    const courseOptions = [
        { label: '-- Sélectionner un cours --', value: 0 },
        ...courses.map(c => ({ label: c.title, value: c.id }))
    ];

    const lessonOptions = [
        { label: '-- Sélectionner une leçon --', value: 0 },
        ...lessons.map(l => ({ label: l.title, value: l.id }))
    ];

    if (!lessonId) {
        return (
            <div {...blockProps}>
                <Placeholder
                    icon="welcome-learn-more"
                    label="Lien vers une leçon"
                    instructions="Sélectionnez un cours puis une leçon."
                >
                    {loadingCourses ? (
                        <Spinner />
                    ) : (
                        <>
                            <SelectControl
                                label="Cours"
                                value={courseId}
                                options={courseOptions}
                                onChange={onSelectCourse}
                            />
                            {courseId > 0 && (
                                loadingLessons ? (
                                    <Spinner />
                                ) : (
                                    <SelectControl
                                        label="Leçon"
                                        value={lessonId}
                                        options={lessonOptions}
                                        onChange={onSelectLesson}
                                    />
                                )
                            )}
                        </>
                    )}
                </Placeholder>
            </div>
        );
    }

    return (
        <div {...blockProps}>
            <div className="jurible-lien-lecon-card">
                <div className="jurible-lien-lecon-header">
                    <span className="jurible-lien-lecon-icon">🔗</span>
                    <span className="jurible-lien-lecon-label">EN SAVOIR PLUS</span>
                </div>
                <p className="jurible-lien-lecon-description">
                    Consultez également : « {lessonTitle} »
                </p>
                <button 
                    className="jurible-lien-lecon-btn"
                    onClick={() => setAttributes({ lessonId: 0, lessonTitle: '', lessonUrl: '' })}
                >
                    Changer de leçon
                </button>
            </div>
        </div>
    );
}