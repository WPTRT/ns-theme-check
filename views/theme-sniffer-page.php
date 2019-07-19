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
$standards    = $this->standards;
$themes       = $this->themes;
$php_versions = $this->php_versions;
$nonce        = $this->nonce_field;

if ( empty( $themes ) ) {
	return;
}

// Predefined values.
$current_theme       = get_stylesheet();
$minimum_php_version = $this->minimum_php_version;
$hide_warning        = 0;
$raw_output          = 0;
$ignore_annotations  = 0;
$check_php_only      = 0;
$standard_status     = wp_list_pluck( $standards, 'default' );
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
					<input type="checkbox" name="selected_ruleset[]" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $standard_status[ $key ], 1 ); ?> tabindex="3" />
			<?php echo '<strong>' , esc_html( $standard['label'] ) , '</strong>: ' , esc_html( $standard['description'] ); ?>
				</label><br>
		<?php endforeach; ?>
		</div>
		<div class="theme-sniffer__form-options">
			<h2><?php esc_html_e( 'Options', 'theme-sniffer' ); ?></h2>
			<label for="hide_warning"><input type="checkbox" name="hide_warning" id="hide_warning" value="1" <?php checked( $hide_warning, 1 ); ?> tabindex="4"/><?php esc_html_e( 'Hide Warnings', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="raw_output"><input type="checkbox" name="raw_output" id="raw_output" value="1" <?php checked( $raw_output, 1 ); ?> tabindex="5"/><?php esc_html_e( 'Raw Output', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="ignore_annotations"><input type="checkbox" name="ignore_annotations" id="ignore_annotations" value="1" <?php checked( $ignore_annotations, 1 ); ?> tabindex="6"/><?php esc_html_e( 'Ignore annotations', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="check_php_only"><input type="checkbox" name="check_php_only" id="check_php_only" value="1" <?php checked( $check_php_only, 1 ); ?> tabindex="6"/><?php esc_html_e( 'Check only PHP files', 'theme-sniffer' ); ?></label>&nbsp;&nbsp;
			<label for="minimum_php_version">
				<select name="minimum_php_version" id="minimum_php_version" tabindex="7">
					<?php foreach ( $php_versions as $version ) : ?>
					<option value="<?php echo esc_attr( $version ); ?>" <?php selected( $minimum_php_version, $version ); ?>><?php echo esc_html( $version ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php esc_html_e( 'Minimum PHP Version', 'theme-sniffer' ); ?>
			</label>
		</div>
	</div>
	<div class="theme-sniffer__start-notice js-start-notice"></div>
	<div class="theme-sniffer__report js-sniff-report">
		<div class="theme-sniffer__report-item js-report-item">
			<div class="theme-sniffer__report-heading js-report-item-heading"></div>
			<table class="theme-sniffer__report-table js-report-table">
				<tr class="theme-sniffer__report-table-row js-report-notice-type">
					<td class="theme-sniffer__report-table-line js-report-item-line"></td>
					<td class="theme-sniffer__report-table-type js-report-item-type"></td>
					<td class="theme-sniffer__report-table-message js-report-item-message"></td>
				</tr>
				<tr class="theme-sniffer__report-table-row js-report-notice-source">
					<td class="theme-sniffer__report-table-empty"></td>
					<td class="theme-sniffer__report-table-source js-report-item-source">
						<span class="tooltipped tooltipped-w tooltipped-no-delay" aria-label="<?php esc_attr_e( 'Copy Annotation', 'theme-sniffer' ); ?>">
							<button class="theme-sniffer__report-copy-annotation-btn">
								<span class="dashicons dashicons-clipboard"></span><span class= "theme-sniffer__report-copy-annotation-source"></span>
							</button>
						</span>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="theme-sniffer__loader js-loader"></div>
	<div class="theme-sniffer__info js-sniffer-info"></div>
	<div class="theme-sniffer__check-done-notice js-check-done"><?php esc_html_e( 'All done!', 'theme-sniffer' ); ?></div>
	<?php echo $nonce; // phpcs:ignore ?>
</div>
