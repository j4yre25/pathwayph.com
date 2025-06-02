import './bootstrap';
import '../css/app.css';

import Chart from 'chart.js/auto'; // ✅ Use this instead of registerables

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import 'quill/dist/quill.snow.css';
import * as lucide from 'lucide-vue-next';

import * as echarts from 'echarts/core';
import {
  PieChart,
  LineChart,
  BarChart
} from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  ToolboxComponent,
  GridComponent,
  DatasetComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import VueECharts from 'vue-echarts';

echarts.use([
  PieChart,
  LineChart,
  BarChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  ToolboxComponent,
  GridComponent,
  DatasetComponent,
  CanvasRenderer
]);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) });

    for (const [key, component] of Object.entries(lucide)) {
      vueApp.component(key, component);
    }

    vueApp
      .use(plugin)
      .use(ZiggyVue)
      .component('VueECharts', VueECharts)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});