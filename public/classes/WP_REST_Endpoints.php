<?php


namespace Palasthotel\WordPress\LiveNews;

use Palasthotel\WordPress\LiveNews\Mapper\DataMapper;
use Palasthotel\WordPress\LiveNews\Model\GetParticlesArguments;
use Palasthotel\WordPress\LiveNews\Model\Particle;
use Palasthotel\WordPress\LiveNews\Model\ParticleContent;

/**
 * @property Plugin plugin
 */
class WP_REST_Endpoints {


	/**
	 * routes
	 */
	const ROUTES_NAMESPACE = "live-news/v1";

	/**
	 * arguments
	 */
	const ARG_POST_ID = "post_id";

	const ARG_PARTICLE_ID = "particle_id";

	const ARG_OUTPUT = "output";

	const ARG_UNIX_TIMESTAMP_AFTER = "unix_timestamp_after";

	const ARG_UNIX_TIMESTAMP_BEFORE = "unix_timestamp_before";

	const ARG_NUMBER_OF_PARTICLES = "number_of_particles";

	const ARG_TAGS_ARR = "tags";

	const ARG_ORDER_DIRECTION = "order_direction";

	const ARG_AUTHOR_ID = "author_id";

	const ARG_PARTICLE_CONTENTS_ARR = "particle_contents";

	const ARG_PARTICLE_CONTENT_TYPES_ARR = "particle_content_types";

