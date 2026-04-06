import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig( {
	build: {
		outDir: path.resolve( __dirname, '..' ),
		emptyOutDir: false,
		minify: 'terser',
		terserOptions: {
			mangle: {
				reserved: [ '_', '$' ],
			},
		},
		rollupOptions: {
			input: {
				'admin/js/admin': path.resolve( __dirname, 'js/admin/adminApp.ts' ),
				'blocks/js/slot-grid': path.resolve( __dirname, 'js/blocks/slot-grid/index.ts' ),
				'blocks/js/slot-grid-frontend': path.resolve( __dirname, 'js/blocks/slot-grid/frontend.ts' ),
				'blocks/js/slot-detail': path.resolve( __dirname, 'js/blocks/slot-detail/index.ts' ),
				'blocks-frontend-css': path.resolve( __dirname, 'scss/blocks/frontend.scss' ),
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: '[name].js',
				intro: '(function(){',
				outro: '})();',
				assetFileNames: ( info ) => {
					const name = info.name || '';
					if ( name.includes( 'blocks-frontend-css' ) ) {
						return 'blocks/css/frontend[extname]';
					}
					if ( name.includes( 'slot' ) || name.includes( 'block' ) || name.includes( 'frontend' ) || name.includes( 'editor' ) ) {
						return 'blocks/css/[name][extname]';
					}
					return 'admin/css/[name][extname]';
				},
			},
			external: [
				/^@wordpress\//,
			],
		},
	},
	css: {
		preprocessorOptions: {
			scss: {},
		},
	},
	test: {
		environment: 'jsdom',
		include: [ '__tests__/**/*.test.ts' ],
		globals: true,
	},
} );
