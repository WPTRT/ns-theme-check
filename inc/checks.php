<?php
/**
 * Theme Checks
 *
 * @package NS_Theme_Check
 */

/**
 * Perform sniff check.
 *
 * @since 0.1.0
 *
 * @param string $theme_slug Theme slug.
 * @param array  $args Arguments.
 * @param string $file Path of the file to sniff.
 *
 * @return bool
 */
function ns_theme_check_do_sniff( $theme_slug, $args = array(), $file ) {

	if ( ! file_exists( NS_THEME_CHECK_DIR . '/vendor/autoload.php' ) ) {
		printf( esc_html__( 'It seems you are using GitHub provided zip for the plugin. Visit %1$sInstalling%2$s to find the correct bundled plugin zip.', 'ns-theme-check' ), '<a href="https://github.com/ernilambar/ns-theme-check#installing" target="_blank">', '</a>' );
		return;
	}

	require_once NS_THEME_CHECK_DIR . '/vendor/autoload.php';

	$defaults = array(
		'show_warnings'       => false,
		'raw_output'          => 0,
		'minimum_php_version' => '5.2',
		'standard'            => array(),
		'text_domains'        => array( $theme_slug ),
	);

	$args = wp_parse_args( $args, $defaults );

	// Set CLI arguments.
	$cli_args  = $file;
	$cli_args .= ' --report-width=110';

	if ( 0 === absint( $args['raw_output'] ) ) {
		$cli_args .= ' --report=json';
	}

	$cli_args .= ' --standard=' . NS_THEME_CHECK_DIR . '/bin/phpcs.xml';
	if ( ! empty( $args['standard'] ) ) {
		$cli_args .= ',' . implode( ',', $args['standard'] );
	}

	// Set default standard.
	$cli_args .= ' --runtime-set default_standard WordPress-Theme';

	// Ignoring warnings when generating the exit code.
	$cli_args .= ' --runtime-set ignore_warnings_on_exit 1';

	// Show only errors?
	if ( $args['show_warnings'] ) {
		$cli_args .= ' -n';
	}

	// Ignore unrelated files from the check.
	$cli_args .= ' --ignore=.*/node_modules/.*';

	// Set minimum supported PHP version.
	$cli_args .= ' --runtime-set testVersion ' . $args['minimum_php_version'] . '-7.0';

	// Set text domains.
	$cli_args .= ' --runtime-set text_domain ' . implode( ',', $args['text_domains'] );

	// Path to WordPress Theme coding standards.
	$cli_args .= ' --runtime-set installed_paths ' . NS_THEME_CHECK_DIR . '/vendor/wp-coding-standards/wpcs/';

	$command = escapeshellcmd( NS_THEME_CHECK_DIR . '/vendor/bin/phpcs ' . $cli_args );

	exec( $command, $raw_output, $return_var );

	// Sniff theme files.
	if ( 1 === absint( $args['raw_output'] ) ) {
		if ( ! empty( $raw_output ) ) {
			$output = implode( "\n", $raw_output );
			echo '<pre>' . esc_html( $output ) . '</pre>';
		}
	} else {
		if ( empty( $raw_output ) ) {
			$output = (object) array(
				'totals' => (object) array(
					'errors'   => 0,
					'warnings' => 0,
					'fixable'  => 0,

				),
				'files' => (object) array(
					$file => (object) array(
						'errors' => 1,
						'warnings' => 0,
						'messages' => array(
							0 => (object) array(
								'message'  => esc_html__('The check has failed. This could be due to running out of memeory. Either reduce the file length or increase PHP memory.', 'ns-theme-check'),
								'source'   => 'Internal.Failed',
								'severity' => 5,
								'type'     => 'error',
								'line'     => 1,
								'column'   => 1,
								'fixable'  => false,
							),
						),
					),
				),
			);
		} else {
			$output = json_decode( $raw_output[0] );
		}

		if ( ! empty( $output ) ) {
			return ns_theme_check_render_json_report( $output );
		}
	} // End if().

}

/**
 * Perform style.css header check.
 *
 * @since 0.3.0
 *
 * @param string $theme_slug Theme slug.
 * @param array  $theme WP_Theme Theme object.
 *
 * @return bool
 */
