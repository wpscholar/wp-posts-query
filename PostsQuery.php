<?php

namespace wpscholar\WordPress;

/**
 * Class PostsQuery
 *
 * @package wpscholar\WordPress
 */
class PostsQuery implements \Countable, \IteratorAggregate {

	/**
	 * Cache expiration, in seconds
	 *
	 * @var int
	 */
	public $cache_expiration = MINUTE_IN_SECONDS;

	/**
	 * Properties of the WP_Query object to retain in the cache
	 *
	 * @see https://pressjitsu.com/blog/dont-cache-wp_query/
	 *
	 * @var array
	 */
	public $cached_properties = [
		'found_posts',
		'max_num_pages',
		'post_count',
		'posts',
	];

	/**
	 * WP_Query object query_vars to retain in the cache
	 *
	 * @var array
	 */
	public $cached_query_vars = [];

	/**
	 * @var \WP_Query
	 */
	public $query;

	/**
	 * Fetch query results
	 *
	 * @param array|string $args
	 *
	 * @return \WP_Query|\stdClass Returns WP_Query instance or a pseudo WP_Query object
	 */
	public function fetch( $args = [] ) {

		$query_args = wp_parse_args( $args );

		ksort( $query_args, SORT_STRING );

		$cache_key = md5( __METHOD__ . '?' . http_build_query( $query_args, null, '&' ) );

		$query = wp_cache_get( $cache_key );

		if ( ! $query ) {
			$query = new \WP_Query();
			$query->query( $query_args );

			$pseudoQuery = [];
			foreach ( $this->cached_properties as $property ) {
				$pseudoQuery[ $property ] = $query->$property;
			}

			foreach ( $this->cached_query_vars as $queryVar ) {
				$pseudoQuery['query_vars'][ $queryVar ] = $query->get( $queryVar );
			}

			wp_cache_set( $cache_key, new PseudoQuery( $pseudoQuery ), '', $this->cache_expiration );
		}

		$this->query = $query;

		return $query;
	}

	/**
	 * Required method for IteratorAggregate interface.
	 *
	 * @return \Generator
	 */
	public function getIterator() {
		try {
			if ( isset( $this->query, $this->query->posts ) ) {
				foreach ( $this->query->posts as $post ) {
					setup_postdata( $post );
					yield $post;
				}
			}
		} finally { // Once loop is done, or we break out
			wp_reset_postdata();
		}
	}

	/**
	 * Required method for Countable interface.
	 *
	 * @return int
	 */
	public function count() {
		return isset( $this->query, $this->query->posts ) ? count( $this->query->posts ) : 0;
	}

}
