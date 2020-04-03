import "trumbowyg";
import "trumbowyg/plugins/emoji/trumbowyg.emoji";
import "trumbowyg/plugins/history/trumbowyg.history";
import "trumbowyg/plugins/cleanpaste/trumbowyg.cleanpaste";
import "trumbowyg/plugins/allowtagsfrompaste/trumbowyg.allowtagsfrompaste";
import "trumbowyg/plugins/colors/trumbowyg.colors";

import {ContentType} from './content-type';

export class ContentType_HTML extends ContentType {

    constructor($) {
        super($);
        this._tmp_val = "";
    }

    editor$() {
        if (!this.$editor.empty()) return this.$editor;

        this.$textarea = $("<textarea/>").addClass("live-news-input__textarea");
        this.$editor.append(this.$textarea);
        this.$textarea.trumbowyg(LiveNews.contentTypes.html);
        if (this._tmp_val) {
            this.setValue(this._tmp_val);
        }

        return this.$editor;
    }

    preview$() {
        return $("<div></div>").html(this.getValue());
    }

    getValue() {
        return (this.$textarea) ? this.$textarea.trumbowyg('html') : this._tmp_val;
    }

    setValue(value) {
        if (this.$textarea) {
            this.$textarea.trumbowyg('html', value);
        } else {
            this._tmp_val = value;
        }
    }

    setDisabled(isDisabled) {
        if (isDisabled) {
            this.$textarea.trumbowyg("disable");
        } else {
            this.$textarea.trumbowyg("enable");
        }
    }

}

ContentType_HTML.prototype.slug = "html";