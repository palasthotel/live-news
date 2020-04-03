<?php
/**
 * @var $this \Palasthotel\WordPress\LiveNews\Render
 * @var $post \WP_Post
 * @var $particles \Palasthotel\WordPress\LiveNews\Model\Particle[]
 */

echo "<ul>";
foreach ($particles as $particle){
	$this->renderParticle($particle);
}
echo "</ul>";