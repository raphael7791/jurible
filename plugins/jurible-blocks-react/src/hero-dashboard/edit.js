import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl, ToggleControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const {
		userName,
		userInitials,
		currentCourse,
		lessonTitle,
		lessonMeta,
		progressPercent,
		qcmCount,
		fichesCount,
		fichesTotal,
		gradeValue,
		gradeImprovement,
		enableParallax,
		enableAnimations,
	} = attributes;

	const blockProps = useBlockProps({ className: 'hero-dashboard-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Utilisateur', 'jurible-blocks-react')} initialOpen={true}>
					<TextControl
						label={__('Prénom', 'jurible-blocks-react')}
						value={userName}
						onChange={(value) => setAttributes({ userName: value })}
					/>
					<TextControl
						label={__('Initiales', 'jurible-blocks-react')}
						value={userInitials}
						onChange={(value) => setAttributes({ userInitials: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Cours actuel', 'jurible-blocks-react')} initialOpen={true}>
					<TextControl
						label={__('Matière', 'jurible-blocks-react')}
						value={currentCourse}
						onChange={(value) => setAttributes({ currentCourse: value })}
					/>
					<TextControl
						label={__('Titre de la leçon', 'jurible-blocks-react')}
						value={lessonTitle}
						onChange={(value) => setAttributes({ lessonTitle: value })}
					/>
					<TextControl
						label={__('Méta (durée, auteur)', 'jurible-blocks-react')}
						value={lessonMeta}
						onChange={(value) => setAttributes({ lessonMeta: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Statistiques', 'jurible-blocks-react')} initialOpen={false}>
					<RangeControl
						label={__('Progression (%)', 'jurible-blocks-react')}
						value={progressPercent}
						onChange={(value) => setAttributes({ progressPercent: value })}
						min={0}
						max={100}
					/>
					<RangeControl
						label={__('QCM réussis', 'jurible-blocks-react')}
						value={qcmCount}
						onChange={(value) => setAttributes({ qcmCount: value })}
						min={0}
						max={500}
					/>
					<RangeControl
						label={__('Fiches lues', 'jurible-blocks-react')}
						value={fichesCount}
						onChange={(value) => setAttributes({ fichesCount: value })}
						min={0}
						max={100}
					/>
					<RangeControl
						label={__('Total fiches', 'jurible-blocks-react')}
						value={fichesTotal}
						onChange={(value) => setAttributes({ fichesTotal: value })}
						min={0}
						max={100}
					/>
					<RangeControl
						label={__('Note partiel', 'jurible-blocks-react')}
						value={gradeValue}
						onChange={(value) => setAttributes({ gradeValue: value })}
						min={0}
						max={20}
					/>
					<TextControl
						label={__('Amélioration', 'jurible-blocks-react')}
						value={gradeImprovement}
						onChange={(value) => setAttributes({ gradeImprovement: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Animations', 'jurible-blocks-react')} initialOpen={false}>
					<ToggleControl
						label={__('Effet parallax', 'jurible-blocks-react')}
						checked={enableParallax}
						onChange={(value) => setAttributes({ enableParallax: value })}
					/>
					<ToggleControl
						label={__('Animations au chargement', 'jurible-blocks-react')}
						checked={enableAnimations}
						onChange={(value) => setAttributes({ enableAnimations: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="hero-dashboard-preview">
					<div className="hero-dashboard-preview__label">
						Hero Dashboard — Aperçu simplifié
					</div>
					<div className="hero-dashboard-preview__info">
						<strong>Utilisateur:</strong> {userName} ({userInitials})<br />
						<strong>Cours:</strong> {currentCourse}<br />
						<strong>Leçon:</strong> {lessonTitle}<br />
						<strong>Progression:</strong> {progressPercent}% | <strong>QCM:</strong> {qcmCount} | <strong>Note:</strong> {gradeValue}/20
					</div>
				</div>
			</div>
		</>
	);
}
