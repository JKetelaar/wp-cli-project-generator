<?php
/**
 * @author JKetelaar
 */

namespace jketelaar\wordpress\pg\data;

interface FileContent {
	/**
	 * @return string
	 */
	public static function getContent();
}