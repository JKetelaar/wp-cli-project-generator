<?php
/**
 * @author JKetelaar
 */

namespace jketelaar\wordpress\pg\data;

class IndexFile implements FileContent {

	/**
	 * @return string
	 */
	public static function getContent() {
		return "<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp/wp-blog-header.php' );";
	}
}