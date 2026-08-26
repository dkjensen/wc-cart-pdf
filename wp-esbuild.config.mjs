import { defineConfig } from '@cloudcatch/wp-esbuild/config';

export default defineConfig( {
	srcDir: 'resources',
	outDir: 'assets',
	minify: process.env.NODE_ENV === 'production',
	sourcemap: process.env.NODE_ENV !== 'production',
	esbuild: {
		jsx: 'transform',
	},
	blocksManifest: {
		enabled: false,
	},
	wordpressExternals: {
		vendors: {
			'@wordpress/blockEditor': {
				global: 'wp.blockEditor',
				handle: 'wp-block-editor',
			},
		},
	},
	blocks: {
		src: 'resources/blocks',
		out: 'assets/blocks',
	},
	js: {
		src: 'resources/js',
		out: 'assets/js',
		glob: 'wc-cart-pdf.js',
	},
	modules: {
		enabled: false,
	},
	scss: {
		src: 'resources/scss',
		out: 'assets/css',
		glob: 'wc-cart-pdf.scss',
		assetPhp: true,
	},
} );
