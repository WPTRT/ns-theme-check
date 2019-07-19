const path = require( 'path' );

const webpack = require( 'webpack' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const ManifestPlugin = require( 'webpack-manifest-plugin' );
const FileManagerPlugin = require( 'filemanager-webpack-plugin' );

const DEV = process.env.NODE_ENV !== 'production';

const appPath = __dirname;

// Entry
const pluginPath = '/assets';
const pluginFullPath = `${appPath}${pluginPath}`;
const pluginEntry = `${pluginFullPath}/dev/application.js`;
const pluginPublicPath = `${pluginFullPath}/build`;

// Outputs
const outputJs = 'scripts/[name]-[hash].js';
const outputCss = 'styles/[name]-[hash].css';

const allModules = {
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
			use: [
				MiniCssExtractPlugin.loader,
				'css-loader', 'sass-loader'
			]
		}
	]
};

const allPlugins = [
	new CleanWebpackPlugin(),
	new MiniCssExtractPlugin(
		{
			filename: outputCss
		}
	),
	new webpack.ProvidePlugin(
		{
			$: 'jquery',
			jQuery: 'jquery'
		}
	),
	new webpack.DefinePlugin(
		{
			'process.env': {
				NODE_ENV: JSON.stringify( process.env.NODE_ENV || 'development' )
			}
		}
	),
	new ManifestPlugin()
];

const allOptimizations = {
	runtimeChunk: false,
	splitChunks: {
		cacheGroups: {
			commons: {
				test: /[\\/]node_modules[\\/]/,
				name: 'vendors',
				chunks: 'all'
			}
		}
	}
};

// Use only for production build
if ( ! DEV ) {
	allOptimizations.minimizer = [
		new TerserPlugin({
			cache: true,
			parallel: true,
			sourceMap: true,
		})
	];

	allPlugins.push(
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
						'./theme-sniffer/webpack.config.js'
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
	);

}

module.exports = [
	{
		context: path.join( appPath ),

		entry: {
			themeSniffer: [ pluginEntry ]
		},

		output: {
			path: pluginPublicPath,
			publicPath: '',
			filename: outputJs
		},

		externals: {
			jquery: 'jQuery',
			esprima: 'esprima'
		},

		optimization: allOptimizations,

		module: allModules,

		plugins: allPlugins
	}
];
