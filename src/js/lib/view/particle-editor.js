
export const particleEditor = ($, tags, contentTypeViewProvider, onSubmit, onReset, onCancel) => {

	/**
	 * particle id if edit or null if create
	 * @type {null|int}
	 */
	let particleId = null;
	let authorId = null;

	/**
	 *
	 * @type {ContentType[]}
	 */
	let contents = [];

	// dom element
	const $editor = $("<div></div>").addClass("live-news-editor");

	// ------------------------------------------------------
	// editor form
	// ------------------------------------------------------
	const $form = $("<div></div>").addClass("live-news-input");
	$editor.append($form);
	const $contentViewElements = $("<div></div>").addClass("live-news__items");
	$form.append($contentViewElements);

	// ------------------------------------------------------
	// tags
	// ------------------------------------------------------
	const $tags = $("<div><h4>Eigenschaften wählen</h4></div>").addClass("live-news-input__tags").appendTo($form);
	const $tagList = $("<div></div>").addClass("live-news-input__tags--list").appendTo($tags);
	const renderTag = (tag)=>{
		return $(`<label><input type='checkbox' value="${tag}" />  ${tag}</label>`).addClass("live-news-input__tags--tag")
	};
	const renderTags = (tags)=>{
		$tagList.empty().append(tags.map(renderTag));
	};
	renderTags(tags);


	// ------------------------------------------------------
	// submit
	// ------------------------------------------------------
	const $submit = $(`<button>Nachricht speichern</button>`)
		.attr("type","submit")
		.addClass("live-news-input__submit")
		.on("click", (e)=>{
			e.preventDefault();
			onSubmit();
		})
		.appendTo($form);

	// ------------------------------------------------------
	// reset
	// ------------------------------------------------------
	const $reset = $(`<button>Zurücksetzten</button>`)
		.attr("type","submit")
		.addClass("live-news-input__reset")
		.on("click", (e)=>{
			e.preventDefault();
			onReset();
			reset();
		})
		.appendTo($form);

	// ------------------------------------------------------
	// reset
	// ------------------------------------------------------
	const $cancel = $(`<button>Abbrechen</button>`)
		.attr("type","submit")
		.addClass("live-news-input__cancel")
		.on("click", (e)=>{
			e.preventDefault();
			onCancel();
		})
		.hide()
		.appendTo($form);


	// ------------------------------------------------------
	// loading state
	// ------------------------------------------------------
	const $loading = $("<span class='spinner'></span>").insertAfter($submit);
	const setIsSaving = (isSaving)=>{
		if(isSaving){
			$form.addClass("is-saving");
			$submit.attr("disabled", "disabled");
			$reset.attr("disabled", "disabled");
			$cancel.attr("disabled", "disabled");
			$loading.addClass("is-active");
			getTagInputs$().attr("disabled", "disabled");
		} else {
			$form.removeClass("is-saving");
			$loading.removeClass("is-active");
			$submit.removeAttr("disabled");
			$reset.removeAttr("disabled");
			$cancel.removeAttr("disabled");
			getTagInputs$().removeAttr("disabled");
		}
		for(let c of contents){
			c.setDisabled(isSaving);
		}
	};


	// ------------------------------------------------------
	// functions
	// ------------------------------------------------------

	// get contents of inputs as json object
	const getContents = ()=>{
		return contents.map((content)=>{
			return {
				type: content.slug,
				content: content.getValue(),
			}
		});
	};
	// add content to editor
	const addContent = (_type_string_or_content_view)=>{
		if( typeof _type_string_or_content_view === typeof ""){
			const content = contentTypeViewProvider.get(_type_string_or_content_view);
			if(!content) return;
			$contentViewElements.append(content.editor$());
			contents.push(content);
		} else {
			$contentViewElements.append(_type_string_or_content_view.editor$());
			contents.push(_type_string_or_content_view);
		}
	};
	// set list of contents
	const setContents = (contents)=>{
		clearContentViews();
		for(let content of contents){
			addContent(content);
		}
	};

	const getTagInputs$ = () => {
		return $tags.find("[type=checkbox]");
	};

	const getTags = ()=>{
		return getTagInputs$().map((index,input)=>{
			return ($(input).prop("checked"))? input.value: false;
		}).toArray().filter(v=>v !== false);
	};

	const setTags = (tags)=>{
		getTagInputs$().map((index,input)=>{
			if(tags.includes(input.value)){
				$(input).prop("checked", true);
			} else {
				$(input).prop("checked", false);
			}
		})
	};

	// set particle to edit
	const setParticle = ({id, author_id, tags, contentViews}) => {
		particleId = id;
		authorId = author_id;
		setTags(tags);
		setContents(contentViews);
		$cancel.show();
		$reset.hide();
	};

	const reset = ()=>{
		particleId = null;
		authorId = null;
		for( let c of contents){
			c.setValue("");
		}
		$tags.find("input").prop("checked", false);
		$cancel.hide();
		$reset.show();
	};

	const clearContentViews = ()=>{
		$contentViewElements.empty();
		contents = [];
	};


	return {
		get$: ()=> $editor,

		addContent,
		setParticle,

		getId: ()=> particleId,
		getAuthorId: ()=> authorId,

		getContents,
		setContents,

		getTags,
		setTags,

		reset,

		clearContentViews,
		setIsSaving,
	}
};