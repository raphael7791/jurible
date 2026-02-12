import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

const homeIcon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`;

export default function Edit({ attributes, setAttributes }) {
	const { homeIconOnMobile, showCurrentPage, showSchema } = attributes;
	const blockProps = useBlockProps({ className: 'jurible-breadcrumb' });

	return (
		<>
			<InspectorControls>
				<PanelBody title="Options">
					<ToggleControl
						label="Icône Home sur mobile"
						help="Remplace 'Accueil' par une icône maison sur mobile"
						checked={homeIconOnMobile}
						onChange={(value) => setAttributes({ homeIconOnMobile: value })}
					/>
					<ToggleControl
						label="Afficher la page actuelle"
						checked={showCurrentPage}
						onChange={(value) => setAttributes({ showCurrentPage: value })}
					/>
					<ToggleControl
						label="Schema SEO (BreadcrumbList)"
						help="Ajoute le balisage structuré pour les moteurs de recherche"
						checked={showSchema}
						onChange={(value) => setAttributes({ showSchema: value })}
					/>
				</PanelBody>
			</InspectorControls>
			<nav {...blockProps}>
				<a href="#" className={`jurible-breadcrumb-item jurible-breadcrumb-home ${homeIconOnMobile ? 'jurible-breadcrumb-home--icon-mobile' : ''}`}>
					<span className="jurible-breadcrumb-home-icon" dangerouslySetInnerHTML={{ __html: homeIcon }} />
					<span className="jurible-breadcrumb-home-text">Accueil</span>
				</a>
				<span className="jurible-breadcrumb-separator">›</span>
				<a href="#" className="jurible-breadcrumb-item">Catégorie</a>
				<span className="jurible-breadcrumb-separator">›</span>
				<a href="#" className="jurible-breadcrumb-item">Sous-catégorie</a>
				{showCurrentPage && (
					<>
						<span className="jurible-breadcrumb-separator">›</span>
						<span className="jurible-breadcrumb-item jurible-breadcrumb-item--active">Page actuelle</span>
					</>
				)}
			</nav>
		</>
	);
}
