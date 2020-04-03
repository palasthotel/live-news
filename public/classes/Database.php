<?php


namespace Palasthotel\WordPress\LiveNews;


use Palasthotel\WordPress\LiveNews\Model\Particle;
use Palasthotel\WordPress\LiveNews\Model\ParticleContent;

/**
 * @property \wpdb wpdb
 * @property string tableParticles
 * @property string tableContents
 * @property string tableParticlesToTags
 * @property string tableTags
 * @property \DateTimeZone timezone
 */
class Database {

	/**
	 * Database constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb                 = $wpdb;
		$this->tableParticles       = $this->wpdb->prefix . "particles";
		$this->tableContents        = $this->wpdb->prefix . "particle_contents";
		$this->tableParticlesToTags = $this->wpdb->prefix . "particles_to_tags";
		$this->tableTags            = $this->wpdb->prefix . "particle_tags";
		$this->timezone             = new \DateTimeZone( get_option( "timezone_string", "utc" ) );
	}

	/**
	 * @param int $post_id
	 *
	 * @return int[]
	 */
	public function getParticleAuthors($post_id){
		return $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT DISTINCT author_id FROM {$this->tableParticles} WHERE post_id = %d", $post_id
			)
		);
	}

	/**
	 * @param Model\GetParticlesArguments $arguments
	 *
	 * @return Particle[]
	 */
	public function getParticles( $arguments ) {

		$whereIsNotDeleted = "AND is_deleted = 0";
		$whereTags = "";
		if ( count( $arguments->withTags ) > 0 ) {
			$whereTags = "AND tag IN ( '" . implode( "', '", $arguments->withTags ) . "' )";
		}

		$whereSinceModified = "";
		if ( is_int( $arguments->modifiedSinceUnixTimestamp ) ) {
			$whereIsNotDeleted = "";
			$whereSinceModified = "AND modified_timestamp >= $arguments->modifiedSinceUnixTimestamp";
		}

		$results = $this->wpdb->get_results( $this->wpdb->prepare(
			"SELECT 
            $this->tableParticles.id as particle_id, $this->tableContents.id as content_id, 
            post_id, author_id, created_timestamp, modified_timestamp, is_deleted, 
            content, type, position,
            tag            
		FROM $this->tableParticles
        LEFT JOIN $this->tableContents ON ($this->tableParticles.id = $this->tableContents.particle_id) 
		LEFT JOIN $this->tableParticlesToTags ON ($this->tableParticles.id = $this->tableParticlesToTags.particle_id)
        LEFT JOIN $this->tableTags ON ($this->tableParticlesToTags.tag_id = $this->tableTags.id )
		WHERE post_id = %d $whereTags $whereSinceModified $whereIsNotDeleted
		ORDER BY created_timestamp DESC, particle_id ASC, position ASC
    ", $arguments->post_id ) );

		$particle_id = NULL;
		$content_id  = NULL;
		$particle    = NULL;
		$particles   = array();
		foreach ( $results as $item ) {

			// its a new particle? we parse it!
			if ( $particle_id != $item->particle_id ) {
				$particle_id         = intval( $item->particle_id );
				$particle            = new Particle();
				$particle->id        = $particle_id;
				$particle->author_id = intval( $item->author_id );
				$particle->post_id   = intval($item->post_id);
				$particle->is_deleted = intval($item->is_deleted) === 1;

				// created timestamp
				$particle->created->setTimestamp( $item->created_timestamp );
				$particle->created->setTimezone( $this->timezone );

				// modified timestamp
				$particle->modified->setTimestamp( $item->modified_timestamp );
				$particle->modified->setTimezone( $this->timezone );

				$particles[] = $particle;
			}

			// has a tag? we add it!
			if ( isset( $item->tag ) != NULL ) {
				$particle->addTag( $item->tag );
			}

			// its a new particle content? we add it!
			if ( $item->content_id == NULL || $item->content_id == $content_id ) {
				continue;
			}
			$content_id = $item->content_id;

			$particleContent           = new ParticleContent();
			$particleContent->id       = intval( $item->content_id );
			$particleContent->content  = $item->content;
			$particleContent->type     = $item->type;
			$particleContent->position = intval( $item->position );
			$particle->addContent( $particleContent, false );
		}

		return $particles;

	}

	/**
	 * @return string[]
	 */
	public function getTags(){
		return $this->wpdb->get_col("SELECT tag FROM $this->tableTags");
	}

	/**
	 * @param string $tag
	 *
	 * @return int|false
	 */
	public function getTagId($tag){
		$id = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM $this->tableTags WHERE tag = %s",$tag
			)
		);
		if(!$id){
			$success = $this->wpdb->insert($this->tableTags, array("tag" => $tag), array("%s"));
			if($success) $id = $this->wpdb->insert_id;
		}
		return ($id)? intval($id): false;
	}

	/**
	 * @param Model\Particle $particle
	 *
	 * @return bool
	 */
	public function update( $particle ) {
		if ( $particle->id ) {
			$result = $this->wpdb->update(
				$this->tableParticles,
				array(
					"post_id"            => $particle->post_id,
					"author_id"          => $particle->author_id,
					"modified_timestamp" => time(),
				),
				array(
					"id" => $particle->id,
				),
				array( "%d", "%d", "%s" ),
				array( "%d" )
			);
			if ( false === $result ) {
				return false;
			}
		} else {
			$result = $this->wpdb->insert(
				$this->tableParticles,
				array(
					"post_id"   => $particle->post_id,
					"author_id" => $particle->author_id,
					"created_timestamp" =>  time(),
					"modified_timestamp" => time(),
				),
				array( "%d", "%d", "%s", "%s" )
			);
			if ( false === $result ) {
				return false;
			}
			$particle->id = $this->wpdb->insert_id;
		}

		$this->flushContents( $particle->id );
		foreach ( $particle->getContents() as $pos => $value ) {
			// ☝️ if particle was just inserted we need to add particle id
			$value->particle_id = $particle->id;
			$this->updateContent( $value );
		}

		$this->flushTags($particle->id);
		foreach ( $particle->getTags() as $tag){
			$tag_id = $this->getTagId($tag);
			if($tag_id)	$this->addTag($particle->id, $tag_id);
		}

		return false !== $result;
	}

	/**
	 * @param Model\ParticleContent $particleContent
	 *
	 * @return bool
	 */
	public function updateContent( $particleContent ) {
		if ( $particleContent->id ) {
			$result = $this->wpdb->update(
				$this->tableContents,
				array(
					"particle_id" => $particleContent->particle_id,
					"content"     => $particleContent->content,
					"type"        => $particleContent->type,
					"position"    => $particleContent->position,
				),
				array(
					"id" => $particleContent->id,
				),
				array( "%d", "%s", "%s", "%d" ),
				array( "%d" )
			);

			if ( false === $result ) {
				return false;
			}

		} else {
			$result = $this->wpdb->insert(
				$this->tableContents,
				array(
					"particle_id" => $particleContent->particle_id,
					"content"     => $particleContent->content,
					"type"        => $particleContent->type,
					"position"    => $particleContent->position,
				),
				array( "%d", "%s", "%s", "%d" )
			);

			if ( false === $result ) {
				return false;
			}
			$particleContent->id = $this->wpdb->insert_id;
		}

		return false !== $result;
	}

	/**
	 * @param int $particle_id
	 * @param int $tag_id
	 *
	 * @return false|int
	 */
	public function addTag($particle_id, $tag_id){
		return $this->wpdb->replace(
			$this->tableParticlesToTags,
			array(
				"particle_id" => $particle_id,
				"tag_id" => $tag_id,
			)
		);
	}

	/**
	 * @param int $particle_id
	 *
	 * @return false|int
	 */
	public function deleteParticle($particle_id){
		return $this->wpdb->update(
			$this->tableParticles,
			array(
				"is_deleted" => 1,
				"modified_timestamp" => time(),
			),
			array(
				"id" => $particle_id,
			),
			array("%d","%s"),
			array("%d")
		);
	}

	/**
	 * @param int $particle_id
	 *
	 * @return false|int
	 */
	public function flushContents( $particle_id ) {
		return $this->wpdb->delete(
			$this->tableContents,
			array(
				"particle_id" => $particle_id,
			),
			array( "%d" )
		);
	}

	/**
	 * @param int $particle_id
	 *
	 * @return false|int
	 */
	public function flushTags( $particle_id ) {
		return $this->wpdb->delete(
			$this->tableParticlesToTags,
			array(
				"particle_id" => $particle_id,
			),
			array( "%d" )
		);
	}

	/**
	 * create database tables
	 */
	public function createTables() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$wpdb = $this->wpdb;
		dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableParticles (
			 id bigint(20) unsigned not null auto_increment,
			 post_id bigint(20) unsigned not null,
			 author_id bigint(20) unsigned not null,
			 modified_timestamp int(11) NOT NULL,
			 created_timestamp int(11) NOT NULL,
			 is_deleted boolean NOT NULL DEFAULT false,
			 primary key (id),
			 key (post_id),
			 key (author_id),
			 key author_particles (author_id, post_id),
			 key (modified_timestamp),
			 key (created_timestamp),
			 key (is_deleted),
			 CONSTRAINT `particle_to_post` FOREIGN KEY (`post_id`) REFERENCES `$wpdb->posts` (`ID`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableContents (
			 id bigint(20) unsigned not null auto_increment,
			 particle_id bigint(20) unsigned not null,
			 content TEXT not null,
			 type VARCHAR(40) not null,
			 position INT(2) not null,
			 primary key (id),
			 key (particle_id),
			 key (type),
			 key (position),
			 CONSTRAINT `content_of_particle` FOREIGN KEY (`particle_id`) REFERENCES `$this->tableParticles` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableTags (
			 id bigint(20) unsigned not null auto_increment,
			 tag VARCHAR(190) not null,
			 primary key (id),
			 unique key (tag)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableParticlesToTags (
			 id bigint(20) unsigned not null auto_increment,
			 particle_id bigint(20) unsigned not null,
			 tag_id bigint(20) unsigned not null,
			 primary key (id),
			 key (particle_id),
			 key (tag_id),
			 unique particle_tag (particle_id, tag_id),
			 CONSTRAINT `to_particle` FOREIGN KEY (`particle_id`) REFERENCES `$this->tableParticles` (`id`) ON DELETE CASCADE,
			 CONSTRAINT `to_tag` FOREIGN KEY (`tag_id`) REFERENCES `$this->tableTags` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}

}