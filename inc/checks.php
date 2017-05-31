<?php
/**
 * Theme Sniffers
 *
 * @package Theme_Sniffer
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
function theme_sniffer_do_sniff( $theme_slug, $args = array(), $file ) {

	require_once THEME_SNIFFER_DIR . '/vendor/autoload.php';

	$defaults = array(
		'show_warnings'       => true,
		'raw_output'          => 0,
		'minimum_php_version' => '5.2',
		'standard'            => array(),
		'text_domains'        => array( $theme_slug ),
	);

	$args = wp_parse_args( $args, $defaults );

	// Set CLI arguments.
	$values['files']       = $file;
	$values['reportWidth'] = '110';

	if ( 0 === absint( $args['raw_output'] ) ) {
		$values['reports']['json'] = null;
	}

	if ( ! empty( $args['standard'] ) ) {
		$values['standard'] = $args['standard'];
	}
	$values['standard'][] = THEME_SNIFFER_DIR . '/bin/phpcs.xml';

	// Set default standard.
	PHP_CodeSniffer::setConfigData( 'default_standard', 'WordPress-Theme', true );

	// Ignoring warnings when generating the exit code.
	PHP_CodeSniffer::setConfigData( 'ignore_warnings_on_exit', true, true );

	// Show only errors?
	PHP_CodeSniffer::setConfigData( 'show_warnings', absint( $args['show_warnings'] ), true );

	// Ignore unrelated files from the check.
	$values['ignored'] = array(
		'.*/node_modules/.*',
	);

	// Set minimum supported PHP version.
	PHP_CodeSniffer::setConfigData( 'testVersion', $args['minimum_php_version'] . '-7.0', true );

	// Set text domains.
	PHP_CodeSniffer::setConfigData( 'text_domain', implode( ',', $args['text_domains'] ), true );

	// Path to WordPress Theme coding standard.
	PHP_CodeSniffer::setConfigData( 'installed_paths', THEME_SNIFFER_DIR . '/vendor/wp-coding-standards/wpcs/', true );

	// Initialise CodeSniffer.
	$phpcs_cli = new PHP_CodeSniffer_CLI();
	$phpcs_cli->checkRequirements();

	ob_start();
	$num_errors = $phpcs_cli->process( $values );
	$raw_output = ob_get_clean();
	$output = '';

	// Sniff theme files.
	if ( 1 === absint( $args['raw_output'] ) ) {
		if ( ! empty( $raw_output ) ) {
			$output = '<pre>' . esc_html( $raw_output ) . '</pre>';
		}
	} else {
		$output = json_decode( $raw_output );
	} // End if().

	return $output;
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
function theme_sniffer_style_headers( $theme_slug, $theme ) {

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
				__( 'The %1$s is not defined in the style.css header.', 'theme-sniffer' ),
				$header
			),
			'severity' => 'error',
		);
	}

	if ( strpos( $theme_slug, 'wordpress' ) || strpos( $theme_slug, 'theme' ) ) {
		$notices[] = array(
			'message'  => __( 'The theme name cannot contain WordPress or Theme.', 'theme-sniffer' ),
			'severity' => 'error',
		);
	}

	if ( preg_match( '|[^\d\.]|', $theme->get( 'Version' ) ) ) {
		$notices[] = array(
			'message' => __( 'Version strings can only contain numeric and period characters (like 1.2).', 'theme-sniffer' ),
			'severity' => 'error',
		);
	}

	// Prevent duplicate URLs.
	$themeuri  = trim( $theme->get( 'ThemeURI' ) , '/\\' );
	$authoruri = trim( $theme->get( 'AuthorURI' ) , '/\\' );
	if ( $themeuri === $authoruri ) {
		$notices[] = array(
			'message'  => __( 'Duplicate theme and author URLs. A theme URL is a page/site that provides details about this specific theme. An author URL is a page/site that provides information about the author of the theme. The theme and author URL are optional.', 'theme-sniffer' ),
			'severity' => 'error',
		);
	}

	if ( $theme_slug === $theme->get( 'Text Domain' ) ) {
		$notices[] = array(
			/* translators: %1$s: Text Domain, %2$s: Theme Slug */
			'message' => sprintf( __( 'The text domain "%1$s" must match the theme slug "%2$s".', 'theme-sniffer' ),
				$theme->get( 'TextDomain' ),
				$theme_slug
			),
			'severity' => 'error',
		);
	}

	$registered_tags    = theme_sniffer_get_theme_tags();
	$tags               = array_map( 'strtolower', $theme->get( 'Tags' ) );
	$tags_count         = array_count_values( $tags );
	$subject_tags_names = array();

	foreach ( $tags as $tag ) {
		if ( $tags_count[ $tag ] > 1 ) {
			$notices[] = array(
				'message' => sprintf(
					__( 'The tag "%s" is being used more than once, please remove the duplicate.', 'theme-sniffer' ),
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
					__( 'Please remove "%s" as it is not a standard tag.', 'theme-sniffer' ),
					$tag
				),
				'severity' => 'error',
			);
			continue;
		}

		if ( 'accessibility-ready' === $tag ) {
			$notices[] = array(
				'message' => __( 'Themes that use the "accessibility-ready" tag will need to undergo an accessibility review.', 'theme-sniffer' ),
				'severity' => 'warning',
			);
		}
	}

	$subject_tags_count = count( $subject_tags_names );
	if ( $subject_tags_count > 3 ) {
		$notices[] = array(
			'message' => sprintf(
				__( 'A maximum of 3 subject tags are allowed. The theme has %1$d subjects tags [%2$s]. Please remove the subject tags, which do not directly apply to the theme.', 'theme-sniffer' ),
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
			<span class="heading-field"><?php printf( esc_html__( 'File: %s', 'theme-sniffer' ), $theme_slug . '/style.css' ); ?></span>
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
