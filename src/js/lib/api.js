
/**
 * shorten function for internal usage
 */
const esc = encodeURIComponent;

/**
 * sanitize params and only return valid params
 * @param unix_timestamp
 * @param tags
 * @param output
 */
const getSanitizeParams = (
	{
		unix_timestamp = null,
		tags = null,
		output = "json",
	} = {}
)=>{

	const params = {};

	if(unix_timestamp !== null){
		params.unix_timestamp = parseInt(unix_timestamp);
	}

	if(tags !== null){
		if(typeof tags !== typeof []) throw new Error("Tags need to be an array of strings");
		params.tags = tags.map(esc);
	}

	if(output !== "html") output = "json";
	params.output = output;

	return params;

};

/**
 * stringify params
 * @param params
 * @return {string}
 */
const getParamsString = (params)=>{
	return Object
		.keys(params)
		.map(param => {
			const val = params[param];
			if(typeof val === typeof []){
				return val.map(v=> `${param}[]=${v}`).join("&");
			}
			return param + "=" + params[param]
		})
		.join("&");
};

/**
 * sanitize and stringify params
 * @param params
 * @return {string}
 */
const getSanitizedParamsString = (params)=>{
	return getParamsString(getSanitizeParams(params));
};


/**
 * api for public use
 */
export const publicApi = (config) => {

	const {routes} = config;
	let last_request_timestamp = config.last_request_timestamp;

	/**
	 * resolve particles response and save request timestamp
	 * @param res
	 * @return {*}
	 */
	const resolveParticles = (res)=>{
		return res.json().then(json => {
			last_request_timestamp = json.request_timestamp;
			return json.particles;
		});
	};

	return {
		fetchParticles: (params)=>{
			return fetch(routes.getParticles+"?"+getSanitizedParamsString(params),{
				method: 'GET',
				})
				.then(resolveParticles)
				.catch(console.error);
		},
		fetchParticlesUpdate: (params)=>{
			const p = {
				unix_timestamp: last_request_timestamp,
				...params,
			};
			return fetch(routes.getParticles+"?"+getSanitizedParamsString(p))
				.then(resolveParticles)
				.catch(console.error);
		},
		resetLastRequestTimestamp: ()=>{
			last_request_timestamp = 0;
		}
	}
};

export class PublicApi{
	constructor(config){
		this.api = publicApi(config);
		this.fetchParticles = this.api.fetchParticles;
		this.fetchParticlesUpdate = this.api.fetchParticlesUpdate;
		this.resetLastRequestTimestamp = this.api.resetLastRequestTimestamp;
	}

	fetchParticles(params){
		return this.api.fetchParticles(params);
	}

	fetchParticlesUpdate(params){
		return this.api.fetchParticlesUpdate(params);
	}

	resetLastRequestTimestamp(){
		this.api.resetLastRequestTimestamp();
	}
}

/**
 * api for admin use
 */
export const adminApi = (config)=>{

	const {routes} = config;
	let wp_rest_nonce = config.wp_rest_nonce;
	const pApi = publicApi(config);

	/**
	 * post request to backend and save new nonce of response
	 * @param {string} route
	 * @param {object} data
	 * @param {string} method
	 * @return {Promise<any | void>}
	 */
	const requestWithNonce = (route, data, method)=> fetch(route,{
			method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wp_rest_nonce,
			},
			credentials: 'include',
			body: JSON.stringify(data),
		})
			.then(res => res.json())
			.then(resolveNonce)
			.catch(console.error);

	const postWithNonce = (route, data) => requestWithNonce(route, data, "POST");
	const deleteWithNonce = (route, data) => requestWithNonce(route, data, "DELETE");


	const resolveNonce = (json) => {
		wp_rest_nonce = json.wp_rest_nonce;
		return json;
	};

	const attachmentCache = {};

	return {
		...pApi,
		updateParticle: (particle)=>{
			// TODO: sanitize particle data
			return postWithNonce(routes.updateParticle, particle).then(json=>json.particle);
		},
		deleteParticle: (particle_id)=>{
			return deleteWithNonce(routes.deleteParticle, {particle_id});
		},
		fetchAttachment: (attachment_id)=>{
			if(typeof attachmentCache[attachment_id] === typeof {}){
				return new Promise((resolve)=>{
					resolve(attachmentCache[attachment_id]);
				});
			}
			return fetch(`/wp-json/wp/v2/media/${attachment_id}`)
				.then(res=>res.json())
				.then((attachment)=>{
				attachmentCache[attachment.id] = attachment;
				return attachment;
			}).catch(()=>{
				attachmentCache[attachment_id] = {error:true};
			})
		},
		uploadAttachment: (form, onRightBeforeUpload = null) => {
			return new Promise((resolve, reject)=>{
				// TODO: catch errors
				// use fetch api?
				const formData = new FormData(form);
				if(onRightBeforeUpload) onRightBeforeUpload();
				$.ajax({
					url: routes.upload,
					method: "POST",
					cache       : false,
					contentType : false,
					processData : false,
					headers: {
						// 'Content-Type': 'application/json',
						'X-WP-Nonce': wp_rest_nonce,
					},
					data: formData,
					success: (json)=>{
						resolve(resolveNonce(json));
					},
				});
			});

		},
	}
};

export class AdminApi extends PublicApi{
	constructor(config){
		super(config);
		this.adminApi = adminApi(config);
	}
	updateParticle(particle){
		return this.adminApi.updateParticle(particle);
	}
}