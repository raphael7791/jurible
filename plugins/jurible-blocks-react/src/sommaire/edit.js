import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const { headings } = attributes;
    const blockProps = useBlockProps({ className: 'jurible-sommaire' });

    // Récupérer tous les blocs de l'éditeur
    const blocks = useSelect((select) => {
        return select(blockEditorStore).getBlocks();
    }, []);

    // Extraire les titres H2 des blocs
    useEffect(() => {
        if (!blocks || blocks.length === 0) return;

        const newHeadings = [];
        
        const extractHeadings = (blockList) => {
            blockList.forEach((block) => {
                if (block.name === 'core/heading' && block.attributes?.level === 2) {
                    const content = typeof block.attributes.content === 'string' 
                        ? block.attributes.content 
                        : block.attributes.content?.text || '';
                    
                    if (content.length > 0) {
                        // Nettoyer le HTML du contenu
                        const cleanContent = content.replace(/<[^>]*>/g, '');
                        const slug = cleanContent.toLowerCase()
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-|-$/g, '');
                        
                        newHeadings.push({
                            content: cleanContent,
                            slug: slug,
                        });
                    }
                }
                // Récursion pour les blocs imbriqués
                if (block.innerBlocks && block.innerBlocks.length > 0) {
                    extractHeadings(block.innerBlocks);
                }
            });
        };

        extractHeadings(blocks);

        // Mettre à jour seulement si différent
        if (JSON.stringify(newHeadings) !== JSON.stringify(headings)) {
            setAttributes({ headings: newHeadings });
        }
    }, [blocks]);

    return (
        <div {...blockProps}>
            <div className="jurible-sommaire-header">
                <span className="jurible-sommaire-icon">📑</span>
                <span className="jurible-sommaire-title">Sommaire</span>
            </div>
            {headings.length > 0 ? (
                <ul className="jurible-sommaire-list">
                    {headings.map((heading, index) => (
                        <li key={index}>
                            <a href={`#${heading.slug}`}>{heading.content}</a>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="jurible-sommaire-info">
                    Le sommaire sera généré automatiquement à partir des titres H2.
                </p>
            )}
        </div>
    );
}