<?php


namespace Palasthotel\WordPress\LiveNews;


/**
 * @property Plugin plugin
 */
class CustomPostType {

	/**
	 * CustomPostType constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_action( 'the_post', array( $this, 'the_post' ) );

		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 90 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * @param \WP_Post $post
	 *
	 */
	function the_post( $post ) {
		if ( Plugin::CPT_LIVE_NEWS == $post->post_type ) {
			$post->particles = new Query($post->ID);
		}
	}

	/**
	 * add adminbar button
	 */
	function admin_bar() {
		/**
		 * @var null|\WP_Post
		 */
		global $post;
		/**
		 * @var \WP_Admin_Bar $wp_admin_bar
		 */
		global $wp_admin_bar;
		if (
			isset( $post->particles )
			||
			(
				is_admin()
				&&
				function_exists('get_current_screen')
				&&
				"post" == get_current_screen()->base
				&&
				Plugin::CPT_LIVE_NEWS == get_current_screen()->id
				&&
				Plugin::CPT_LIVE_NEWS == get_post_type()
			)
		) {
			$wp_admin_bar->add_node( array(
				'id'    => Plugin::MENU_SLUG_PARTICLE_EDITOR,
				'title' => __( 'Particle-Editor', Plugin::DOMAIN ),
				'href'  => $this->plugin->editor->getParticleEditorLink( get_the_ID() ),
			) );
		}
	}

	/**
	 * add the grid action to post types
	 *
	 * @param $actions
	 * @param $entity
	 *
	 * @return array
	 */
	function row_actions( $actions, $entity ) {
		if ( Plugin::CPT_LIVE_NEWS == get_post_type( $entity->ID ) ) {
			$temp                                      = array();
			$link                                      = $this->plugin->editor->getParticleEditorLink( $entity->ID );
			$temp[ Plugin::MENU_SLUG_PARTICLE_EDITOR ] = '<a href="' . $link . '">' . __( 'Particle-Editor', Plugin::DOMAIN ) . '</a>';
			$actions                                   = array_merge( $temp, $actions );
		}

		return $actions;
	}

	/**
	 *
	 */
	public function add_meta_boxes() {
		add_meta_box(
			Plugin::DOMAIN,
			__( 'Live-News', Plugin::DOMAIN ),
			function ( $post ) {
			    $canGoToEditor = !(get_post_status( $post->ID ) == 'auto-draft' );
			    do_action(Plugin::ACTION_META_BOX_BEFORE, $post, $canGoToEditor);
				if ( $canGoToEditor ) {
					printf(
						"<p style='text-align: center;'><a style='display: block; width: 100%%;' class='button' href='%s'>%s</a></p>",
						$this->plugin->editor->getParticleEditorLink( $post->ID ),
						__( "Particle-Editor", Plugin::DOMAIN )
					);
				} else {
					printf(
						"<p>%s</p>",
						__( "You have to save the Live-News first, than you can use the Particle-Editor.", Plugin::DOMAIN )
					);
				}
				do_action(Plugin::ACTION_META_BOX_AFTER, $post, $canGoToEditor);
			},
			Plugin::CPT_LIVE_NEWS,
			'side',
			'high'
		);
	}



	function init() {

		$labels = array(
			'name'                  => __( 'Live-News', Plugin::DOMAIN ),
			'singular_name'         => __( 'Live-News', Plugin::DOMAIN ),
			'menu_name'             => __( 'Live-News', Plugin::DOMAIN ),
			'name_admin_bar'        => __( 'Live-News', Plugin::DOMAIN ),
			'archives'              => __( 'Live-News Archives', Plugin::DOMAIN ),
			'attributes'            => __( 'Live-News Attributes', Plugin::DOMAIN ),
			'parent_item_colon'     => __( 'Parent Item:', Plugin::DOMAIN ),
			'all_items'             => __( 'All Live-News', Plugin::DOMAIN ),
			'add_new_item'          => __( 'Add New Live-News', Plugin::DOMAIN ),
			'add_new'               => __( 'New', Plugin::DOMAIN ),
			'new_item'              => __( 'New Live-News', Plugin::DOMAIN ),
			'edit_item'             => __( 'Edit Live-News', Plugin::DOMAIN ),
			'update_item'           => __( 'Update Live-News', Plugin::DOMAIN ),
			'view_item'             => __( 'View Live-News', Plugin::DOMAIN ),
			'view_items'            => __( 'View Live-News', Plugin::DOMAIN ),
			'search_items'          => __( 'Search Live-News', Plugin::DOMAIN ),
			'not_found'             => __( 'Not found', Plugin::DOMAIN ),
			'not_found_in_trash'    => __( 'Not found in Trash', Plugin::DOMAIN ),
			'featured_image'        => __( 'Featured Image', Plugin::DOMAIN ),
			'set_featured_image'    => __( 'Set featured image', Plugin::DOMAIN ),
			'remove_featured_image' => __( 'Remove featured image', Plugin::DOMAIN ),
			'use_featured_image'    => __( 'Use as featured image', Plugin::DOMAIN ),
			'insert_into_item'      => __( 'Insert into item', Plugin::DOMAIN ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', Plugin::DOMAIN ),
			'items_list'            => __( 'Items list', Plugin::DOMAIN ),
			'items_list_navigation' => __( 'Items list navigation', Plugin::DOMAIN ),
			'filter_items_list'     => __( 'Filter items list', Plugin::DOMAIN ),
		);
		$args   = array(
			'label'               => __( 'Live-News', Plugin::DOMAIN ),
			'description'         => __( 'Newsstream', Plugin::DOMAIN ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'comments',
				'revisions',
			),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'menu_icon'           => 'dashicons-format-status',
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);

		do_action( Plugin::ACTION_BEFORE_REGISTER_POST_TYPE );

		register_post_type( Plugin::CPT_LIVE_NEWS, apply_filters( Plugin::FILTER_REGISTER_POST_TYPE_ARGS, $args ) );

	}

}