function ns_theme_check_style_headers( $theme_slug, $theme ) {

	$pass             = true;
	$required_headers = array(
		'Name',
		'Description',
		'Author',
		'Version',
		'License',
		'License URI',
		'TextDomain',
	);

	foreach ( $required_headers as $header ) {
		if ( $theme->get( $header ) ) {
			continue;
		}
		$notices[] = array(
			/* translators: 1: comment header line, 2: style.css */
			'message'  => sprintf(
				__( 'The %1$s is not defined in the style.css header.', 'ns-theme-check' ),
				$header
			),
			'severity' => 'error',
		);
	}

	if ( strpos( $theme_slug, 'wordpress' ) || strpos( $theme_slug, 'theme' ) ) {
		$notices[] = array(
			'message'  => __( 'The theme name cannot contain WordPress or Theme.', 'ns-theme-check' ),
			'severity' => 'error',
		);
	}

	if ( preg_match( '|[^\d\.]|', $theme->get( 'Version' ) ) ) {
		$notices[] = array(
			'message' => __( 'Version strings can only contain numeric and period characters (like 1.2).', 'ns-theme-check' ),
			'severity' => 'error',
		);
	}

	// Prevent duplicate URLs.
	$themeuri  = trim( $theme->get( 'ThemeURI' ) , '/\\' );
	$authoruri = trim( $theme->get( 'AuthorURI' ) , '/\\' );
	if ( $themeuri === $authoruri ) {
		$notices[] = array(
			'message'  => __( 'Duplicate theme and author URLs. A theme URL is a page/site that provides details about this specific theme. An author URL is a page/site that provides information about the author of the theme. The theme and author URL are optional.', 'ns-theme-check' ),
			'severity' => 'error',
		);
	}

	if ( $theme_slug === $theme->get( 'Text Domain' ) ) {
		$notices[] = array(
			/* translators: %1$s: Text Domain, %2$s: Theme Slug */
			'message' => sprintf( __( 'The text domain "%1$s" must match the theme slug "%2$s".', 'ns-theme-check' ),
				$theme->get( 'TextDomain' ),
				$theme_slug
			),
			'severity' => 'error',
		);
	}

	$registered_tags    = ns_theme_check_get_theme_tags();
	$tags               = array_map( 'strtolower', $theme->get( 'Tags' ) );
	$tags_count         = array_count_values( $tags );
	$subject_tags_names = array();

	foreach ( $tags as $tag ) {
		if ( $tags_count[ $tag ] > 1 ) {
			$notices[] = array(
				'message' => sprintf(
					__( 'The tag "%s" is being used more than once, please remove the duplicate.', 'ns-theme-check' ),
					$tag
				),
				'severity' => 'error',
			);
		}

		if ( isset( $registered_tags['subject_tags'][ $tag ] ) ) {
			$subject_tags_names[] = $tag;
			continue;
		}

		if ( ! isset( $registered_tags['allowed_tags'][ $tag ] ) ) {
			$notices[] = array(
				'message' => sprintf(
					__( 'Please remove "%s" as it is not a standard tag.', 'ns-theme-check' ),
					$tag
				),
				'severity' => 'error',
			);
			continue;
		}

		if ( 'accessibility-ready' === $tag ) {
			$notices[] = array(
				'message' => __( 'Themes that use the "accessibility-ready" tag will need to undergo an accessibility review.', 'ns-theme-check' ),
				'severity' => 'warning',
			);
		}
	}

	$subject_tags_count = count( $subject_tags_names );
	if ( $subject_tags_count > 3 ) {
		$notices[] = array(
			'message' => sprintf(
				__( 'A maximum of 3 subject tags are allowed. The theme has %1$d subjects tags [%2$s]. Please remove the subject tags, which do not directly apply to the theme.', 'ns-theme-check' ),
				$subject_tags_count,
				implode( ',', $subject_tags_names )
			),
			'severity' => 'error',
		);
	}

	if ( empty( $notices ) ) {
		return true;
	}

	?>
	<div class="report-file-item">
		<div class="report-file-heading">
			<span class="heading-field"><?php printf( esc_html__( 'File: %s', 'ns-theme-check' ), $theme_slug . '/style.css' ); ?></span>
		</div><!-- .report-file-heading -->
		<table class="report-table">
		<?php foreach ( $notices as $notice ) : ?>
			<tr class="item-type-<?php echo esc_attr( $notice['severity'] ); ?>">
			<td class="td-type"><?php echo esc_html( $notice['severity'] ); ?></td>
			<td class="td-message"><?php echo esc_html( $notice['message'] ); ?></td>
			</tr>
		<?php endforeach; ?>
		</table>
	</div><!-- .report-file-item -->
	<?php
	return $pass;
}
