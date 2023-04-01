import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh'; // Spécifique à react
import { resolve } from 'path';

const twigRefreshPlugin = {
  name: 'twig-refresh',
  configureServer ({ watcher, ws }) {
    watcher.add(resolve('templates/**/*.twig'));
    watcher.on('change', function (path) {
      if (path.endsWith('.twig')) {
        ws.send({
          type: 'full-reload'
        });
      }
    });
  }
}

export default defineConfig({
  plugins: [reactRefresh(), twigRefreshPlugin],
  root: './assets',
  base: '/assets/',
  server: {
    watch: {
      disableGlobbing: false
    }
  },
  build: {
    manifest: true,
    assetsDir: '',
    outDir: '../public/assets/',
    rollupOptions: {
      output: {
        manualChunks: undefined
      },
      input: {
        'main.tsx': './assets/main.tsx',
        'index.css': './assets/index.css',
        'primeicons.css': './node_modules/primeicons/primeicons.css',
      }
    }
  }
});
