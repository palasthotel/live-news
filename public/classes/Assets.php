<?php


namespace Palasthotel\WordPress\LiveNews;


/**
 * @property Plugin plugin
 */
class Assets {

	/**
	 * Assets constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

	}

	/**
	 * register frontend scripts
	 */
	public function wp_enqueue_scripts() {
		if ( get_post_type() == Plugin::CPT_LIVE_NEWS ) {
			$this->enqueueFrontend( get_the_ID() );
		}
	}


	/**
	 * register scripts for later use
	 */
	public function admin_init() {
		wp_register_script(
			Plugin::HANDLE_EDITOR_JS,
			$this->plugin->url . "/dist/js/editor.min.js",
			array(),
			filemtime( $this->plugin->path . "/dist/js/editor.min.js" ),
			true
		);
		wp_register_style(
			Plugin::HANDLE_EDITOR_STYLE,
			$this->plugin->url . "/dist/css/editor.min.css",
			array(),
			filemtime( $this->plugin->path . "/dist/css/editor.min.css" )
		);
	}

	/**
	 * editor assets
	 *
	 * @param int $postId
	 */
	public function enqueueEditor( $postId ) {
		wp_enqueue_script( Plugin::HANDLE_EDITOR_JS );
		$tags = $this->plugin->database->getTags();

		$editor_config = array(
			"removeformatPasted" => true,
			"tagsToRemove"       => [ 'img', 'script', 'pre' ],
			"svgPath"            => '../wp-content/plugins/live-news/dist/assets/svg/icons.svg',
			"btns"               => array(
				array( 'viewHTML' ),
				array( 'undo', 'redo' ),
				array( 'formatting' ),
				array( 'foreColor', 'backColor' ),
				array( 'strong', 'em', 'del' ),
				array( 'superscript', 'subscript' ),
				array( 'link' ),
				array( 'unorderedList', 'orderedList' ),
				array( 'removeformat' ),
			),
			"plugins"            => array(
				"allowTagsFromPaste" => array(
					"allowedTags" =>
						array( 'h1', 'h2', 'h3', 'h4', 'p', 'br' )
				),
				"colors"             => array(
					"colorList" =>
						array(
							'000',
							'111',
							'222',
							'ffea00'
						)
				)
			)
		);

		$editor_config = apply_filters( Plugin::FILTER_EDITOR_CONTENT_TYPE_HTML_CONFIG, $editor_config );

		wp_localize_script(
			Plugin::HANDLE_EDITOR_JS,
			"LiveNews",
			array(
				"wp_rest_nonce" => $this->plugin->wp_rest_endpoint->getRestNonce(),
				"dom"           => array(
					"rootId" => Editor::DOM_PARTICLE_EDITOR_ROOT_ID,
				),
				"postId"        => $postId,
				"tags"          => apply_filters( Plugin::FILTER_TAGS, $tags, $tags ),
				"routes"        => array(
					"getParticles"   => $this->plugin->wp_rest_endpoint->getParticlesRoute( $postId ),
					"updateParticle" => $this->plugin->wp_rest_endpoint->getUpdateParticleRoute( $postId ),
					"deleteParticle" => $this->plugin->wp_rest_endpoint->getDeleteParticleRoute( $postId ),
					"upload"         => $this->plugin->wp_rest_endpoint->getUploadAttachmentRoute( $postId ),
				),
				"contentTypes"  => array( "html" => $editor_config )
			)
		);
		wp_enqueue_style( Plugin::HANDLE_EDITOR_STYLE );
	}


	/**
	 * editor assets
	 *
	 * @param int $postId
	 */
	public function enqueueFrontend( $postId ) {
		wp_enqueue_script(
			Plugin::HANDLE_FRONTEND_JS,
			$this->plugin->url . "/dist/js/frontend.min.js",
			array(),
			filemtime( $this->plugin->path . "/dist/js/frontend.min.js" ),
			true
		);
		wp_localize_script(
			Plugin::HANDLE_FRONTEND_JS,
			"LiveNews",
			array(
				"isFetchUpdatesActive"   => true,
				"selectors"              => array(
					"rootId" => Plugin::SELECTOR_ROOT_ID,
					"listId" => Plugin::SELECTOR_LIST_ID,
				),
				"postId"                 => $postId,
				"routes"                 => array(
					"getParticles" => $this->plugin->wp_rest_endpoint->getParticlesRoute( $postId ),
				),
				"last_request_timestamp" => time(),
			)
		);
	}

}