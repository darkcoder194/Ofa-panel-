import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  build: {
    rollupOptions: {
      input: {
        ofaAdmin: path.resolve(__dirname, 'resources/js/admin/app.js'),
      },
      output: {
        entryFileNames: 'js/ofa-admin.[hash].js',
      }
    },
    outDir: 'public'
  }
})
