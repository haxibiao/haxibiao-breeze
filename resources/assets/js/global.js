import Vue from 'vue';

Object.defineProperty(window, 'GLOBAL', {
    value: {},
    writable: false,
    enumerable: true,
    configurable: false,
});

window.$bus = GLOBAL.VueBus = new Vue();
window.$bus.state = {
    answer: {
        answerIds: [],
    },
};
