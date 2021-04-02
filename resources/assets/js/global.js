import Vue from 'vue';
import axios from 'axios';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import { optionalChaining } from './plugins/vue-properties';

// GLOBAL
Object.defineProperty(window, 'GLOBAL', {
    value: {},
    writable: false,
    enumerable: true,
    configurable: false,
});
window.$bus = GLOBAL.vueBus = new Vue();
window.$bus.state = {
    answer: {
        answerIds: [],
    },
};
//element
Vue.use(ElementUI);
// prototype
Vue.prototype.$http = axios;
Vue.prototype.$optional = optionalChaining;
