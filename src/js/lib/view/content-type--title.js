import {ContentType} from './content-type';

export class ContentType_Title extends ContentType{

	constructor($){
		super($);
		this._tmp_val = "";
	}

	editor$() {
		if(!this.$editor.empty()) return this.$editor;

		// super.editor$();

		const val = this.getValue();

		this.$content = $("<input />")
			.addClass("live-news-input__input-text")
			.attr("type", "text")
			.attr("placeholder", "Titel (optional)")
			.val(val);
		this.$editor.append(this.$content);
		return this.$editor;
	}

	preview$() {
		return $("<h3/>").text(this.getValue());
	}

	setValue(val){
		if(this.$content){
			this.$content.val(val);
		}  else {
			this._tmp_val = val;
		}
	}

	getValue() {
		return (this.$content)? this.$content.val(): this._tmp_val;
	}

	setDisabled(isDisabled) {
		if(isDisabled){
			this.$content.attr("disabled", "disabled");
		} else {
			this.$content.removeAttr("disabled");
		}

	}

}

ContentType_Title.prototype.slug = "title";