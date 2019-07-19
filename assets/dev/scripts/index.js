import $ from 'jquery';
import ThemeSniffer from './theme-sniffer';

$(
	function() {
		const options = {
			sniffReport: $( '.js-sniff-report' ),
			snifferInfo: $( '.js-sniffer-info' ),
			checkNotice: $( '.js-check-done' ),
			startNotice: $( '.js-start-notice' ),
			reportItem: $( '.js-report-item' ),
			loader: $( '.js-loader' ),
			startButton: '.js-start-check',
			stopButton: '.js-stop-check',
			reportItemHeading: '.js-report-item-heading',
			reportReportTable: '.js-report-table',
			reportNoticeType: '.js-report-notice-type',
			reportNoticeSource: '.js-report-notice-source',
			reportItemBtn: '.theme-sniffer__report-copy-annotation-btn',
			reportItemSource: '.theme-sniffer__report-copy-annotation-source',

			reportItemLine: '.js-report-item-line',
			reportItemType: '.js-report-item-type',
			reportItemMessage: '.js-report-item-message',
			runAction: 'run_sniffer',
			nonce: $( '#theme-sniffer_nonce' ).val()
		};

		const themeSniffer = new ThemeSniffer( options );

		$( options.startButton ).on(
			'click', () => {
				const theme             = $( 'select[name=themename]' ).val();
				const warningHide       = $( 'input[name=hide_warning]' ).is( ':checked' );
				const outputRaw         = $( 'input[name=raw_output]' ).is( ':checked' );
				const ignoreAnnotations = $( 'input[name=ignore_annotations]' ).is( ':checked' );
				const checkPhpOnly      = $( 'input[name=check_php_only]' ).is( ':checked' );
				const minPHPVersion     = $( 'select[name=minimum_php_version]' ).val();
				const themePrefixes     = $( 'input[name=theme_prefixes]' ).val();

				const selectedRulesets = $( 'input[name="selected_ruleset[]"]:checked' ).map( ( ind, el ) => el.value ).toArray();

				themeSniffer.enableAjax();
				themeSniffer.themeCheckRunPHPCS( theme, warningHide, outputRaw, ignoreAnnotations, checkPhpOnly, minPHPVersion, selectedRulesets, themePrefixes );
			}
		);

		$( options.stopButton ).on(
			'click', () => themeSniffer.preventAjax()
		);

		$( 'select[name="themename"]' ).on(
			'change', () => {
				if ( options.startNotice.html().length ) {
					themeSniffer.preventAjax();

					if ( options.sniffReport.length ) {
						options.sniffReport.empty();
					}
				}

			}
		);
	}
);
