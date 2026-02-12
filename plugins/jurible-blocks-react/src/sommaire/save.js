import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { headings } = attributes;
    const blockProps = useBlockProps.save({ className: 'jurible-sommaire' });

    if (headings.length === 0) {
        return null;
    }

    return (
        <div {...blockProps}>
            <div className="jurible-sommaire-header">
                <span className="jurible-sommaire-icon">📑</span>
                <span className="jurible-sommaire-title">Sommaire</span>
            </div>
            <ul className="jurible-sommaire-list">
                {headings.map((heading, index) => (
                    <li key={index}>
                        <a href={`#${heading.slug}`}>{heading.content}</a>
                    </li>
                ))}
            </ul>
        </div>
    );
}