/**
 *
 * @param {object} config
 * @param {AdminApi} adminApi
 * @param {jQuery} $
 */
import {contentTypeViewProvider} from './utils/content-type-view-provider';
import {particleStreamPreview} from './view/particle-stream-preview';
import {particleEditor} from './view/particle-editor';
import events from './utils/events';

export const application = (config, adminApi, $)=>{

    let isSavingParticle =  false;
	let fetchingParticles = false;

	/**
	 * @type {object}
	 */
	const dom = {};

	/**
	 *
	 * @type {{add, get}}
	 */
	const contentTypeProvider = contentTypeViewProvider();

	// ------------------------------------------------------
	// get root dom element of editor
	// ------------------------------------------------------
	dom.$root = $(`#${config.dom.rootId}`);
	dom.$root.empty();

	// ------------------------------------------------------
	// editor
	// ------------------------------------------------------
	const editor = particleEditor(
		$,
		config.tags,
		contentTypeProvider,
		()=>{
			if(isSavingParticle) return;
			isSavingParticle = true;
			editor.setIsSaving(true);

			const values = editor.getContents();
			const data = {
				particle_contents: values.map(({content}) => content),
				particle_content_types: values.map(({type}) => type),
				tags: editor.getTags(),
			};
			if(editor.getId()) data.particle_id = editor.getId();
			if(editor.getAuthorId()) data.author_id = editor.getAuthorId();


			adminApi.updateParticle(data).then(()=>{
				if(data.particle_id>0){
					preview.setEditing(data.particle_id, false);
					preview.setLoading(data.particle_id, true);
				} else {
					setPreviewLoading(true);
				}
				editor.setIsSaving(false);
				editor.reset();
				isSavingParticle = false;
				updatePreviewStream();
			});
		},
		()=>{
			// if editor contents are deleted
		},
		()=>{
			// if editing a particle is canceled
			preview.setEditing(editor.getId(), false);
			editor.reset();
		}
	);
	dom.$root.append(editor.get$());

	// ------------------------------------------------------
	// settings for preview stream
	// ------------------------------------------------------
	const settings = `<div class="live-news-stream__settings">
            <ul>
                <li>
                    <a href="#" class="live-news-stream__settings--is-active">Neueste zuerst</a>
                </li>
                <li>
                    <a href="#">Älteste zuerst</a>
                </li>
                <li>
                    <a href="#">Nur Wichtigste</a>
                </li>
            </ul>
        </div>`;

	// ------------------------------------------------------
	// preview of particle stream
	// ------------------------------------------------------
	dom.$preview = $("<div></div>").addClass("live-news__preview").appendTo(dom.$root);
	dom.$previewHeader = $("<div></div>").addClass("live-news__preview--header").appendTo(dom.$preview);

	dom.$previewLoadingIndicator = $("<span class='spinner'></span>")
		.appendTo(dom.$previewHeader);

	const preview = particleStreamPreview(
		$,
		contentTypeProvider,
		(particle)=>{
			// so all others can be unset for editing
			preview.setEditing(particle.id, true);
			editor.setParticle(particle);
		},
		(particle)=>{
			editor.reset();
		},
		(particle)=>{
			adminApi.deleteParticle(particle.id).then((json)=>{
				if(json.deleted){
					preview.removeParticleById(particle.id);
				}
			});
		}
	);
	dom.$preview.append(preview.get$());
	const setPreviewLoading = (isLoading) => {
		if(isLoading){
			dom.$previewLoadingIndicator.addClass("is-active");
		} else{
			dom.$previewLoadingIndicator.removeClass("is-active");
		}
	};
	let updateTimeout = null;
	const updatePreviewStream = ()=>{
		if(fetchingParticles) return;

		clearTimeout(updateTimeout);
		fetchingParticles = true;

		adminApi.fetchParticlesUpdate().then((particles)=>{

			updateTimeout = setTimeout(updatePreviewStream, 5000);
			fetchingParticles = false;
			setPreviewLoading(false);
			preview.update(particles);
		});
	};
	setPreviewLoading(true);
	updatePreviewStream();

	// ------------------------------------------------------
	// functions
	// ------------------------------------------------------

	// ------------------------------------------------------
	// public
	// ------------------------------------------------------
	return {
		dom,
		contentTypeProvider,
		editor,
		events,
		init: () => {
			// initially add this combination
			editor.setContents(["title", "html", "upload"]);
		}
	}
};