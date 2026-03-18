import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
    const blockProps = useBlockProps({ className: 'jurible-sommaire jurible-sommaire-placeholder' });

    return (
        <div {...blockProps}>
            <div className="jurible-sommaire-preview">
                <span className="jurible-sommaire-icon">📑</span>
                <span className="jurible-sommaire-title">Sommaire</span>
            </div>
            <p className="jurible-sommaire-info">
                Le sommaire sera généré automatiquement à partir des titres H2 de l'article.
            </p>
        </div>
    );
}
