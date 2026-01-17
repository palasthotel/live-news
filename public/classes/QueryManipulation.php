<?php

namespace Palasthotel\WordPress\LiveNews;

/**
 * Class QueryManipulation
 *
 * @package AdditionalAuthors
 */
class QueryManipulation {

    public Plugin $plugin;

	/**
	 * Query constructor.
	 */
	function __construct( Plugin $plugin ) {

		$this->plugin = $plugin;

		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
		add_filter( 'get_usernumposts', array( $this, 'change_num_posts' ), 10, 4 );

		// WP_User query
		add_action('pre_user_query', array($this, 'pre_user_query'));
	}

	/**
	 * @param \WP_Query $wp_query
	 *
	 * @return bool|int
	 */
	private function getAuthorId($wp_query){

		// at the moment we are only compatible with an single author id
		$author_id = $wp_query->get('author');
		if( (is_int($author_id) && $author_id > 0) || (is_string($author_id) && $author_id != "" && intval($author_id)."" === $author_id)){
			return intval($author_id);
		}
		return false;
	}

	/**
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	private function isManipulationNeeded($wp_query){
		$author_id = $this->getAuthorId($wp_query);
		return $author_id !== false;

	}

	/**
	 * WHERE statement
	 *
	 * @param  string $where The WHERE clause of the query.
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return string $where
	 */
	function posts_where( $where, $wp_query ) {

		if ( !$this->isManipulationNeeded($wp_query) ) {
			return $where;
		}

		$author_id = $this->getAuthorId($wp_query);

		global $wpdb;
		$table = $this->plugin->database->tableParticles;

		$where = str_replace(
			"{$wpdb->posts}.post_author IN ({$author_id})",
			"( {$wpdb->posts}.post_author IN ({$author_id}) OR {$wpdb->posts}.ID IN (SELECT post_id FROM $table WHERE author_id IN ({$author_id})) )",
			$where
		);

		$where = str_replace(
			"{$wpdb->posts}.post_author = {$author_id}",
			"{$wpdb->posts}.ID IN (SELECT post_id FROM $table WHERE author_id = $author_id) OR {$wpdb->posts}.post_author = {$author_id}",
			$where
		);

		return $where;
	}

	function change_num_posts( $count, $userid, $post_type, $public_only ) {

		global $wpdb;
		$table = $this->plugin->database->tableParticles;

		$select = "SELECT count(*) FROM $wpdb->posts LEFT JOIN $table ON ($table.post_id = {$wpdb->posts}.ID and $table.is_deleted = 0) ";
		$where = "WHERE author_id = $userid";
		if(is_array($post_type)){
			if(count($post_type) > 0 && !in_array("any", $post_type)){

				$values = array_filter($post_type, function($type){
					return post_type_exists($type);
				});
				$values = implode(", ", array_map(function($type){
					return "'$type'";
				}, $values));

				$where.= " AND post_type IN ($values)";
			}
		} else if($post_type != "any") {
			$where.= " AND post_type = '$post_type'";
		}

		$additional_count = $wpdb->get_var( $select.$where );

		return (int)$count + (int)$additional_count;
	}

	/**
	 * @param \WP_User_Query $query
	 */
	function pre_user_query($query){
		global $wpdb;
		if(
			$query->get("has_published_posts") === true
		) {
			$start_string = "AND $wpdb->users.ID IN ( ";
			
			$inject_where_additional_authors = " $wpdb->users.ID IN ( SELECT author_id FROM ".$this->plugin->database->tableParticles." WHERE is_deleted = 0 ) ";

			$new_start    = "AND ( $inject_where_additional_authors OR $wpdb->users.ID IN ( ";
			$end_string   = ") )";

			$where = $query->query_where;
			$start = strpos( $where, $start_string );
			$end   = strpos( $where, $end_string, $start );

			$where = substr_replace( $where, ") ", $end, 0 );

			$where = str_replace( $start_string, $new_start, $where );

			$query->query_where = $where;
		}
	}
}
