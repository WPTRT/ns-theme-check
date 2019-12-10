// Other build files.
const config = require( './webpack/config' );

// Load plugins for postcss.
const autoPrefixer = require( 'autoprefixer' );
const cssNano = require( 'cssnano' );

// All Plugins used in production and development build.
const plugins = [
	autoPrefixer
];

module.exports = () => {

	// // Use only for production build
	if ( config.env === 'production' ) {
		plugins.push( cssNano );
	}

	return { plugins };
};
