import js from '@eslint/js'
import globals from 'globals'
import pluginVue from 'eslint-plugin-vue'
import css from '@eslint/css'
import { defineConfig } from 'eslint/config'

export default defineConfig([
  // 1) Ignora dist, node_modules e cartelle esterne
  {
    ignores: [
      'dist/**',
      'node_modules/**',
      // se lanci eslint dalla monorepo, puoi anche limitare a:
      '../**',
    ],
  },

  // 2) JS + Vue
  {
    files: ['**/*.{js,mjs,cjs,vue}'],
    plugins: { js },
    extends: ['js/recommended'],
    languageOptions: {
      globals: globals.browser,
    },
  },

  // 3) Config Vue flat (solo sui file dove serve)
  pluginVue.configs['flat/essential'],

  // 4) CSS
  {
    files: ['**/*.css'],
    plugins: { css },
    language: 'css/css',
    extends: ['css/recommended'],
  },
])
