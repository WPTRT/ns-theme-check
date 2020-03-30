<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package Theme_Sniffer\Views;
 *
 * @since 1.0.0 Moved to a separate file
 */

namespace Theme_Sniffer\Views;

// Check for errors.
if ( ! empty( $this->error ) ) {
	?>
	<div class="notice error">
		<p><?php echo esc_html( $this->error ); ?></p>
	</div>
	<?php
	return;
}

// Use attributes passed from the page creation class.
$themes = $this->themes;

if ( empty( $themes ) ) {
	return;
}

$standards     = $this->standards;
$php_versions  = $this->php_versions;
$nonce         = $this->nonce_field;
$current_theme = $this->current_theme;

// Predefined values.
$minimum_php_version = $this->minimum_php_version;
$standard_status     = $this->standard_status;

// Defaults.
$hide_warning       = 0;
$raw_output         = 0;
$ignore_annotations = 0;
$check_php_only     = 0;
?>

<div class="wrap theme-sniffer">
	<h1 class="theme-sniffer__title"><?php esc_html_e( 'Theme Sniffer', 'theme-sniffer' ); ?></h1>
	<hr />
	<div class="theme-sniffer__form">
		<div class="theme-sniffer__form-theme-switcher">
			<label class="theme-sniffer__form-label" for="themename">
				<h2><?php esc_html_e( 'Select Theme', 'theme-sniffer' ); ?></h2>
			</label>
			<select id="themename" name="themename" class="theme-sniffer__form-select theme-sniffer__form-select--spaced" tabindex="1">
			<?php foreach ( $themes as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_theme, $key ); ?>><?php echo esc_html( $value ); ?></option>
			<?php endforeach; ?>
			</select>
			<span class="theme-sniffer__form-button theme-sniffer__form-button--primary js-start-check" tabindex="8"><?php esc_attr_e( 'Go', 'theme-sniffer' ); ?></span>
			<span class="theme-sniffer__form-button theme-sniffer__form-button--secondary js-stop-check" tabindex="9"><?php esc_attr_e( 'Stop', 'theme-sniffer' ); ?></span>
		</div>
		<div class="theme-sniffer__form-theme-prefix">
			<label for="theme_prefixes">
				<h2><?php esc_html_e( 'Theme prefixes', 'theme-sniffer' ); ?></h2>
			</label>
			<input id="theme_prefixes" class="theme-sniffer__form-input" type="text" name="theme_prefixes" value="" tabindex="2" />
			<div class="theme-sniffer__form-description"><?php esc_html_e( 'Add the theme prefixes to check if all the globals are properly prefixed. Can be just one, or multiple prefixes, separated by comma - e.g. twentyseventeen,twentysixteen,myprefix', 'theme-sniffer' ); ?></div>
		</div>
		<div class="theme-sniffer__form-standards">
			<h2><?php esc_html_e( 'Select Standard', 'theme-sniffer' ); ?></h2>
		<?php foreach ( $standards as $key => $standard ) : ?>
				<label for="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $standard['description'] ); ?>">
					<input type="checkbox" class="theme-sniffer__form-checkbox" name="selected_ruleset[]" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $standard_status[ $key ], 1 ); ?> tabindex="3" />
			<?php echo '<strong>' , esc_html( $standard['label'] ) , '</strong>: ' , esc_html( $standard['description'] ); ?>
				</label><br>
		<?php endforeach; ?>
		</div>
		<div class="theme-sniffer__form-options">
			<h2><?php esc_html_e( 'Options', 'theme-sniffer' ); ?></h2>
			<label for="hide_warning"><input type="checkbox" class="theme-sniffer__form-checkbox" name="hide_warning" id="hide_warning" value="1" <?php checked( $hide_warning, 1 ); ?> tabindex="4"/><?php esc_html_e( 'Hide Warnings', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="raw_output"><input type="checkbox" class="theme-sniffer__form-checkbox" name="raw_output" id="raw_output" value="1" <?php checked( $raw_output, 1 ); ?> tabindex="5"/><?php esc_html_e( 'Raw Output', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="ignore_annotations"><input type="checkbox" class="theme-sniffer__form-checkbox" name="ignore_annotations" id="ignore_annotations" value="1" <?php checked( $ignore_annotations, 1 ); ?> tabindex="6"/><?php esc_html_e( 'Ignore annotations', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="minimum_php_version">
				<select name="minimum_php_version" id="minimum_php_version" class="theme-sniffer__form-select" tabindex="7">
					<?php foreach ( $php_versions as $version ) : ?>
					<option value="<?php echo esc_attr( $version ); ?>" <?php selected( $minimum_php_version, $version ); ?>><?php echo esc_html( $version ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php esc_html_e( 'Minimum PHP Version', 'theme-sniffer' ); ?>
			</label>
		</div>
	</div>
	<?php echo $this->render_partial( 'views/partials/report-notice' ); // phpcs:ignore ?>
	<div class="theme-sniffer__loader js-loader"></div>
	<div class="theme-sniffer__info js-sniffer-info"></div>
	<div class="theme-sniffer__check-done-notice js-check-done"><?php esc_html_e( 'All done!', 'theme-sniffer' ); ?></div>
	<?php echo $nonce; // phpcs:ignore ?>
</div>
