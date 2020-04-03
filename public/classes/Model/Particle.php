<?php


namespace Palasthotel\WordPress\LiveNews\Model;


/**
 * @property \DateTime created
 * @property \DateTime modified
 */
class Particle {

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $post_id;

	/**
	 * @var int
	 */
	public $author_id;

	/**
	 * @var bool
	 */
	public $is_deleted;

	public function __construct() {
		$this->created = new \DateTime();
		$this->modified = new \DateTime();
		$this->is_deleted = false;
	}

	/**
	 * @var string[]
	 */
	private $tags = array();

	/**
	 * @return string[]
	 */
	public function getTags(){
		return $this->tags;
	}

	/**
	 * @param array $tags
	 */
	public function setTags($tags){
		$this->tags = $tags;
	}

	/**
	 * @param string $tag
	 *
	 * @return $this
	 */
	public function addTag($tag){
		if(!in_array($tag, $this->tags)){
			$this->tags[] = $tag;
		}

		return $this;
	}

	/**
	 * @var ParticleContent[] $contents
	 */
	private $contents = array();

	/**
	 * @param ParticleContent $content
	 * @param bool $overwritePosition default is true
	 *
	 * @return $this
	 */
	public function addContent($content, $overwritePosition = true){
		$content->particle_id  = $this->id;
		if($overwritePosition){
			$content->position = (count($this->contents) == 0)?
				0 : max(array_map(function($c){ return $c->position; }, $this->contents))+1;
		}
		$this->contents[] = $content;

		return $this;
	}

	/**
	 * @return ParticleContent[]
	 */
	public function getContents(){
		usort($this->contents, function($a, $b){
			return $a->position - $b->position;
		});
		return $this->contents;
	}
}