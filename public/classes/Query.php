<?php


namespace Palasthotel\WordPress\LiveNews;


use Palasthotel\WordPress\LiveNews\Model\GetParticlesArguments;


class Query {

	/**
	 * @var Model\GetParticlesArguments arguments
	 */
	private $arguments;

	public function __construct($post_id) {
		$this->arguments = GetParticlesArguments::build($post_id);
	}

	/**
	 * @return Model\GetParticlesArguments
	 */
	public function arguments(){
		return $this->arguments;
	}

	/**
	 * @return Model\QueryResult
	 */
	public function get(){
		return Plugin::instance()->database->getParticles($this->arguments);
	}

}