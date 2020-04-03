
export const contentTypeViewProvider = ()=>{

	/**
	 *
	 * @type {ContentType[]}
	 */
	const contentTypes = [];

	// add a new content type to editor
	const addContentType = (_type, cls) =>{
		if(contentTypes.find(({slug})=> slug === _type)){
			console.error(`content type already exists ${_type}`);
			return false;
		}
		contentTypes.push({
			type: _type,
			cls,
		});
		return true;
	};

	/**
	 *
	 * @param _type
	 * @return {boolean|ContentType}
	 */
	const getContentView = (_type)=>{
		const found = contentTypes.find(({type})=> type === _type);
		if(!found){
			console.log(`cannot find content view of type ${_type}`);
			return false;
		}
		return new found.cls($);
	};

	return {
		add: addContentType,
		get: getContentView,
	}
};