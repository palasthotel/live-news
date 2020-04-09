import {particlePreview} from './particle';

export const particleStreamPreview = ($, contentTypeProvider, onEdit, onCancelEdit, onDelete)=>{

	let streamParticlesViews = [];

	const $el = $("<div></div>").addClass("live-news__stream");

	// check if is in preview stream
	// return position of view in array or -1
	const isInPreviewStream = (particle_id) => streamParticlesViews.findIndex(({particle})=> particle.id === particle_id);

	// get particle view from stream
	const getPreviewStreamParticleView = (particle_id)=>{
		return streamParticlesViews.find(({particle}) => particle.id === particle_id);
	};

	// particle view for preview stream
	const buildPreviewStreamParticleView = (particle) => {
		particle.contentViews = particle.contents.map(({type, content}) => {
			const contentView = contentTypeProvider.get(type);
			if(!contentView) return null;
			contentView.setValue(content);
			return contentView;
		}).filter(v => v !== null);
		return particlePreview(particle, onEdit, onCancelEdit, onDelete);
	};

	const removeParticleById = (particleId)=>{
		const view = getPreviewStreamParticleView(particleId);
		if(view){
			streamParticlesViews = streamParticlesViews.filter(view=> view.particle.id !== particleId);
			view.get$().slideUp(330, ()=>{
				view.get$().remove();
			});
		}
	};
	const resetEditing = ()=>{
		for(let v of streamParticlesViews){
			v.setEditing(false);
		}
	}

	return {
		get$: ()=> $el,
		setEditing: (particle_id, isEditing)=>{
			const p = getPreviewStreamParticleView(particle_id);
			resetEditing();
			if(p) p.setEditing(isEditing);
		},
		setLoading: (particle_id, isLoading)=>{
			const p = getPreviewStreamParticleView(particle_id);
			if(p) p.setLoading(isLoading);
		},
		removeParticleById,
		update: (particles) => {
			for(let p of particles.reverse()){
				const inStream = isInPreviewStream(p.id);

				if(p.is_deleted === true){
					if(inStream){
						// DELETE
						removeParticleById(p.id);
					}
					continue;
				}

				if(inStream >= 0){
					// UPDATE
					const view = getPreviewStreamParticleView(p.id);

					const indexOfView = inStream;
					const newView = buildPreviewStreamParticleView(p);
					view.get$().after(newView.get$());
					view.get$().remove();
					streamParticlesViews[indexOfView] = newView;



				} else {
					// add
					const view = buildPreviewStreamParticleView(p);
					streamParticlesViews.push(view);
					$el.prepend(view.get$());
				}
			}
		}
	}
};