<?php
/**
 * @var \Palasthotel\WordPress\LiveNews\Render $this
 * @var \Palasthotel\WordPress\LiveNews\Model\ParticleContent $particleContent
 */

echo apply_filters("the_content", $particleContent->content);