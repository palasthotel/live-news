<?php
/**
 * @var $this \Palasthotel\WordPress\LiveNews\Render
 * @var $post \WP_Post
 * @var $particles \Palasthotel\WordPress\LiveNews\Query
 */

use Palasthotel\WordPress\LiveNews\Plugin;

$particles->arguments()->setNumberOfParticles(null);
$result = $particles->get();
echo "<ul>";
foreach ($result->particles as $particle){
	$this->renderParticle($particle);
}
echo "</ul>";