import { createApp } from 'vue';
import App from './App';
import router from './router';
import { IonicVue } from '@ionic/vue';

import '@ionic/vue/css/core.css';
import '@ionic/vue/css/normalize.css';
import '@ionic/vue/css/structure.css';
import '@ionic/vue/css/typography.css';
import '@ionic/vue/css/padding.css';
import '@ionic/vue/css/float-elements.css';
import '@ionic/vue/css/text-alignment.css';
import '@ionic/vue/css/text-transformation.css';
import '@ionic/vue/css/flex-utils.css';
import '@ionic/vue/css/display.css';
import './theme/variables.css';

import MasterLayout from '@/components/MasterLayout';
import './registerServiceWorker';

const app = createApp(App)
    .use(IonicVue)
    .use(router);

app.component('master-layout', MasterLayout);

router.isReady().then(() => app.mount('#app'));