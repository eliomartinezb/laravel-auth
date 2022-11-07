import './bootstrap';

import { createApp } from 'vue';
import Router from './routes';

const app = createApp({});

app.use(Router);

app.mount('#app');
