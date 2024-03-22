/**
 * Laravel Mix configuration file.
 *
 * Laravel Mix is a layer built on top of Webpack that simplifies much of the
 * complexity of building out a Webpack configuration file. Use this file
 * to configure how your assets are handled in the build process.
 *
 * @link https://laravel.com/docs/5.8/mix
 */

// Import required packages.
const mix = require("laravel-mix");
const CopyPlugin = require("copy-webpack-plugin");
const DependencyExtractionWebpackPlugin = require("@wordpress/dependency-extraction-webpack-plugin");

/*
 * Disable all notifications.
 */

mix.disableNotifications();

/*
 * -----------------------------------------------------------------------------
 * Build Process
 * -----------------------------------------------------------------------------
 * The section below handles processing, compiling, transpiling, and combining
 * all of the theme's assets into their final location. This is the meat of the
 * build process.
 * -----------------------------------------------------------------------------
 */

/*
 * Sets the development path to assets. By default, this is the `/resources`
 * folder in the theme.
 */
const devPath = "resources";
const distPath = "assets";

/*
 * Sets the path to the generated assets. By default, this is the root folder in
 * the theme. If doing something custom, make sure to change this everywhere.
 */
mix.setPublicPath("./");

/*
 * Builds sources maps for assets.
 *
 * @link https://laravel.com/docs/5.6/mix#css-source-maps
 */
// mix.sourceMaps();

/*
 * Versioning and cache busting. Append a unique hash for production assets. If
 * you only want versioned assets in production, do a conditional check for
 * `mix.inProduction()`.
 *
 * @link https://laravel.com/docs/5.6/mix#versioning-and-cache-busting
 */
mix.version();

/*
 * Compile CSS.
 */
mix.sass(`${devPath}/scss/blocks.scss`, `${distPath}/blocks`)
.sass(`${devPath}/scss/settings.scss`, `${distPath}/css`)
.sass(`${devPath}/scss/wc-cart-pdf.scss`, `${distPath}/css`);

/*
 * Compile JavaScript.
 */
mix.js(`${devPath}/js/wc-cart-pdf.js`, `${distPath}/js`)
	.js(`${devPath}/js/settings.js`, `${distPath}/js`)
	.js(`${devPath}/js/worker.js`, `${distPath}/js`)
	.js(`${devPath}/js/blocks.js`, `${distPath}/blocks`)
	.react();

/*
 * Add custom Webpack configuration.
 *
 * Laravel Mix doesn't currently minimize images while using its `.copy()`
 * function, so we're using the `CopyPlugin` for processing and copying
 * images into the distribution folder.
 *
 * @link https://laravel.com/docs/5.6/mix#custom-webpack-configuration
 * @link https://webpack.js.org/configuration/
 */
mix.webpackConfig({
	stats: "minimal",
	performance: { hints: false },
	externals: { jquery: "jQuery" },
	plugins: [
		new DependencyExtractionWebpackPlugin(),
		new CopyPlugin({
			patterns: [
				{ from: `${devPath}/js/block.json`, to: `${distPath}/blocks` },
			],
		}),
	],
});