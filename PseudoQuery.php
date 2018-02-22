<?php

namespace wpscholar\WordPress;

/**
 * Class PseudoQuery
 * @package wpscholar\WordPress
 */
class PseudoQuery {

	/**
	 * @var array Cached query vars, can be fetched via the get() method.
	 */
	public $query_vars = [];

	public function __construct( array $values ) {
		foreach ( $values as $property => $value ) {
			$this->{$property} = $value;
		}
	}

	/**
	 * Retrieve cached query variable.
	 *
	 * Literally copied from \WP_Query.
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed  $default   Optional. Value to return if the query variable is not set. Default empty.
	 * @return mixed Contents of the query variable.
	 */
	public function get( $query_var, $default = '' ) {
		if ( isset( $this->query_vars[ $query_var ] ) ) {
			return $this->query_vars[ $query_var ];
		}

		return $default;
	}
}