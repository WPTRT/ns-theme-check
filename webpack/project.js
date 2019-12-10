/* eslint-disable import/no-extraneous-dependencies*/

// Other build files.
const config = require( './config' );

// Plugins.

// Main Webpack build setup - Project specific.
const project = {
	context: config.appPath,
	entry: {
		application: [ config.assetsEntry ]
	},
	output: {
		path: config.outputPath,
		publicPath: '',
		filename: 'scripts/[name]-[hash].js'
	},
	externals: {
		jquery: 'jQuery',
		esprima: 'esprima'
	},

	plugins: [
	]
};

// Export project specific configs.
// IF you have multiple builds a flag can be added to the package.json config and use switch case to determin the build config.
module.exports = project;
