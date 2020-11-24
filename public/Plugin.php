<?php
/**
 *
 * Plugin Name: Live News
 * Plugin URI: https://github.com/EdwardBock/live-news
 * Description: Custom post type for news stream
 * Version: 1.0.0
 * Author: Palasthotel by Stephan <stephan.kroppenstedt@palasthotel.de> & Edward <edward.bock@palasthotel.de>
 * Author URI: https://palasthotel.de
 * Text Domain: live-news
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 5.5.3
 * License: http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 *
 * @copyright Copyright (c) 2019, Palasthotel
 * @package Palasthotel\WordPress\LiveNews
 *
 */

namespace Palasthotel\WordPress\LiveNews;


use Palasthotel\WordPress\LiveNews\Mapper\DataMapper;

/**
 * @property string path
 * @property string url
 * @property CustomPostType $customPostType
 * @property Editor editor
 * @property Assets assets
 * @property Database database
 * @property Render render
 * @property string namespace
 * @property WP_REST_Endpoints wp_rest_endpoint
 * @property DataMapper dataMapper
 * @property QueryManipulation queryManipulation
 */
class Plugin {

	const DOMAIN = "live-news";

	/**
	 * actions
	 */
	const ACTION_META_BOX_BEFORE = "live_news_meta_box_before";

	const ACTION_META_BOX_AFTER = "live_news_meta_box_after";

	const ACTION_BEFORE_REGISTER_POST_TYPE = "live_news_before_register_post_type";

	/**
	 * filters
	 */
	const FILTER_SHOULD_RENDER_TO_THE_CONTENT = "live_news_should_render_to_the_content";

	const FILTER_THE_CONTENT_PRIORITY = "live_news_the_content_priority";

	const FILTER_ADD_TEMPLATE_PATHS = "live_news_add_template_paths";

	const FILTER_TAGS = "live_news_tags";

	const FILTER_REGISTER_POST_TYPE_ARGS = "live_news_register_args";

	const FILTER_EDITOR_CONTENT_TYPE_HTML_CONFIG = "live_news_editor_content_type_html_config";

	/**
	 * slugs
	 */
	const CPT_LIVE_NEWS = "live-news";

	const MENU_SLUG_PARTICLE_EDITOR = "particle-editor";

	const HANDLE_EDITOR_JS = "live-news-editor-js";

	const HANDLE_FRONTEND_JS = "live-news-frontend-js";

	const HANDLE_EDITOR_STYLE = "live-news-editor-style";

	/**
	 * templates
	 */
	const THEME_FOLDER = "live-news";

	const TEMPLATE_THE_PARTICLES = "the_particles.php";

	const TEMPLATE_THE_PARTICLE = "the_particle.php";

	const TEMPLATE_THE_PARTICLE_CONTENT = "the_particle_content.php";

	const TEMPLATE_THE_PARTICLE_CONTENT_TYPE = "the_particle_content--%s.php";

	/**
	 * frontend dom selectors
	 */
	const SELECTOR_ROOT_ID = "live-news";

	const SELECTOR_LIST_ID = "live-news-list";

	const SELECTOR_LOAD_MORE_ID = "live-news-load-more";

	/**
	 * Plugin constructor.
	 */
	private function __construct() {

		load_plugin_textdomain(
			Plugin::DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		$this->namespace = __NAMESPACE__;
		$this->path      = plugin_dir_path( __FILE__ );
		$this->url       = plugin_dir_url( __FILE__ );

		require_once dirname( __FILE__ ) . "/vendor/autoload.php";

		$this->database         = new Database();
		$this->dataMapper       = new DataMapper( $this );
		$this->wp_rest_endpoint = new WP_REST_Endpoints( $this );
		$this->customPostType   = new CustomPostType( $this );
		$this->render           = new Render( $this );
		$this->editor           = new Editor( $this );
		$this->assets           = new Assets( $this );

		$this->queryManipulation = new QueryManipulation($this);
		
		register_activation_hook( __FILE__, array( $this, "on_activate" ) );
	}

	/**
	 * on plugin activation
	 */
	public function on_activate() {
		$this->database->createTables();
		$this->customPostType->init();
		flush_rewrite_rules();
	}

	private static $instance;

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( self::$instance == NULL ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

}

Plugin::instance();

require_once dirname( __FILE__ ) . "/public-functions.php";