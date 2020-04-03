<?php

use Palasthotel\WordPress\LiveNews\Model\Particle;
use Palasthotel\WordPress\LiveNews\Plugin;

/**
 * @return Plugin
 */
function live_news_plugin(){
	return Plugin::instance();
}

/**
 * @param null|int $post_id
 *
 * @return Particle[]
 */
function get_the_particles($post_id = null){
	$post = get_post($post_id);
	return (isset($post->particles) && is_array($post->particles))? $post->particles: array();
}

/**
 * @param null|int $post_id
 */
function the_post_particles($post_id = null){
	live_news_plugin()->render->renderPost(get_post($post_id));
}

/**
 * @param null|int $post_id
 *
 * @return array|int[]
 */
function get_the_post_particles_authors($post_id = null){
	$post = get_post($post_id);
	if(!($post instanceof WP_Post)) return array();
	return live_news_plugin()->database->getParticleAuthors($post->ID);
}