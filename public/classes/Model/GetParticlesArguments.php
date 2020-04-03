<?php


namespace Palasthotel\WordPress\LiveNews\Model;


/**
 * @property int post_id
 * @property string[] withTags
 * @property null|int modifiedSinceUnixTimestamp
 * @property null|int modifiedBeforeUnixTimestamp
 * @property null|int numberOfParticles
 */
class GetParticlesArguments {

	const DIRECTION_ASC = "ASC";
	const DIRECTION_DESC = "DESC";

	/**
	 * @var string
	 */
	private $orderDirection;

	/**
	 * GetParticlesArguments constructor.
	 *
	 * @param int $post_id
	 */
	private function __construct( $post_id ) {
		$this->post_id                    = $post_id;
		$this->withTags                   = array();
		$this->modifiedSinceUnixTimestamp = NULL;
		$this->modifiedBeforeUnixTimestamp = NULL;
		$this->numberOfParticles = NULL;
		$this->setOrderDesc();
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

	/**
	 * @param int $unix_timestamp
	 *
	 * @return $this
	 */
	public function setModifiedBefore( $unix_timestamp ) {
		$this->modifiedBeforeUnixTimestamp = $unix_timestamp;

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setNumberOfParticles( $value ) {
		$this->numberOfParticles = $value;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function setOrderAsc(){
		$this->orderDirection = self::DIRECTION_ASC;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function setOrderDesc(){
		$this->orderDirection = self::DIRECTION_DESC;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOrderDirection(){
		return $this->orderDirection;
	}

}