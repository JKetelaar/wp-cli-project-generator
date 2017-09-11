<?php
/**
 * @author JKetelaar
 */

namespace jketelaar\wordpress\pg;

use Composer\Console\Application;
use jketelaar\wordpress\pg\data\IndexFile;
use Symfony\Component\Console\Input\ArrayInput;

class Core extends \WP_CLI_Command {

	/**
	 * @var string[]
	 */
	private $args;

	/**
	 * @var string[]
	 */
	private $assoc_args;

	/**
	 * Core constructor.
	 *
	 * @param string[] $args
	 * @param string[] $assoc_args
	 */
	public function __construct( $args = null, $assoc_args = null ) {
		$this->assignArgs( $args, $assoc_args );
	}

	/**
	 * @param string[] $args
	 * @param string[] $assoc_args
	 */
	private function assignArgs( $args, $assoc_args ) {
		$this->args       = $args;
		$this->assoc_args = $assoc_args;

		$this->assoc_args['path'] = ( ( $path = \WP_CLI::get_config( 'path' ) ) !== null ) ? $path : getcwd();
	}

	/**
	 * Generates a WordPress project with Composer
	 *
	 * ## OPTIONS
	 *
	 * [--pathe=<path>]
	 * : Path to the WordPress files.
	 *
	 * ## EXAMPLES
	 *
	 * wp generate-project composer
	 *
	 * @when before_wp_load
	 *
	 * @param string[] $args
	 * @param string[] $assoc_args
	 */
	public function composer( $args, $assoc_args ) {
		$this->assignArgs( $args, $assoc_args );

		if ( $this->getPath() == null ) {
			\WP_CLI::error( 'Directory invalid' );

			return;
		}

		if ( ! file_exists( $this->getPath() ) ) {
			\WP_CLI::confirm( 'Would you like to create the given path (' . $this->getPath() . ')?' );

			mkdir( $this->getPath(), 0777, true );
		}

		$composer = $this->getPath() . 'composer.json';
		if ( file_exists( $composer ) ) {
			\WP_CLI::confirm( 'Would you like to overwrite the existing composer file?' );
		}
		file_put_contents( $this->getPath() . 'composer.json', json_encode( $this->getComposerSetup( 'jketelaar/test' ) ) );

		\WP_CLI::confirm( 'Would you like to execute composer install?' );

		$input = new ArrayInput( [ 'command' => 'install', '--working-dir' => $this->getPath() ] );
		$app   = new Application();
		$app->setAutoExit(false);
		$app->run( $input );

		\WP_CLI::log("Adding index.php");
		file_put_contents($this->getPath() . 'index.php', IndexFile::getContent());

		\WP_CLI::runcommand(sprintf('config create --path=%s/wp --prompt', $this->getPath()));
		rename($this->getPath() . 'wp/wp-config.php', $this->getPath() . 'wp-config.php');

		\WP_CLI::runcommand(sprintf('core install --path=%s/wp --prompt', $this->getPath()));
	}

	/**
	 * @param bool $appendTrailingSlash
	 *
	 * @return string
	 */
	private function getPath( $appendTrailingSlash = true ) {
		$path = $this->assoc_args['path'];
		if ( $appendTrailingSlash && substr( $path, strlen( $path ) - 1 ) !== '/' ) {
			$path .= '/';
		}

		return $path;
	}

	private function getComposerSetup( $name ) {
		return [
			'name'         => $name,
			'type'         => 'project',
			'license'      => 'MIT',
			'authors'      => [
				[
					'name'  => 'Jeroen Ketelaar',
					'email' => 'jeroen@ketelaar.me'
				]
			],
			'repositories' => [
				'jketelaar'         => [
					'type' => 'composer',
					'url'  => 'https://composer.jketelaar.nl'
				],
				'wpackagist-plugin' => [
					'type' => 'composer',
					'url'  => 'https://wpackagist.org'
				]
			],
			'require'      => [
				'php'                                  => '>=5.5',
				'composer/installers'                  => '*',
				'composer/composer'                    => '*',
				'johnpbloch/wordpress'                 => '*',
				'wpackagist-plugin/w3-total-cache'     => '*',
				'wpackagist-plugin/loco-translate'     => '*',
				'wpackagist-plugin/wordfence'          => '*',
				'jketelaar/advanced-custom-fields-pro' => '*'
			],
			'extra'        => [
				'wordpress-install-dir' => 'wp',
				'installer-paths'       => [
					'assets/plugins/{$name}' => [
						'type:wordpress-plugin'
					],
					'assets/mu-plugins/'     => [
						'type:wordpress-muplugin'
					],
					'assets/themes/{$name}'  => [
						'type:wordpress-theme'
					]
				]
			]
		];
	}
}

\WP_CLI::add_command( 'generate-project', __NAMESPACE__ . '\\Core' );
