<?php
/**
 * A notice partial
 *
 * This file contains an empty HTML which is used as a template so that the sniffer can
 * add results in it.
 *
 * @package Theme_Sniffer\Views;
 *
 * @since 1.2.0 Moved to a separate file
 */

namespace Theme_Sniffer\Views;

?>

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
							<button class="theme-sniffer__report-copy-annotation-btn js-annotation-button">
								<span class="dashicons dashicons-clipboard"></span><span class= "theme-sniffer__report-copy-annotation-source js-annotation-source"></span>
							</button>
						</span>
					</td>
				</tr>
			</table>
		</div>
	</div>
