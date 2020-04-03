<?php


namespace Palasthotel\WordPress\LiveNews\Model;


/**
 * @property Particle[] particles
 * @property int $numberOfParticles
 */
class QueryResult {

	/**
	 * QueryResult constructor.
	 *
	 * @param Particle[] $particles
	 * @param int $numParticles
	 */
	public function __construct($particles, $numParticles) {
		$this->particles         = $particles;
		$this->numberOfParticles = $numParticles;
	}
}