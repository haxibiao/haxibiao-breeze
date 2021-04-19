import axios from 'axios';
const AXIOS_DEFAULT_CONFIG = {
    timeout: 20000,
    maxContentLength: 2000,
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
};
const CONSOLE_REQUEST_ENABLE = true;

function requestSuccessFunc(config) {
    CONSOLE_REQUEST_ENABLE && console.info('requestSuccessFunc', config);
    // 请求拦截逻辑，可以处理权限，请求发送监控等
    return config;
}

function requestFailFunc(error) {
    CONSOLE_REQUEST_ENABLE && console.info('requestFailFunc', error);
    // 发送请求失败逻辑，断网，请求发送监控等
    return Promise.reject(error);
}

function responseSuccessFunc(response) {
    CONSOLE_REQUEST_ENABLE && console.info('responseSuccessFunc', response);
    // 响应成功逻辑
    response.config.successMessage &&
        GLOBAL.vueBus.$emit('global.$dialog.show', {
            response,
            type: 'success',
        });
    return response.data;
}

function responseFailFunc(error) {
    CONSOLE_REQUEST_ENABLE && console.info('responseFailFunc', error);
    // 响应失败逻辑
    error.config.errorMessage &&
        GLOBAL.vueBus.$emit('global.$dialog.show', {
            error,
            type: 'error',
        });
    return Promise.reject(error);
}

// 注入axios默认配置
const axiosInstance = axios.create(AXIOS_DEFAULT_CONFIG);
// 注入请求拦截
axiosInstance.interceptors.request.use(requestSuccessFunc, requestFailFunc);
// 注入响应拦截
axiosInstance.interceptors.response.use(responseSuccessFunc, responseFailFunc);

export default axiosInstance;
