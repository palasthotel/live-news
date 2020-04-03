import {svgDown, svgMove, svgUp} from './icons';

export class ContentType{

	constructor(){
		this.$editor = $("<div></div>").addClass("particle-content-type live-news-editor__item");
		this.$preview = $("<div></div>").addClass("particle-content-type--preview");
	}

	editor$(){

		if(!this.$editor.empty()) return this.$editor;

		this.$toolbar = $(`
                <div class="live-news-editor__toolbar">
                    <div class="live-news-editor__mover">
                        <button class="live-news-editor__up">

                            ${svgUp}
                        </button>
                        <span class="live-news-editor__handle">
                            ${svgMove}
                        </span>
                        <button class="live-news-editor__down">
                            ${svgDown}
                        </button>
                    </div>

                </div>`);
		this.$editor.append(this.$toolbar);

		return this.$editor;
	}

	preview$(){
		return $("<p/>").text(this.getValue());
	}

	setValue(value){}

	getValue(){	}

	setDisabled(isDisabled){

	}
}

ContentType.prototype.slug = "";