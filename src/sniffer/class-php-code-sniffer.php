<?php
/**
 * Sniffer class file
 *
 * @since 1.2.0
 *
 * @package Theme_Sniffer\Sniffer
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffer;

use PHP_CodeSniffer\Exceptions\DeepExitException;
use \PHP_CodeSniffer\Runner;
use \PHP_CodeSniffer\Config;
use \PHP_CodeSniffer\Reporter;
use \PHP_CodeSniffer\Ruleset;
use \PHP_CodeSniffer\Files\DummyFile;
use \WordPressCS\WordPress\PHPCSHelper;

use Theme_Sniffer\Helpers\Sniffer_Helpers;


/**
 * Class for the sniffer instance implementation
 *
 * This class is the Sniffer implementation where we are using PHPCS
 */
class Php_Code_Sniffer implements Sniffer {

	use Sniffer_Helpers;

	/**
	 * The default_standard config key
	 *
	 * @var string
	 */
	const DEFAULT_STANDARD = 'default_standard';

	/**
	 * The ignore_warnings_on_exit config key
	 *
	 * @var string
	 */
	const IGNORE_WARNINGS_ON_EXIT = 'ignore_warnings_on_exit';

	/**
	 * The testVersion config key
	 *
	 * @var string
	 */
	const TEST_VERSION = 'testVersion';

	/**
	 * The text_domain config key
	 *
	 * @var string
	 */
	const TEXT_DOMAIN = 'text_domain';

	/**
	 * The prefixes config key
	 *
	 * @var string
	 */
	const PREFIXES = 'prefixes';

	/**
	 * The text_domains argument key
	 *
	 * @var string
	 */
	const TEXT_DOMAINS = 'text_domains';

	/**
	 * Init sniffer runner
	 *
	 * @return Runner|void
	 */
	public function init_runner() {
		return new Runner();
	}

	/**
	 * Init sniffer config
	 *
	 * @param array $config Configuration array.
	 * @return mixed|Config
	 */
	public function init_config( $config ) {
		return new Config( $config );
	}

	/**
	 * Init sniffer reporter
	 *
	 * @param Config $config Configuration array.
	 * @return mixed|Reporter
	 */
	public function init_reporter( $config ) {
		return new Reporter( $config );
	}

	/**
	 * Method that returns the results based on a custom PHPCS Runner
	 *
	 * @param array ...$arguments Array of passed arguments.
	 * @return string              Sniff results string.
	 *
	 * @throws DeepExitException Exception thrown in the case of error.
	 */
	public function get_sniff_results( ...$arguments ): string {
		// Unpack the arguments.
		$show_warnings       = $arguments[0]['show_warnings'] ?? '';
		$minimum_php_version = $arguments[0]['minimum_php_version'] ?? '';
		$args                = $arguments[0]['args'] ?? '';
		$theme_prefixes      = $arguments[0]['theme_prefixes'] ?? '';
		$all_files           = $arguments[0]['all_files'] ?? '';
		$standards_array     = $arguments[0]['standards_array'] ?? '';
		$ignore_annotations  = $arguments[0]['ignore_annotations'] ?? '';
		$ignored             = $arguments[0]['ignored'] ?? '';
		$raw_output          = $arguments[0]['raw_output'] ?? '';

		// Create a custom runner.
		$runner = $this->init_runner();

		$config_args = [ '-s', '-p' ];

		if ( $show_warnings === '0' ) {
			$config_args[] = '-n';
		}

		$runner->config = $this->init_config( $config_args );

		$all_files = array_values( $all_files );

		$runner->config->extensions = [
			'php' => 'PHP',
			'inc' => 'PHP',
		];

		$runner->config->standards   = $standards_array;
		$runner->config->files       = $all_files;
		$runner->config->annotations = $ignore_annotations;
		$runner->config->parallel    = 10;
		$runner->config->colors      = false;
		$runner->config->tabWidth    = 0;
		$runner->config->reportWidth = 110;
		$runner->config->interactive = false;
		$runner->config->cache       = true;
		$runner->config->ignored     = $ignored;

		if ( ! $raw_output ) {
			$runner->config->reports = [ 'json' => null ];
		}

		$runner->init();

		// Set default standard.
		PHPCSHelper::set_config_data( self::DEFAULT_STANDARD, $this->get_default_standard(), true );

		// Ignoring warnings when generating the exit code.
		PHPCSHelper::set_config_data( self::IGNORE_WARNINGS_ON_EXIT, true, true );

		// Set minimum supported PHP version.
		PHPCSHelper::set_config_data( self::TEST_VERSION, $minimum_php_version . '-', true );

		// Set text domains.
		PHPCSHelper::set_config_data( self::TEXT_DOMAIN, implode( ',', $args[ self::TEXT_DOMAINS ] ), true );

		if ( $theme_prefixes !== '' ) {
			// Set prefix.
			PHPCSHelper::set_config_data( self::PREFIXES, $theme_prefixes, true );
		}

		$runner->reporter = $this->init_reporter( $runner->config );

		foreach ( $all_files as $file_path ) {
			$file       = $this->get_dummy_file( file_get_contents( $file_path ), $runner->ruleset, $runner->config );
			$file->path = $file_path;

			$runner->processFile( $file );
		}

		ob_start();
		$runner->reporter->printReports();
		$report = ob_get_clean();

		return $report;
	}

	/**
	 * A dummy file represents a chunk of text that does not have a file system location.
	 *
	 * @param string                   $file_content The content of the file.
	 * @param Ruleset $ruleset      The ruleset used for the run.
	 * @param Config  $config       The config data for the run.
	 *
	 * @return DummyFile
	 */
	private function get_dummy_file( string $file_content, Ruleset $ruleset, Config $config ) {
		return new DummyFile( $file_content, $ruleset, $config );
	}
}
