import {adminApi} from './lib/api';
import {application} from './lib/application';
import {ContentType_HTML} from './lib/view/content-type--html';
import {ContentType_Title} from './lib/view/content-type--title';
import {ContentType_Upload} from './lib/view/content-type--upload';

import '../scss/editor.scss';

(function(config) {

	window.LiveNews = config;

	const api = adminApi(config);
	window.LiveNews.api = api;

	// ugly fix. please give it down as an dependency
	window.$ = jQuery;

	const app = application(config, api, jQuery);
	window.LiveNews.app = app;

	app.contentTypeProvider.add(ContentType_Title.prototype.slug, ContentType_Title);
	app.contentTypeProvider.add(ContentType_HTML.prototype.slug, ContentType_HTML);
	app.contentTypeProvider.add(ContentType_Upload.prototype.slug, ContentType_Upload);

	app.init();

})(LiveNews);




