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
				'editor/js/slotFields': path.resolve( __dirname, 'js/editor/slotFields.ts' ),
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: '[name].js',
				intro: '(function(){',
				outro: '})();',
				assetFileNames: ( info ) => {
					const name = info.name || '';
					if ( name.includes( 'slotFields' ) ) {
						return 'editor/css/[name][extname]';
					}
					return 'admin/css/[name][extname]';
				},
			},
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
