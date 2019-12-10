/* eslint-disable import/no-extraneous-dependencies*/

// Webpack specific imports.
const merge = require( 'webpack-merge' );

// Other build files.
const base = require( './base' );
const project = require( './project' );

// Plugins.
const TerserPlugin = require( 'terser-webpack-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
const FileManagerPlugin = require( 'filemanager-webpack-plugin' );

// All Plugins used in production build.
const plugins = [

	// Clean public files before next build.

	new CleanWebpackPlugin(),

	new FileManagerPlugin({
		onEnd: [
			{
				copy: [
					{
						source: './',
						destination: './theme-sniffer'
					}
				]
			},
			{
				delete: [
					'./theme-sniffer/assets/dev',
					'./theme-sniffer/node_modules',
					'./theme-sniffer/composer.json',
					'./theme-sniffer/composer.lock',
					'./theme-sniffer/package.json',
					'./theme-sniffer/package-lock.json',
					'./theme-sniffer/phpcs.xml.dist',
					'./theme-sniffer/webpack.config.js',
					'./theme-sniffer/CODE_OF_CONDUCT.md',
					'./theme-sniffer/CONTRIBUTING.md'
				]
			},
			{
				archive: [
					{
						source: './theme-sniffer',
						destination: './theme-sniffer.zip',
						options: {
							gzip: true,
							gzipOptions: { level: 1 },
							globOptions: { nomount: true }
						}
					}
				]
			},
			{
				delete: [
					'./theme-sniffer'
				]
			}
		]
	})

];

// All Optimizations used in production build.
const optimization = {
	minimizer: [
		new TerserPlugin({
			cache: true,
			parallel: true,
			terserOptions: {
				output: {
					comments: false
				}
			}
		})
	]
};

// Define productionConfig setup.
const productionConfig = {
	plugins,
	optimization,

	devtool: 'inline-cheap-module-source-map'
};

// Combine base with productionConfig specific config.
module.exports = merge( project, base, productionConfig );
