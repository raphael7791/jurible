import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
    const { collectionId, collectionName, videoCount } = attributes;
    const [collections, setCollections] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const blockProps = useBlockProps();

    useEffect(() => {
        setLoading(true);
        setError(null);

        apiFetch({ path: '/jurible/v1/bunny/collections?filter=Fiches' })
            .then((response) => {
                if (response.success && response.collections) {
                    setCollections(response.collections);
                } else {
                    setError('Erreur lors du chargement des collections');
                }
                setLoading(false);
            })
            .catch((err) => {
                console.error('Error loading collections:', err);
                setError(err.message || 'Erreur de connexion');
                setLoading(false);
            });
    }, []);

    const handleCollectionChange = (e) => {
        const selectedId = e.target.value;

        if (!selectedId) {
            setAttributes({
                collectionId: '',
                collectionName: '',
                videoCount: 0
            });
            return;
        }

        const selected = collections.find(c => c.id === selectedId);
        if (selected) {
            setAttributes({
                collectionId: selected.id,
                collectionName: selected.name,
                videoCount: selected.videoCount
            });
        }
    };

    return (
        <div {...blockProps}>
            <div className="playlist-editor">
                <div className="playlist-editor-header">
                    <span className="playlist-icon">🎬</span>
                    <span className="playlist-title">Playlist Vidéo</span>
                </div>

                <div className="playlist-editor-content">
                    {loading ? (
                        <div className="playlist-editor-placeholder">
                            Chargement des collections...
                        </div>
                    ) : error ? (
                        <div className="playlist-editor-error">
                            {error}
                        </div>
                    ) : collections.length === 0 ? (
                        <div className="playlist-editor-placeholder">
                            Aucune collection "Fiches" trouvée
                        </div>
                    ) : (
                        <>
                            <div className="playlist-field">
                                <label htmlFor="collection-select">
                                    {__('Collection Bunny', 'jurible-blocks')}
                                </label>
                                <select
                                    id="collection-select"
                                    value={collectionId}
                                    onChange={handleCollectionChange}
                                >
                                    <option value="">
                                        {__('-- Sélectionner une collection --', 'jurible-blocks')}
                                    </option>
                                    {collections.map((collection) => (
                                        <option key={collection.id} value={collection.id}>
                                            {collection.name} ({collection.videoCount} vidéos)
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {collectionId && (
                                <div className="playlist-info">
                                    <strong>{videoCount}</strong> vidéos dans cette collection
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
