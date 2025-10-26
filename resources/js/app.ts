import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';

const appName = import.meta.env.VITE_APP_NAME || 'CorpVitals24';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./pages/**/*.vue')
    ),
  setup({ el, App, props, plugin }) {
    const pinia = createPinia();

    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(pinia);

    // Initialize store sync with Inertia shared data
    // This happens automatically via the useStoreSync composable in components

    app.mount(el);
  },
  progress: {
    color: '#4F46E5', // Indigo-600 per la progress bar
    showSpinner: true,
  },
});

