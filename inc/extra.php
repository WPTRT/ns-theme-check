<?php
/**
 * Extra checks
 *
 * @package NS_Theme_Check
 */

/**
 * Perform extra checks.
 *
 * @since 0.1.3
 *
 * @param string $json JSON encoded string
 * @return string Modified string.
 */
function ns_theme_check_extra_checks( $json ) {

	$styles_needed = ns_theme_check_styles_needed();

	if ( ! empty( $styles_needed ) ) {
		// TODO: Logic to add errors
		// $json = ns_theme_check_add_errors( $styles_needed );
	}

	return $json;

}

/**
 * Perform checks related those needed in style.
 *
 * @since 0.1.3
 *
 * @return array Check results.
 */
function ns_theme_check_styles_needed() {

	$main_object = new stdClass();
	$totals_object = new stdClass();
	$totals_object->errors = 1;
	$totals_object->warnings = 0;
	$main_object->totals = $totals_object;

	$files_object = new stdClass();

	$obj1 = new stdClass();
	$obj1->errors = 1;
	$obj1->warnings = 0;

	$obj_sub = new stdClass();
	$obj_sub->message = 'In the theme name, WordPress or Theme keyword is not allowed';
	$obj_sub->type = 'ERROR';
	$obj_sub->line = 2;

	$obj1->messages[] = $obj_sub;

	$files_object->{"/var/www/review.dev/public_html/wp-content/themes/twentytwelve/style.css"} = $obj1;

	$main_object->files = $files_object;

	return $main_object;

}
