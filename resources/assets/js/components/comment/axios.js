import axios from 'axios';
const AXIOS_DEFAULT_CONFIG = {
    timeout: 10000,
    maxContentLength: 2000,
    headers: {},
};
const CONSOLE_REQUEST_ENABLE = true;

function requestSuccessFunc(config) {
    CONSOLE_REQUEST_ENABLE && console.info('requestSuccessFunc', config);
    // 请求拦截逻辑，可以处理权限，请求发送监控等
    // ...
    return config;
}

function requestFailFunc(error) {
    CONSOLE_REQUEST_ENABLE && console.info('requestFailFunc', error);
    // 发送请求失败逻辑，断网，请求发送监控等
    // ...
    return Promise.reject(error);
}

function responseSuccessFunc(response) {
    CONSOLE_REQUEST_ENABLE && console.info('responseSuccessFunc', response);
    const resData = response.data;
    const { code } = resData;
    switch (code) {
        case 0:
            // 如果业务成功，直接进成功回调
            return resData.data;
        default:
            // 特殊code逻辑，在这里做统一处理，也可以下放到业务层
            !response.config.noShowDefaultError && GLOBAL.VueBus.$emit('global.$dialog.show', resData.msg);
            return Promise.reject(resData);
    }
}

function responseFailFunc(error) {
    CONSOLE_REQUEST_ENABLE && console.info('responseFailFunc', error);
    // 响应失败，可根据 error.message 和 error.response.status 来做监控处理
    // ...
    return Promise.reject(error);
}

// 注入axios默认配置
const axiosInstance = axios.create(AXIOS_DEFAULT_CONFIG);
// 注入请求拦截
axiosInstance.interceptors.request.use(requestSuccessFunc, requestFailFunc);
// 注入响应拦截
axiosInstance.interceptors.response.use(responseSuccessFunc, responseFailFunc);

export default axiosInstance;
