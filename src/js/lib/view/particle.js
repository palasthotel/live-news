
export const particlePreview = (particle, onEdit, onCancelEdit, onDelete)=>{

	const {id, created_timestamp, author, author_id, tags} = particle;
	const created = new Date(parseInt(created_timestamp)*1000);

	const $header = $(`<div class="live-news-particle__header">

        <div class="live-news-particle__meta">
                <span class="live-news-particle__date">${created.toLocaleDateString()} ${created.toLocaleTimeString()}</span>
                <span class="live-news-particle__tags">${tags.join(", ")}</span>
        </div>

        <div class="live-news-particle__author">
            <div class="live-news-particle__name">${(author)? author: author_id}</div>
        </div>

    </div>`);

	const $content = $("<div/>").addClass("live-news-particle__content");
	$content.append((particle.contentViews.map((view)=>view.preview$())));

	const $footer = $(`<nav class="live-news-particle__options">
            <ul>
                <li>
                    <a data-action="edit" href="#">Bearbeiten</a>
                </li>
                <li>
                    <a data-action="delete" href="#">Löschen</a>
                </li>
            </ul>
       </nav>`);
	$footer.on("click", "[data-action=delete]", (e)=>{
		e.preventDefault();
		setLoading(true);
		onDelete(particle);
	});
	$footer.on("click", "[data-action=edit]", (e)=>{
		e.preventDefault();
		const isEditing = $preview.hasClass("is-editing");
		if(isEditing){
			onCancelEdit(particle);
		} else {
			syncContentWithViews();
			onEdit(particle);

		}
		setEditing(!isEditing);
	});

	const syncContentWithViews = ()=>{
		for(let i in particle.contentViews){
			if(!particle.contentViews.hasOwnProperty(i)) continue;
			particle.contentViews[i].setValue(particle.contents[i].content);
		}
	};
	syncContentWithViews();

	const $preview = $("<div></div>").addClass("live-news-particle");
	$preview.append($header);
	$preview.append($content);
	$preview.append($footer);

	// loading layer
	const $loading = $("<div></div>").addClass("live-news-particle__loading").appendTo($preview);
	const $spinner = $("<span></span>").addClass("spinner").appendTo($loading);

	const setLoading = (isLoading)=>{
		if(isLoading){
			$preview.addClass("is-loading");
			$spinner.addClass("is-active");
		} else {
			$preview.removeClass("is-loading");
			$spinner.removeClass("is-active");
		}
	};
	const setEditing = (isEditing)=>{
		if(isEditing){
			$preview.addClass("is-editing");
			$footer.find("[data-action=edit]").text("Abbrechen");
		} else {
			$preview.removeClass("is-editing");
			$footer.find("[data-action=edit]").text("Bearbeiten");
		}
	};

	return {
		get$: ()=> $preview,
		particle,
		setLoading,
		setEditing,
	}
};