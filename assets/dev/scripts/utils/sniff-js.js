/**
 * Sniff a JavaScript file.
 *
 * @since 1.1.0
 *
 * @prop {string} path                - File's Path relative to WordPress' ABSPATH.
 * @prop {object} fileObject          - ThemeSniffer file sniff object.
 * @prop {object} fileObject.filePath - Absolute path to file to sniff.
 * @prop {object} fileObject.errors   - Total errors on file to sniff.
 * @prop {array}  fileObject.messages - ThemeSniffer Error message objects for ouput.
 *
 * @type {SniffJs}
 */
export class SniffJs {

	/**
	 * Set object properties and instantiate.
	 *
	 * @param {Object} fileObject ThemeSniffer file sniff object.
	 *
	 * @since 1.1.0
	 */
	constructor( fileObject ) {
		this.fileObject = fileObject;
		// Seems like syntax errors or empty results cause phpcs to provide a non-number for error count.
		this.fileObject.errors = isNaN( this.fileObject.errors ) ? 0 : parseInt( this.fileObject.errors, 10 );
		this.path = this.getPath();
	}

	/**
	 * Get relative path to WordPress' ABSPATH from absolute path.
	 *
	 * @since 1.1.0
	 *
	 * @return {string} Relative path from ABSPATH.
	 */
	getPath() {
		let jsFile = this.fileObject.filePath.split( /((?:[^/]*\/)*)(.*)\/themes\//gmi ),
			fp = jsFile.pop(), // Filepath relative to wp-content/themes/.
			wpContent = jsFile.pop(); // Name of wpContent folder.
		return `/${ wpContent }/themes/${ fp }`;
	}

	/**
	 * Format Espirma errors for ThemeSniffer consumption.
	 *
	 * @param {Object} err An Espirma error.
	 *
	 * @since 1.1.0
	 */
	format( err ) {
		this.fileObject.errors++;
		this.fileObject.messages.push(
			{
				line: err.lineNumber,
				column: err.column,
				message: err.description,
				severity: 5,
				type: 'ERROR',
				fixable: false
			}
		);
	}

	/**
	 * Processes the fileObject class property.
	 *
	 * This will get the file contents of the file requested for sniff, and
	 * then will do syntax checks using Espirma.  The tolerant mode for
	 * Espirma is on to allow multiple errors to come through if Esprima can
	 * continue syntax checking.  It's not perfect, but it helps!  Loc is on
	 * so we can include the col/line nums in our reporter, which are passed
	 * back to the fileObject.
	 *
	 * @since 1.1.0
	 *
	 * @return {Promise|fileObject} A promise for the fileObject passed in the constructor.
	 */
	process() {
		return new Promise( ( resolve, reject ) => {
			fetch( this.path )
				.then( response => response.text() )
				.then( data => {
					const errors = esprima.parse( data,
						{
							tolerant: true,
							loc: true
						}
					).errors;

					for ( let error of errors ) {
						this.format( error );
					}
				})
				.catch( error => this.format( error ) )
				.finally( () => this.fileObject.errors && resolve( this.fileObject ) );
		});
	}
}

export default SniffJs;
