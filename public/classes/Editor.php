<?php


namespace Palasthotel\WordPress\LiveNews;


/**
 * @property Plugin plugin
 */
class Editor {

	const DOM_PARTICLE_EDITOR_ROOT_ID = "live-news-root";

	/**
	 * Editor constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_menu() {
		add_submenu_page(
			NULL,
			'Particle Editor',
			'Particle Editor',
			'edit_pages',
			Plugin::MENU_SLUG_PARTICLE_EDITOR,
			array(
				$this,
				'render',
			)
		);
	}

	public function getParticleEditorLink( $postId ) {
		return add_query_arg(
			array(
				'page'   => Plugin::MENU_SLUG_PARTICLE_EDITOR,
				'postid' => $postId,
			),
			admin_url( 'admin.php' )
		);
	}

	function render() {
		$postId = intval( $_GET["postid"] );
		$post   = get_post( $postId );
		?>
		<div class="wrap">
			<?php $this->renderTitle( $post ); ?>
			<?php $this->renderEditor( $post ); ?>
		</div>
		<?php
	}

	private function renderTitle( $post ) {
		?>
		<h2>
			<?php echo $post->post_title; ?>
			<a
					title="Return to the post-edit page"
					class="add-new-h2"
					href="<?php echo get_edit_post_link( $post->ID ); ?>"
			><?php _e( 'Edit Live-News', Plugin::DOMAIN ); ?></a>
			<a class="add-new-h2"
			   href="<?php echo get_permalink( $post->ID ); ?>"
			><?php _e( 'View Live-News', Plugin::DOMAIN ); ?></a>
		</h2>
		<?php
	}

	/**
	 * @param \WP_Post $post
	 */
	private function renderEditor( $post ) {
		$this->plugin->assets->enqueueEditor( $post->ID );
		?>
        <div id="<?php echo Editor::DOM_PARTICLE_EDITOR_ROOT_ID; ?>" class="live-news__interface" ></div>
		<?php
	}
}