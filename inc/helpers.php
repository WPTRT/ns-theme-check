<?php
/**
 * Helper functions
 *
 * @package NS_Theme_Check
 */

/**
 * Render form.
 *
 * @since 0.1.0
 */
function ns_theme_check_render_form() {

	$standards = ns_theme_check_get_standards();

	$all_themes = wp_get_themes();
	$themes = array();

	if ( ! empty( $all_themes ) ) {
		foreach ( $all_themes as $key => $theme ) {
			$themes[ $key ] = $theme->get( 'Name' );
		}
	}

	if ( empty( $themes ) ) {
		return;
	}

	$current_theme = get_stylesheet();

	if ( ! empty( $_POST['themename'] ) ) {
		$current_theme = $_POST['themename'];
	}

	$minimum_php_version = '5.2';

	if ( ! empty( $_POST['minimum_php_version'] ) ) {
		$minimum_php_version = $_POST['minimum_php_version'];
	}

	$hide_warning = 0;
	if ( isset( $_POST['hide_warning'] ) && 1 === absint( $_POST['hide_warning'] ) ) {
		$hide_warning = 1;
	}

	$raw_output = 0;
	if ( isset( $_POST['raw_output'] ) && 1 === absint( $_POST['raw_output'] ) ) {
		$raw_output = 1;
	}

	$standard_status = wp_list_pluck( $standards, 'default' );

	if ( isset( $_POST['_wp_http_referer'] ) ) {
		foreach ( $standards as $key => $standard ) {
			if ( isset( $_POST[ $key ] ) && 1 === absint( $_POST[ $key ] ) ) {
				$standard_status[ $key ] = 1;
			} else {
				$standard_status[ $key ] = 0;
			}
		}
	}
	?>
	<form action="<?php echo esc_url( admin_url( 'themes.php?page=ns-theme-check' ) ); ?>" method="post" class="frm-theme-check">
		<?php wp_nonce_field( 'ns_theme_check_run', 'ns_theme_check_nonce' ); ?>
		<div class="theme-switcher-wrap">
			<h2><?php esc_html_e( 'Select Theme', 'ns-theme-check' ); ?></h2>
			<label for="themename">
				<select name="themename">
					<?php foreach ( $themes as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_theme, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<input type="submit" value="<?php esc_attr_e( 'GO', 'ns-theme-check' ); ?>" class="button button-secondary" />
		</div><!-- .theme-switcher-wrap -->
		<div class="standards-wrap">
			<h2><?php esc_html_e( 'Select Standard', 'ns-theme-check' ); ?></h2>
			<?php foreach ( $standards as $key => $standard ) : ?>
				<label for="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $standard['description'] ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $standard_status[ $key ], 1 ); ?> />
					<?php echo '<strong>' . esc_html( $standard['label'] ) . '</strong>: ' . esc_html( $standard['description'] ); ?>
				</label><br>
			<?php endforeach; ?>
		</div><!-- .standards-wrap -->
		<div class="options-wrap">
			<h2><?php esc_html_e( 'Options', 'ns-theme-check' ); ?></h2>
			<label for="hide_warning"><input type="checkbox" name="hide_warning" id="hide_warning" value="1" <?php checked( $hide_warning, 1 ); ?> /><?php esc_html_e( 'Hide Warning', 'ns-theme-check' ); ?></label>
			&nbsp;<label for="raw_output"><input type="checkbox" name="raw_output" id="raw_output" value="1" <?php checked( $raw_output, 1 ); ?> /><?php esc_html_e( 'Raw Output', 'ns-theme-check' ); ?></label>&nbsp;
			<?php $php_versions = ns_theme_check_get_php_versions(); ?>
			<label for="minimum_php_version">
				<select name="minimum_php_version">
				<?php foreach ( $php_versions as $version ) : ?>
					<option value="<?php echo esc_attr( $version ); ?>" <?php selected( $minimum_php_version, $version ); ?>><?php echo esc_html( $version ); ?></option>
				<?php endforeach; ?>
				</select>
				<?php esc_html_e( 'Minimum PHP Version', 'ns-theme-check' ); ?>
			</label>
		</div><!-- .options-wrap -->
	</form>
	<?php
}

/**
 * Render sniff results.
 *
 * @since 0.1.0
 */
function ns_theme_check_render_output() {

	$standards = ns_theme_check_get_standards();

	// Bail if empty.
	if ( empty( $_POST['themename'] ) ) {
		return;
	}

	// Verify nonce.
	if ( ! isset( $_POST['ns_theme_check_nonce'] ) || ! wp_verify_nonce( $_POST['ns_theme_check_nonce'], 'ns_theme_check_run' ) ) {
		esc_html_e( 'Error', 'ns-theme-check' );
		return;
	}

	$args = array(
		'show_warnings' => 1,
		'raw_output'    => 0,
	);

	if ( isset( $_POST['hide_warning'] ) && 1 === absint( $_POST['hide_warning'] ) ) {
		$args['show_warnings'] = 0;
	}

	if ( isset( $_POST['raw_output'] ) && 1 === absint( $_POST['raw_output'] ) ) {
		$args['raw_output'] = 1;
	}

	if ( isset( $_POST['minimum_php_version'] ) && ! empty( $_POST['minimum_php_version'] ) ) {
		$args['minimum_php_version'] = esc_html( $_POST['minimum_php_version'] );
	}

	$args['standard'] = array();
	foreach ( $standards as $key => $standard ) {
		if ( isset( $_POST[ $key ] ) && 1 === absint( $_POST[ $key ] ) ) {
			$args['standard'][] = $standard['label'];
		}
	}

	ns_theme_check_do_sniff( esc_html( $_POST['themename'] ), $args );

}

/**
 * Perform sniff check.
 *
 * @since 0.1.0
 *
 * @param string $theme_slug Theme slug.
 * @param array  $args Arguments.
 *
 * @return bool
 */
function ns_theme_check_do_sniff( $theme_slug, $args = array() ) {

	if ( ! file_exists( NS_THEME_CHECK_DIR . '/vendor/autoload.php' ) ) {
		printf( esc_html__( 'It seems you are using GitHub provided zip for the plugin. Visit %1$sInstalling%2$s to find the correct bundled plugin zip.', 'ns-theme-check' ), '<a href="https://github.com/ernilambar/ns-theme-check#installing" target="_blank">', '</a>' );
		return;
	}

	require_once NS_THEME_CHECK_DIR . '/vendor/autoload.php';

	// Path to WordPress Theme coding standard.
	PHP_CodeSniffer::setConfigData( 'installed_paths', NS_THEME_CHECK_DIR . '/vendor/wp-coding-standards/wpcs/', true );
	PHP_CodeSniffer::setConfigData( 'csslint_path', NS_THEME_CHECK_DIR . '/node_modules/csslint/dist/cli.js --errors=errors', true );

	// Set default standard.
	PHP_CodeSniffer::setConfigData( 'default_standard', 'WordPress-Theme', true );

	// Ignoring warnings when generating the exit code.
	PHP_CodeSniffer::setConfigData( 'ignore_warnings_on_exit', true, true );

	// Set text domains.
	$theme = wp_get_theme( $theme_slug );
	$text_domains = array(
		// Current theme text domain.
		$theme_slug,
		// Frameworks.
		'hybrid-core',
	);
	PHP_CodeSniffer::setConfigData( 'text_domain', implode( ',', $text_domains ), true );

	if ( isset( $args['show_warnings'] ) ) {
		PHP_CodeSniffer::setConfigData( 'show_warnings', absint( $args['show_warnings'] ), true );
	}

	$minimum_php = '5.2';
	if ( isset( $args['minimum_php_version'] ) && ! empty( $args['minimum_php_version'] ) ) {
		$minimum_php = $args['minimum_php_version'];
	}

	PHP_CodeSniffer::setConfigData( 'testVersion', $minimum_php . '-7.0', true );

	// Initialise CodeSniffer.
	$phpcs_cli = new PHP_CodeSniffer_CLI();
	$phpcs_cli->checkRequirements();

	// Set CLI arguments.
	$values['files']       = get_theme_root() . '/' . $theme_slug;
	$values['reportWidth'] = '110';

	if ( isset( $args['raw_output'] ) && 0 === absint( $args['raw_output'] ) ) {
		$values['reports']['json'] = null;
	}

	if ( isset( $args['standard'] ) && ! empty( $args['standard'] ) ) {
		$values['standard'] = $args['standard'];
	}

	$values['standard'][] = NS_THEME_CHECK_DIR . '/bin/phpcs.xml';

	// Ignore unrelated files from the check.
	$values['ignored'] = array(
		'.*/node_modules/.*',
	);

	// Sniff theme files.
	if ( isset( $args['raw_output'] ) && 1 === absint( $args['raw_output'] ) ) {
		echo '<div class="theme-check-report theme-check-report-raw">';
		ns_theme_check_show_repot_info();
		echo '<pre>';
		$num_errors = $phpcs_cli->process( $values );
		echo '</pre></div>';
	} else {
		ob_start();
		$num_errors = $phpcs_cli->process( $values );
		$raw_output = ob_get_clean();
		$output = json_decode( $raw_output );
		if ( ! empty( $output ) ) {
			ns_theme_check_render_json_report( $output );
		}
	}

	// Has the theme passed?
	if ( $num_errors === 0 ) {
		return true;
	} else {
		return false;
	}

}

/**
 * Render JSON data in cleaner format.
 *
 * @since 0.1.0
 *
 * @param string $json JSON data.
 */
function ns_theme_check_render_json_report( $json ) {
	?>
	<div class="theme-check-report theme-check-report-json">
		<?php ns_theme_check_show_repot_info(); ?>

		<div class="summary">
			<h2><?php esc_html_e( 'Summary', 'ns-theme-check' ); ?></h2>
			<div class="summary-content">
				<strong><?php esc_html_e( 'Errors:', 'ns-theme-check' ); ?>&nbsp;<?php echo absint( $json->totals->errors ); ?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php esc_html_e( 'Warnings:', 'ns-theme-check' ); ?>&nbsp;<?php echo absint( $json->totals->warnings ); ?></strong>
			</div><!-- .summary-content -->
		</div><!-- .summary -->
		<hr />

		<?php
		$files = '';
		if ( isset( $json->files ) ) {
			$files = $json->files;
		}
		?>

		<?php if ( $files ) : ?>

			<div class="report-files">
				<h4><?php esc_html_e( 'Details', 'ns-theme-check' ); ?></h4>
				<?php foreach ( $files as $file_key => $file ) : ?>

					<?php
					$errors   = $file->errors;
					$warnings = $file->warnings;

					if ( 0 === absint( $errors ) && 0 === absint( $warnings ) ) {
						continue;
					}
					?>

					<div class="report-file-item">
						<div class="report-file-heading">
							<span class="heading-field"><?php esc_html_e( 'File:', 'ns-theme-check' ); ?></span>
							<?php
								$exploded_file_path = explode( '/themes/', $file_key );
								$file_name = array_pop( $exploded_file_path );
							?>
							<?php echo esc_html( $file_name ); ?>

						</div><!-- .report-file-heading -->
						<div class="report-file-heading-meta">
							<?php echo sprintf( esc_html__( '%1$d errors and %2$d warnings' ), absint( $errors ), absint( $warnings ) ); ?>
						</div><!-- .report-file-heading-meta -->

						<?php if ( ! empty( $file->messages ) && is_array( $file->messages ) ) : ?>

							<table class="report-table">
								<?php foreach ( $file->messages as $item ) : ?>
									<?php $row_class = ( 'error' === strtolower( $item->type ) ) ? 'error' : 'warning'; ?>
									<tr class="item-type-<?php echo esc_attr( $row_class ); ?>">
										<td class="td-line"><?php printf( esc_html__( 'Line: %d', 'ns-theme-check' ), absint( $item->line ) ); ?></td>
										<td class="td-type"><?php echo esc_html( $item->type ); ?></td>
										<td class="td-message"><?php echo esc_html( $item->message ); ?></td>
									</tr>
								<?php endforeach; ?>
							</table>

						<?php endif; ?>

					</div><!-- .report-file-item -->

				<?php endforeach; ?>
			</div><!-- .report-files -->

		<?php endif; ?>

	</div><!-- .theme-check-report .theme-check-report-json -->
	<?php
}

/**
 * Show info about report.
 *
 * @since 0.1.2
 */
function ns_theme_check_show_repot_info() {
	?>
	<p><strong><?php esc_html_e( 'Note: Errors need to be fixed and Warnings are things that need to be checked manually.', 'ns-theme-check' ); ?></strong></p>
	<hr />
	<?php
}

/**
 * Returns standards.
 *
 * @since 0.1.3
 *
 * @return array Standards details.
 */
function ns_theme_check_get_standards() {

	$output = array(
		'wordpress-theme' => array(
			'label'       => 'WordPress-Theme',
			'description' => 'Ruleset for WordPress theme review requirements (Required)',
			'default'     => 1,
		),
		'wordpress-core' => array(
			'label'       => 'WordPress-Core',
			'description' => 'Main ruleset for WordPress core coding standards (Optional)',
			'default'     => 0,
		),
		'wordpress-extra' => array(
			'label'       => 'WordPress-Extra',
			'description' => 'Extended ruleset for recommended best practices (Optional)',
			'default'     => 0,
		),
		'wordpress-docs' => array(
			'label'       => 'WordPress-Docs',
			'description' => 'Additional ruleset for WordPress inline documentation standards (Optional)',
			'default'     => 0,
		),
		'wordpress-vip' => array(
			'label'       => 'WordPress-VIP',
			'description' => 'Extended ruleset for WordPress VIP coding requirements (Optional)',
			'default'     => 0,
		),
	);

	return $output;

}
/**
 * Returns PHP versions.
 *
 * @since 0.1.3
 *
 * @return array PHP versions.
 */
function ns_theme_check_get_php_versions() {

	$output = array(
		'5.2',
		'5.3',
		'5.4',
		'5.5',
		'5.6',
		'7.0',
	);

	return $output;

}