	/**
	 * Ajax constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );
	}

	public function getParticlesRoute( $post_id ) {
		return rest_url( self::ROUTES_NAMESPACE . "/particles/$post_id" );
	}

	public function getUpdateParticleRoute( $post_id ) {
		return rest_url( self::ROUTES_NAMESPACE . "/particles/$post_id" );
	}

	public function getDeleteParticleRoute( $post_id ) {
		return rest_url( self::ROUTES_NAMESPACE . "/particles/$post_id" );
	}

	public function getUploadAttachmentRoute( $post_id ) {
		return rest_url( self::ROUTES_NAMESPACE . "/attachment/$post_id" );
	}

	public function getRestNonce() {
		return wp_create_nonce( 'wp_rest' );
	}

	public function getRoundRequestTimestamp() {
		return 10;
	}

	public function init_rest_api() {

		//-------------------------------------
		// sanitize and validate
		//-------------------------------------
		$arg_int_optional = array(
			'validate_callback' => function ( $value, $request, $param ) {
				return is_numeric( $value ) || $value == NULL;
			},
			'sanitize_callback' => function ( $value ) {
				return intval( $value );
			},
		);

		$arg_int_required = array(
			'validate_callback' => function ( $value, $request, $param ) {
				return is_numeric( $value );
			},
			'sanitize_callback' => function ( $value ) {
				return intval( $value );
			},
		);

		$arg_string_array_optional = array(
			'validate_callback' => function ( $value, $request, $param ) {
				return is_array( $value ) || $value == NULL;
			},
			'sanitize_callback' => function ( $value ) {
				return array_map( 'sanitize_text_field', $value );
			},
		);

		$arg_string_array_required = array(
			'validate_callback' => function ( $value, $request, $param ) {
				return is_array( $value );
			},
			'sanitize_callback' => function ( $value ) {
				return array_map( 'sanitize_text_field', $value );
			},
		);

		//------------------------------
		// register routes
		//------------------------------
		register_rest_route(
			self::ROUTES_NAMESPACE, '/particles/(?P<' . self::ARG_POST_ID . '>\d+)',
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'route_get_particles' ),
				'args'     => array(
					self::ARG_POST_ID              => $arg_int_required,
					self::ARG_UNIX_TIMESTAMP_AFTER => $arg_int_optional,
					self::ARG_UNIX_TIMESTAMP_BEFORE => $arg_int_optional,
					self::ARG_NUMBER_OF_PARTICLES => $arg_int_optional,
					self::ARG_TAGS_ARR             => $arg_string_array_optional,
					self::ARG_OUTPUT               => array(
						'validate_callback' => function ( $value, $request, $param ) {
							return is_string( $value ) || $value == NULL;
						},
						'sanitize_callback' => function ( $value ) {
							return ( $value != NULL && $value == "html" ) ? "html" : "json";
						},
					),
					self::ARG_ORDER_DIRECTION   => array(
						'validate_callback' => function ( $value, $request, $param ) {
							return is_string( $value ) || $value == NULL;
						},
						'sanitize_callback' => function ( $value ) {
							if(
								$value !== GetParticlesArguments::DIRECTION_ASC
								&&
								$value !== GetParticlesArguments::DIRECTION_DESC
							) return null;
							return $value;
						},
					),
				),
			)
		);
		register_rest_route(
			self::ROUTES_NAMESPACE, '/particles/(?P<' . self::ARG_POST_ID . '>\d+)',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array(
					$this,
					'route_update_particle',
				),
				'args'                => array(
					self::ARG_POST_ID                    => $arg_int_required,
					self::ARG_AUTHOR_ID                  => $arg_int_optional,
					self::ARG_TAGS_ARR                   => $arg_string_array_optional,
					self::ARG_PARTICLE_CONTENTS_ARR      => array(
						'validate_callback' => function ( $value, $request, $param ) {
							return
								is_array( $value )
								&&
								count( array_filter( $value, function ( $candidate ) {
									return ! is_string( $candidate ) && ! is_numeric($candidate);
								} ) ) == 0;
						},
					),
					self::ARG_PARTICLE_CONTENT_TYPES_ARR => $arg_string_array_required,
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_pages' );
				},
			)
		);
		register_rest_route(
			self::ROUTES_NAMESPACE, '/particles/(?P<' . self::ARG_POST_ID . '>\d+)',
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array(
					$this,
					'route_delete_particle',
				),
				'args'                => array(
					self::ARG_POST_ID     => $arg_int_required,
					self::ARG_PARTICLE_ID => $arg_int_required,
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_pages' );
				},
			)
		);

		register_rest_route(
			self::ROUTES_NAMESPACE, '/attachment/(?P<' . self::ARG_POST_ID . '>\d+)',
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'route_upload_attachment' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_pages' );
				},
			)
		);
	}

	/**
	 * get all particles to post id
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function route_get_particles( $request ) {

		$post_id   = $request->get_param( self::ARG_POST_ID );
		$after = $request->get_param( self::ARG_UNIX_TIMESTAMP_AFTER );
		$before = $request->get_param( self::ARG_UNIX_TIMESTAMP_BEFORE );
		$numberOfParticles = $request->get_param( self::ARG_NUMBER_OF_PARTICLES );
		$tags      = $request->get_param( self::ARG_TAGS_ARR );
		$orderDirection = $request->get_param(self::ARG_ORDER_DIRECTION);
		$output    = $request->get_param( self::ARG_OUTPUT );

		$query = new Query($post_id);

		if ( $after ) {
			$query->arguments()->setModifiedSince( $after );
		}

		if ( $before ) {
			$query->arguments()->setModifiedBefore( $before );
		}

		if( $numberOfParticles ) {
			$query->arguments()->setNumberOfParticles($numberOfParticles);
		}

		if ( $tags ) {
			$query->arguments()->setWithTags( $tags );
		}

		switch ($orderDirection){
			case GetParticlesArguments::DIRECTION_DESC:
				$query->arguments()->setOrderDesc();
				break;
			case GetParticlesArguments::DIRECTION_ASC:
				$query->arguments()->setOrderDesc();
				break;
		}

		// save current time before db request
		// to report request time to user for next request
		$time      = time();
		$result = $query->get();

		if( "html" == $output){
			$particles = $this->plugin->dataMapper->particlesToHtmlList( $result->particles );
		} else {
			$particles = DataMapper::particlesToJson( $result->particles );
		}

		$request_timestamp = floor(
			$time / $this->getRoundRequestTimestamp()
         ) * $this->getRoundRequestTimestamp();

		return array(
			"output"            => $output,
			"request_timestamp" => $request_timestamp,
			"particles"         => $particles,
			"numberOfParticles" => $result->numberOfParticles,
		);
	}

	/**
	 * insert or update particle
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function route_update_particle( $request ) {

		$post_id     = $request->get_param( self::ARG_POST_ID );
		$particle_id = $request->get_param( self::ARG_PARTICLE_ID );
		$author_id   = $request->get_param( self::ARG_AUTHOR_ID );
		$tags        = $request->get_param( self::ARG_TAGS_ARR );
		$contents    = $request->get_param( self::ARG_PARTICLE_CONTENTS_ARR );
		$types       = $request->get_param( self::ARG_PARTICLE_CONTENT_TYPES_ARR );

		if ( count( $contents ) !== count( $types ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				'contents and types are not equally sized',
				array(
					'status' => 422,
				)
			);
		}

		// if no author is provided, set current user
		if ( $author_id == NULL ) {
			$author_id = get_current_user_id();
		}

		$particle = new Particle();
		if ( $particle_id ) {
			$particle->id = $particle_id;
		}
		$particle->post_id   = $post_id;
		$particle->author_id = $author_id;
		if ( $tags ) {
			$particle->setTags( $tags );
		}

		foreach ( $contents as $index => $content ) {
			$type = $types[ $index ];

			$particleContent          = new ParticleContent();
			$particleContent->content = $content;
			$particleContent->type    = $type;
			$particle->addContent( $particleContent );
		}


		if ( ! $this->plugin->database->update( $particle ) ) {
			return new \WP_Error( 'could_not_insert_particle', "Could not insert particle", 409 );
		}

		// update modified date
		wp_update_post( array(
			'ID' => $post_id,
			'post_modified_gmt' => date( 'Y:m:d H:i:s' ),
		) );

		return $this->getResponseWithNonce( array(
			"particle" => DataMapper::particleToJson( $particle ),
		) );
	}

	/**
	 * delete particle
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function route_delete_particle( $request ) {
		$post_id   = $request->get_param( self::ARG_POST_ID );
		$particle_id = $request->get_param(self::ARG_PARTICLE_ID);

		$result = $this->plugin->database->deleteParticle($particle_id);

		// update modified date
		wp_update_post( array(
			'ID' => $post_id,
			'post_modified_gmt' => date( 'Y:m:d H:i:s' ),
		) );

		return $this->getResponseWithNonce( array(
			"particle_id" => $particle_id,
			"deleted" => $result
		) );
	}

	/**
	 * upload attachment
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	function route_upload_attachment($request){
		$post_id     = $request->get_param( self::ARG_POST_ID );

		if(get_post_type($post_id) !== Plugin::CPT_LIVE_NEWS)
			return new \WP_Error( 'rest_invalid_param', __( 'Invalid parent type.' ), array( 'status' => 400 ) );

		// Get the file via $_FILES or raw data.
		$files   = $request->get_file_params();
		$headers = $request->get_headers();

		if ( ! empty( $files ) ) {
			$file = $this->upload_from_file( $files, $headers );
		} else {
			return new \WP_Error( 'rest_invalid_param', __( 'No file found.' ), array( 'status' => 400 ) );
		}

		if ( is_wp_error( $file ) ) {
			return $file;
		}

		$name       = wp_basename( $file['file'] );
		$name_parts = pathinfo( $name );
		$name       = trim( substr( $name, 0, -( 1 + strlen( $name_parts['extension'] ) ) ) );

		$url  = $file['url'];
		$type = $file['type'];
		$file = $file['file'];

		// Include image functions to get access to wp_read_image_metadata().
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// use image exif/iptc data for title and caption defaults if possible
		$image_meta = wp_read_image_metadata( $file );

		if ( ! empty( $image_meta ) ) {
			if ( empty( $request['title'] ) && trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
				$request['title'] = $image_meta['title'];
			}

			if ( empty( $request['caption'] ) && trim( $image_meta['caption'] ) ) {
				$request['caption'] = $image_meta['caption'];
			}
		}

		$attachment                 = $prepared_post = new \stdClass();
		$attachment->post_title = $request['title'];
		$attachment->post_excerpt = $request['caption'];
		$attachment->post_parent = (int) $post_id;
		$attachment->post_mime_type = $type;
		$attachment->guid           = $url;

		if ( empty( $attachment->post_title ) ) {
			$attachment->post_title = preg_replace( '/\.[^.]+$/', '', wp_basename( $file ) );
		}

		// $post_parent is inherited from $attachment['post_parent'].
		$id = wp_insert_attachment( wp_slash( (array) $attachment ), $file, 0, true );

		if ( is_wp_error( $id ) ) {
			if ( 'db_update_error' === $id->get_error_code() ) {
				$id->add_data( array( 'status' => 500 ) );
			} else {
				$id->add_data( array( 'status' => 400 ) );
			}
			return $id;
		}

		// Include admin function to get access to wp_generate_attachment_metadata().
		require_once ABSPATH . 'wp-admin/includes/media.php';

		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

		if ( class_exists( "RegenerateThumbnails_Regenerator" ) ) {

			$regen = \RegenerateThumbnails_Regenerator::get_instance( $id );
			if ( is_wp_error( $regen ) ) {
				return new \WP_Error('regenerate', "Could not regenereate image sizes");
			}
			$regen->regenerate( array(
				'only_regenerate_missing_thumbnails'  => true,
				'delete_unregistered_thumbnail_files' => false,
			) );
		}

		return $this->getResponseWithNonce(array(
			"attachment_id" => $id,
			"src" => wp_get_attachment_image_src($id),
		));
	}

	/**
	 * Handles an upload via multipart/form-data ($_FILES).
	 *
	 * @since 4.7.0
	 *
	 * @param array $files   Data from the `$_FILES` superglobal.
	 * @param array $headers HTTP headers from the request.
	 * @return array|\WP_Error Data from wp_handle_upload().
	 */
	protected function upload_from_file( $files, $headers ) {
		if ( empty( $files ) ) {
			return new \WP_Error( 'rest_upload_no_data', __( 'No data supplied.' ), array( 'status' => 400 ) );
		}

		/** Include admin function to get access to wp_handle_upload(). */
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$file = wp_handle_upload( $files['file'] , array(
			'test_form' => false,
		));

		if ( isset( $file['error'] ) ) {
			return new \WP_Error( 'rest_upload_unknown_error', $file['error'], array( 'status' => 500 ) );
		}

		return $file;
	}


	/**
	 * merge nonce into data
	 * @param array $data
	 *
	 * @return array
	 */
	private function getResponseWithNonce( $data ) {
		return array_merge( array( "wp_rest_nonce" => $this->getRestNonce() ), $data );
	}


}