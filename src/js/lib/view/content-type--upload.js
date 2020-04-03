import {ContentType} from './content-type';

export class ContentType_Upload extends ContentType{

	constructor($){
		super($);
		this.val = "";
		this.fetched_id = null;
	}

	editor$() {
		if(!this.$editor.empty()) return this.$editor;

		const $content = $("<lable><strong>Bild (optional)</strong><br></lable>");
		const $input = $("<input />")
			.addClass("live-news-input__input-file")
			.attr("name", "file")
			.attr("type", "file")
			.attr("placeholder", "Bild (optional)")
			.attr("accept", "image/x-png,image/gif,image/jpeg")
			.appendTo($content);

		this.$input = $input;

		const $form = $("<form></form>");
		$form.append($content);

		$form.on("submit",(e)=>{
			e.preventDefault();
			if($input.val() === "") return;
			this.setFetchingImages(true);
			LiveNews.api.uploadAttachment(
				$form[0],
				()=>{
					this.setUploadingImage(true);
				}).then((data)=>{
				this.setUploadingImage(false);
				this.setValue(data.attachment_id);
			});
		});
		$input.on("change", ()=> $form.submit());

		// build ui
		this.$editor.append($form);
		const $wrapper = $("<div></div>");

		this.getEditorImg$().appendTo($wrapper.appendTo($form));

		this.$delete = $("<button>Bild entfernen</button>");
		this.$delete.on("click",(e)=>{
			e.preventDefault();
			this.setValue("");
		});
		$wrapper.append(this.$delete);
		this.updateDeleteButton();

		return this.$editor;
	}

	setFetchingImages(isFetching){
		this.setFetchingImage(this.getEditorImg$(), isFetching);
		this.setFetchingImage(this.getImg$(), isFetching);
	}

	setFetchingImage($wrapper, isFetching){
		if(!$wrapper) return;
		if(isFetching){
			$wrapper.find(".spinner").addClass("is-active");
		} else {
			$wrapper.find(".spinner").removeClass("is-active");
		}
	}
	setUploadingImage(isUploading){
		this.setDisabled(isUploading);
	}

	renderImages(attachment){
		this.renderImage(this.$img, attachment);
		this.renderImage(this.$editorImg, attachment);
	}

	renderImage($imgWrapper, attachment){
		if(!$imgWrapper) return;
		if(attachment !== null && typeof attachment === typeof {}){
			if(attachment.media_details){
				$imgWrapper.show();
				$imgWrapper.find("img").attr("src", attachment.media_details.sizes.medium.source_url);
			} else {
				$imgWrapper.replaceWith("<p>🖼 Image lost!</p>");
			}
		} else {
			$imgWrapper.hide();
		}
	}

	getEditorImg$(){
		if(!this.$editorImg){
			this.$editorImg = $("<div class='particle__content-type--upload'><span class='spinner'></span><img class='particle__img'/></div>");
		}
		this.fetchAttachment();
		return this.$editorImg;
	}

	getImg$(){
		if(!this.$img){
			this.$img = $("<div class='particle__content-type--upload'><span class='spinner'></span><img class='particle__img'/></div>");
		}
		this.fetchAttachment();
		return this.$img;
	}

	preview$() {
		return this.getImg$();
	}

	fetchAttachment(){
		if(
			this.val === ""
			||
			this.isFetching === true
			||
			this.fetched_id === this.val
		) return;

		this.isFetching = true;
		this.setFetchingImages(true);
		LiveNews.api.fetchAttachment(this.val).then((attachment)=>{
			this.isFetching = false;
			this.fetched_id = this.val;
			this.setFetchingImages(false);
			this.renderImages(attachment)
		});
	}

	setValue(attachment_id){
		this.val = attachment_id;
		// clear file field
		if(this.$input) this.$input.val("");
		if(attachment_id === ""){
			this.renderImage(this.$editorImg, null);
		}
		this.updateDeleteButton();
		this.fetchAttachment();
	}

	getValue() {
		return this.val;
	}

	updateDeleteButton(){
		this.showDeleteImage(this.getValue() !== "");
	}

	showDeleteImage(show){
		if(this.$delete){
			if(show) this.$delete.show();
			else if(!show) this.$delete.hide();
		}
	}

	setDisabled(isDisabled) {
		if(isDisabled){
			this.$input.attr("disabled", "disabled");
		} else {
			this.$input.removeAttr("disabled");
		}
	}

}

ContentType_Upload.prototype.slug = "upload";