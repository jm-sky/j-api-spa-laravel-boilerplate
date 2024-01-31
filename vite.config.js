import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';

export default ({ mode }) => {
  process.env = { ...process.env, ...loadEnv(mode, process.cwd()) }

  return defineConfig({
    base: '',
    root: 'src',

    server: {
      host: '0.0.0.0',
      port: Number(process.env.VITE_PORT ?? 9173),
      strictPort: true,
      watch: {
        usePolling: true,
      },
    },
    build: {
      // manifest: 'manifest.json',
      outDir: '../../public/',
      chunkSizeWarningLimit: 1600,
      rollupOptions: {
        output: {
          manualChunks: {
            lodash: ['lodash'],
          },
        },
      },
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
    plugins: [
      vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
    ],
    define: {
      __VUE_I18N_FULL_INSTALL__: true,
      __VUE_I18N_LEGACY_API__: false,
      __INTLIFY_PROD_DEVTOOLS__: false,
    },
  })
}
