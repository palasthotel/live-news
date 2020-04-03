<?php
/**
 * @var $this \Palasthotel\WordPress\LiveNews\Render
 * @var $post \WP_Post
 * @var $particles \Palasthotel\WordPress\LiveNews\Query
 */

use Palasthotel\WordPress\LiveNews\Plugin;

$particles->arguments()->setNumberOfParticles(10);
$result = $particles->get();
echo "<ul>";
foreach ($result->particles as $particle){
	$this->renderParticle($particle);
}
echo "</ul>";

if($result->numberOfParticles > count($result->particles)){
	printf(
		"<button id='%s'>%s</button>",
		Plugin::SELECTOR_LOAD_MORE_ID,
		__("Load more", Plugin::DOMAIN)

	);
}