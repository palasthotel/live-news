<?php


namespace Palasthotel\WordPress\LiveNews\Mapper;

use Palasthotel\WordPress\LiveNews\Plugin;
use Palasthotel\WordPress\LiveNews\Model\Particle;
use Palasthotel\WordPress\LiveNews\Model\ParticleContent;
use WP_Query;

/**
 * @property Plugin plugin
 */
class DataMapper {

	/**
	 * DataMapper constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct($plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * list of html particles
	 *
	 * @param Particle[] $particles
	 *
	 * @return array
	 */
	public function particlesToHtmlList($particles){
		if(count($particles) < 1) return array();

		$post_id = $particles[0]->post_id;
		if(!$post_id) {
			error_log("Cannot create post context in particlesToHTML DataMapper");
			return array();
		}

		$list = array();
		$query = new WP_Query(["p"=>$post_id, "post_type" => Plugin::CPT_LIVE_NEWS]);
		if($query->have_posts()){
			$query->the_post();

			foreach ($particles as $particle){
				ob_start();
				$this->plugin->render->renderParticle($particle);
				$list[] = array(
					"id" => $particle->id,
					"created" => $particle->created->getTimestamp(),
					"modified" => $particle->modified->getTimestamp(),
					"is_deleted" => $particle->is_deleted,
					"tags" => $particle->getTags(),
					"html" => ob_get_contents(),
				);
				ob_end_clean();
			}

		}

		wp_reset_postdata();

		return $list;
	}

	/**
	 * @param Particle[] $particles
	 *
	 * @return array
	 */
	public static function particlesToJson($particles){
		return array_map(
			function($p){
				/**
				 * @var Particle $p
				 */
				return DataMapper::particleToJson($p);
			},
			$particles
		);
	}

	/**
	 * @param Particle $particle
	 *
	 * @return array
	 */
	public static function particleToJson($particle){
		$author = get_userdata($particle->author_id);
		return array(
			"id" => $particle->id,
			"post_id" => $particle->post_id,
			"author_id" => $particle->author_id,
			"author" => ($author)? $author->display_name:false,
			"created_timestamp" => $particle->created->getTimestamp(),
			"modified_timestamp" => $particle->modified->getTimestamp(),
			"is_deleted" => $particle->is_deleted,
			"tags" => $particle->getTags(),
			"contents" => DataMapper::particleContentsToJson($particle->getContents()),
		);
	}

	/**
	 * @param ParticleContent[] $contents
	 *
	 * @return array
	 */
	public static function particleContentsToJson($contents){
		return array_map(function($c){
			return DataMapper::particleContentToJson($c);
		}, $contents);
	}

	/**
	 * @param ParticleContent $content
	 *
	 * @return array
	 */
	public static function particleContentToJson($content){
		return (array) $content;
	}

}