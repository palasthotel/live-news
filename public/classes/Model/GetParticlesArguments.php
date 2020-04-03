<?php


namespace Palasthotel\WordPress\LiveNews\Model;


/**
 * @property int post_id
 * @property string[] withTags
 * @property null|int $modifiedSinceUnixTimestamp
 */
class GetParticlesArguments {

	/**
	 * GetParticlesArguments constructor.
	 *
	 * @param int $post_id
	 */
	private function __construct( $post_id ) {
		$this->post_id                    = $post_id;
		$this->withTags                   = array();
		$this->modifiedSinceUnixTimestamp = NULL;
	}

	/**
	 * @param $post_id
	 *
	 * @return GetParticlesArguments
	 */
	public static function build( $post_id ) {
		return new GetParticlesArguments( $post_id );
	}

	/**
	 * @param string[] $tags
	 *
	 * @return $this
	 */
	public function setWithTags( $tags ) {
		$this->withTags = $tags;

		return $this;
	}

	/**
	 * @param int $unix_timestamp
	 *
	 * @return $this
	 */
	public function setModifiedSince( $unix_timestamp ) {
		$this->modifiedSinceUnixTimestamp = $unix_timestamp;

		return $this;
	}

}