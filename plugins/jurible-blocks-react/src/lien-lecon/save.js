import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { lessonTitle, lessonUrl } = attributes;
    const blockProps = useBlockProps.save({ className: 'jurible-lien-lecon' });

    if (!lessonUrl) {
        return null;
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
                <a 
                    href={lessonUrl} 
                    className="jurible-lien-lecon-btn"
                    target="_blank" 
                    rel="noopener noreferrer"
                >
                    Voir le cours →
                </a>
            </div>
        </div>
    );
}