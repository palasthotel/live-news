<?php


namespace Palasthotel\WordPress\LiveNews;


class Render {

    public Plugin $plugin;

	/**
	 * @var array
	 */
	private $sub_dirs;

	/**
	 * Render constructor
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->sub_dirs = null;
		add_action('init', function(){
			if($this->shouldRenderToTheContent()) $this->add_content_filter();

		});
	}

	/**
	 * add the content filter
	 */
	function add_content_filter(){
		add_filter('the_content', array($this, 'the_content'), $this->getTheContentFilterPriority());
	}

	/**
	 * remove the content filter
	 */
	function remove_content_filter(){
		remove_filter('the_content', array($this, 'the_content'), $this->getTheContentFilterPriority());
	}

	/**
	 * should we add the particles to the_content
	 * @return boolean
	 */
	public function shouldRenderToTheContent(){
		return apply_filters(Plugin::FILTER_SHOULD_RENDER_TO_THE_CONTENT, true);
	}

	/**
	 * the priority for the_content filter which adds particles content
	 * @return int
	 */
	public function getTheContentFilterPriority(){
		return apply_filters(Plugin::FILTER_THE_CONTENT_PRIORITY, 10);
	}

	/**
	 * render with the_content filter
	 */
	public function the_content($content){
		if(
			get_post_type() != Plugin::CPT_LIVE_NEWS
			||
			!$this->shouldRenderToTheContent()
		) return $content;

		// remove it so if we use it in the render particles process it won't be a recursion
		remove_filter('the_content', array($this, 'the_content'), $this->getTheContentFilterPriority());
		ob_start();
		$this->renderPost(get_post());
		$content.= ob_get_contents();
		ob_end_clean();
		add_filter('the_content', array($this, 'the_content'), $this->getTheContentFilterPriority());

		return $content;
	}

	/**
	 * @param \WP_Post $post
	 */
	public function renderPost($post){
		if(Plugin::CPT_LIVE_NEWS != $post->post_type) return;

		if(!isset($post->particles)) {
			try{
				$this->plugin->customPostType->the_post($post);
			} catch ( \Exception $e ) {
				error_log($e->getMessage());
			}
		}

		if(!($post->particles instanceof Query)){
			error_log("$post->ID: particles is not an array. Cannot render...");
			return;
		}
		$particles = $post->particles;

		if($this->plugin->proLitteris->isMessageTheContent()){
			include $this->get_template_path(Plugin::TEMPLATE_THE_PARTICLES_PRO_LITTERIS);
		} else {
			include $this->get_template_path(Plugin::TEMPLATE_THE_PARTICLES);
		}
	}

	/**
	 * @param Model\Particle $particle
	 */
	public function renderParticle($particle){
		include $this->get_template_path(Plugin::TEMPLATE_THE_PARTICLE);
	}

	/**
	 * @param Model\ParticleContent $particleContent
	 */
	public function renderParticleContent($particleContent){
		$typeTemplate = $this->get_template_path(
			sprintf(
				Plugin::TEMPLATE_THE_PARTICLE_CONTENT_TYPE,
				$particleContent->type
			)
		);

		if($typeTemplate){
			include $typeTemplate;
		} else {
			include $this->get_template_path(Plugin::TEMPLATE_THE_PARTICLE_CONTENT);
		}
	}

	/**
	 * Look for existing template path
	 * @return string|false
	 */
	function get_template_path( $template ) {

		// theme or child theme
		if ( $overridden_template = locate_template( $this->get_template_dirs($template) ) ) {
			return $overridden_template;
		}

		// parent theme
		foreach ($this->get_template_dirs($template) as $path){
			if( is_file( get_template_directory()."/$path")){
				return get_template_directory()."/$path";
			}
		}

		// other plugins
		$paths = apply_filters(Plugin::FILTER_ADD_TEMPLATE_PATHS, array());
		// add default templates at last position
		$paths[] = $this->plugin->path . 'templates';
		// find templates
		foreach ($paths as $path){
			if(is_file("$path/$template")){
				return "$path/$template";
			}
		}

		// if nothing found...
		return false;
	}

	/**
	 * get array of possible template files in theme
	 * @param $template
	 *
	 * @return array
	 */
	function get_template_dirs($template){
		$dirs = array(
			Plugin::THEME_FOLDER . "/" . $template,
		);
		foreach ($this->get_sub_dirs() as $sub){
			$dirs[] = $sub.'/'.$template;
		}
		return $dirs;
	}

	/**
	 * paths for locate_template
	 * @return array
	 */
	function get_sub_dirs(){
		if($this->sub_dirs == null){
			$this->sub_dirs = array();
			$dirs = array_filter(glob(get_template_directory().'/'.Plugin::THEME_FOLDER.'/*'), 'is_dir');
			foreach($dirs as $dir){
				$this->sub_dirs[] = str_replace(get_template_directory().'/', '', $dir);
			}
		}
		return $this->sub_dirs;
	}


}
