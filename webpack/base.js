/* eslint-disable import/no-extraneous-dependencies*/

// Webpack specific imports.
const merge = require( 'webpack-merge' );
const webpack = require( 'webpack' );

// Plugins.
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const ManifestPlugin = require( 'webpack-manifest-plugin' );
const CreateFileWebpack = require( 'create-file-webpack' );
const Licenses = require( 'wp-license-compatibility' );

// All Plugins used in production and development build.
const plugins = [

	// Provide global variables to window object.
	new webpack.ProvidePlugin({
		$: 'jquery',
		jQuery: 'jquery'
	}),

	// Create manifest.json file.
	new ManifestPlugin({
		seed: {}
	}),

	new MiniCssExtractPlugin({
		filename: 'styles/[name]-[hash].css'
	}),

	// Create licenses.json file.

	new CreateFileWebpack({
		path: './assets/build/',
		fileName: 'licenses.json',
		content: JSON.stringify( Licenses, null, 2 )
	}),
];

// All Optimizations used in production and development build.
const optimization = {
	runtimeChunk: false
};

// All Loaders used in production and development build.
const loaders = {
	rules: [
		{
			test: /\.(js|jsx)$/,
			exclude: /node_modules/,
			use: 'babel-loader'
		},
		{
			test: /\.json$/,
			use: 'json-loader'
		},
		{
			test: /\.scss$/,
			exclude: /node_modules/,
			use: [
				MiniCssExtractPlugin.loader,
				{
					loader: 'css-loader',
					options: {
						sourceMap: true,
						url: false
					}
				},
				{
					loader: 'postcss-loader',
					options: {
						sourceMap: true
					}
				},
				{
					loader: 'sass-loader',
					options: {
						sourceMap: true
					}
				},
				{
					loader: 'import-glob-loader'
				}
			]
		}
	]
};

// Main Webpack build setup.
const base = {
	optimization,
	plugins,
	module: loaders
};

// Combine base with blocks specific config.
module.exports = merge( base );
