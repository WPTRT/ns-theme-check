<?php
/**
 * Admin functions
 *
 * @package NS_Theme_Check
 */

/**
 * Add go to theme check page link on plugin page.
 *
 * @since 0.1.3
 *
 * @param array $links Array of plugin action links.
 * @return array Modified array of plugin action links.
 */
function ns_theme_check_plugin_settings_link( $links ) {
	$theme_check_link = '<a href="themes.php?page=ns-theme-check">' . esc_attr__( 'Theme Check Page', 'ns-theme-check' ) . '</a>';
	array_unshift( $links, $theme_check_link );
	return $links;
}
add_filter( 'plugin_action_links_' . NS_THEME_CHECK_BASENAME, 'ns_theme_check_plugin_settings_link' );

/**
 * Register admin menu.
 *
 * @since 0.1.0
 */
function ns_theme_check_admin_menu() {
	add_theme_page(
		esc_html__( 'NS Theme Check', 'ns-theme-check' ),
		esc_html__( 'NS Theme Check', 'ns-theme-check' ),
		'manage_options',
		'ns-theme-check',
		'ns_theme_check_render_admin_page'
	);
}
add_action( 'admin_menu', 'ns_theme_check_admin_menu' );

/**
 * Callback for admin page.
 *
 * @since 0.1.0
 */
function ns_theme_check_render_admin_page() {
	?>
	<div class="wrap ns-theme-check">
		<h1><?php esc_html_e( 'NS Theme Check', 'ns-theme-check' ); ?></h1>
		<hr />
		<?php ns_theme_check_render_form(); ?>
	</div>
	<?php
}

/**
 * Load admin scripts and styles.
 *
 * @since 0.1.2
 *
 * @param string $hook Admin hook name.
 */
function ns_theme_check_admin_scripts( $hook ) {
	if ( 'appearance_page_ns-theme-check' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'ns-theme-check-admin', NS_THEME_CHECK_URL . '/css/admin.css', array(), '0.1.3c' );
	wp_enqueue_script( 'ns-theme-check-admin', NS_THEME_CHECK_URL . '/js/admin.js', array( 'jquery', 'underscore' ), '0.1.3a' );
}
add_action( 'admin_enqueue_scripts', 'ns_theme_check_admin_scripts' );

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
	if ( isset( $_POST['hide_warning'] ) && 'true' === $_POST['hide_warning'] ) {
		$hide_warning = 1;
	}

	$raw_output = 0;
	if ( isset( $_POST['raw_output'] ) && 'true' === $_POST['raw_output'] ) {
		$raw_output = 1;
	}

	$standard_status = wp_list_pluck( $standards, 'default' );

	if ( isset( $_POST['_wp_http_referer'] ) ) {
		foreach ( $standards as $key => $standard ) {
			if ( isset( $_POST[ $key ] ) && 'true' === $_POST[ $key ] ) {
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
			<span id="check-status" class="button button-secondary"><?php esc_attr_e( 'Go', 'ns-theme-check' ); ?></span>
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
			<label for="hide_warning"><input type="checkbox" name="hide_warning" id="hide_warning" value="1" <?php checked( $hide_warning, 1 ); ?> /><?php esc_html_e( 'Hide Warnings', 'ns-theme-check' ); ?></label>
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
	<div class="theme-check-report"></div><!-- .theme-check-report -->
	<?php
}

add_action( 'wp_ajax_ns_theme_check_run', 'ns_theme_check_initialize_sniff' );
/**
 * Start sniffing
 *
 * @since 0.1.0
 */
function ns_theme_check_initialize_sniff() {
	// Bail if empty.
	if ( empty( $_POST['themename'] ) ) {
		return;
	}

	$theme_slug = esc_html( $_POST['themename'] );

	// Verify nonce.
	if ( ! isset( $_POST['ns_theme_check_nonce'] ) || ! wp_verify_nonce( $_POST['ns_theme_check_nonce'], 'ns_theme_check_run' ) ) {
		esc_html_e( 'Error', 'ns-theme-check' );
		return;
	}

	if ( isset( $_POST['hide_warning'] ) && 'true' === $_POST['hide_warning'] ) {
		$args['show_warnings'] = true;
	}

	if ( isset( $_POST['raw_output'] ) && 'true' === $_POST['raw_output'] ) {
		$args['raw_output'] = 1;
	}

	if ( isset( $_POST['minimum_php_version'] ) && ! empty( $_POST['minimum_php_version'] ) ) {
		$args['minimum_php_version'] = esc_html( $_POST['minimum_php_version'] );
	}

	$standards = ns_theme_check_get_standards();
	foreach ( $standards as $key => $standard ) {
		if ( isset( $_POST[ $key ] ) && 'true' === $_POST[ $key ] ) {
			$args['standard'][] = $standard['label'];
		}
	}

	$theme = wp_get_theme( $theme_slug );
	$php_files = $theme->get_files( 'php', 4, false );
	// Current theme text domain.
	$args['text_domains'][] = $theme_slug;
	// Frameworks.
	foreach ( $php_files as $key => $file ) {
		if ( strrpos( $key, 'hybrid.php' ) ) {
			$args['text_domains'][] = 'hybrid-core';
		}
		if ( strrpos( $key, 'kirki.php' ) ) {
			$args['text_domains'][] = 'kirki';
		}
	}

	$all_files = $theme->get_files( array( 'php', 'css,', 'js' ), -1, false );

	wp_die( json_encode( array( $theme_slug, $args, $all_files ) ) );

}

add_action( 'wp_ajax_ns_theme_check_sniff', 'ns_theme_check_individual_files' );
/**
 * Render sniff results.
 *
 * @since 0.1.0
 */
function ns_theme_check_individual_files() {
	// Bail if empty.
	if ( empty( $_POST['theme_name'] ) || empty( $_POST['theme_args'] )  || empty( $_POST['file'] ) ) {
		return;
	}

	// Verify nonce.
	if ( ! isset( $_POST['ns_theme_check_nonce'] ) || ! wp_verify_nonce( $_POST['ns_theme_check_nonce'], 'ns_theme_check_run' ) ) {
		esc_html_e( 'Error', 'ns-theme-check' );
		return;
	}

	$sniff = ns_theme_check_do_sniff( $_POST['theme_name'], $_POST['theme_args'], $_POST['file'] );

	wp_die();

}
