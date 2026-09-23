import { defineConfig } from 'vite';

/**
 * Builds every island into one small IIFE bundle at assets/js/dist/app.js.
 *
 * IIFE rather than ESM on purpose: WordPress enqueues it as a classic
 * deferred script, and the theme supports no build step at runtime.
 */
export default defineConfig({
  build: {
    outDir: 'assets/js/dist',
    emptyOutDir: true,
    target: 'es2019',
    lib: {
      entry: 'src/main.jsx',
      name: 'SSBD',
      formats: ['iife'],
      fileName: () => 'app.js',
    },
    rollupOptions: {
      output: {
        // Everything is inlined; the theme loads exactly one script.
        inlineDynamicImports: true,
      },
    },
    minify: 'esbuild',
    sourcemap: false,
  },
  // Without this, lib mode leaves `process.env.NODE_ENV` undefined and
  // React ships its development build — roughly 3x the bytes.
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
  esbuild: {
    jsx: 'automatic',
  },
});
