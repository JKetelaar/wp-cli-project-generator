<?php
/**
 * @author JKetelaar
 */

require_once('vendor/autoload.php');

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

new \jketelaar\wordpress\pg\Core();