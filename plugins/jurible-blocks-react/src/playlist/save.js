import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { collectionId, collectionName, videoCount } = attributes;

    const blockProps = useBlockProps.save();

    if (!collectionId) {
        return null;
    }

    return (
        <div
            {...blockProps}
            className="jurible-playlist-container"
            data-collection-id={collectionId}
            data-collection-name={collectionName}
            data-video-count={videoCount}
        >
            <div className="playlist-loading">
                Chargement de la playlist...
            </div>
        </div>
    );
}
