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
 * @param string $data Report data.
 * @return string Modified string.
 */
function ns_theme_check_extra_checks( $data ) {

	$styles_needed = ns_theme_check_styles_needed();

	if ( ! empty( $styles_needed ) ) {
		$data = ns_theme_check_add_errors( $data, $styles_needed );
	}

	return $data;

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
	$obj_sub->message = 'In the theme name, WordPress or Theme keyword is not allowed.';
	$obj_sub->type = 'ERROR';
	$obj_sub->line = 2;

	$obj1->messages[] = $obj_sub;

	$files_object->{"/var/www/review.dev/public_html/wp-content/themes/twentytwelve/style.css"} = $obj1;

	$main_object->files = $files_object;

	return $main_object;

}

/**
 * Add errors in main data.
 *
 * @since 0.1.3
 *
 * @param mixed $data Original data.
 * @param mixed $new New data.
 * @return array Modified data.
 */
function ns_theme_check_add_errors( $data, $new ) {

	if ( 0 === absint( $new->totals->errors ) && 0 === absint( $new->totals->warnings ) ) {
		return $data;
	}

	$data->totals->errors += absint( $new->totals->errors );
	$data->totals->warnings += absint( $new->totals->warnings );

	foreach ( $new->files as $file_key => $file_value ) {
		$data->files->$file_key->errors += absint( $file_value->errors );
		$data->files->$file_key->warnings += absint( $file_value->warnings );
		$data->files->$file_key->messages = array_merge( $data->files->$file_key->messages, $file_value->messages );
	}

	return $data;
}
