import axios from './axios';

var API_CONFIG = {
    comment: [
        {
            name: 'query',
            method: 'GET',
            desc: '评论列表',
            path: '/comment/{{id}}/{{type}}',
            params: {
                page: 1,
                order: 'like',
                api_token: 'token',
            },
            // path {{}} variable
            variables: {
                id: 1,
                type: 'articles',
            },
        },
        {
            name: 'create',
            method: 'POST',
            desc: '写评论',
            path: '/comment',
            params: {
                api_token: 'token',
            },
            data: {
                // 后端检索body中的'@xxx '字符串，验证xxx是否为正确存在的用户名
                body: '评论内容',
                commentable_id: 1,
                commentable_type: 'articles',
                //父级评论的id
                comment_id: 11,
                //回复的人
                user: {
                    id: 22,
                    name: '用户名',
                },
            },
        },
        {
            name: 'like',
            method: 'GET',
            desc: '点赞评论',
            path: '/comment/{{id}}/like',
            variables: {
                id: 1,
            },
        },
        {
            name: 'report',
            method: 'GET',
            desc: '举报评论',
            path: '/comment/{{id}}/report',
            variables: {
                id: 1,
            },
        },
    ],
};

class APIConstruct {
    constructor(options) {
        this.api = {};
        this.builder(options);
    }

    builder({ config = {} }) {
        Object.keys(config).map(namespace => {
            this._makeApi({
                namespace,
                config: config[namespace],
            });
        });
    }
    _makeApi({ namespace, config = {} }) {
        config.forEach(api => {
            const { name, method, path } = api;
            Object.defineProperty(this.api, `${namespace}/${name}`, {
                value(options = {}) {
                    const { variables, ...props } = options;
                    const url = compileStringTemplate(path, variables);
                    return axios(
                        Object.assign(
                            {
                                url,
                                method,
                            },
                            props,
                        ),
                    );
                },
            });
        });
    }
}

function compileStringTemplate(path, variables = {}) {
    var url = path;
    for (var key in variables) {
        var re = new RegExp('{{' + key + '}}', 'g');
        url = url.replace(re, variables[key]);
    }
    return url;
}

// 注入配置实例化，暴露api出去
export default new APIConstruct({
    config: API_CONFIG,
})['api'];

// 使用
// api['comment/query']({ variables: { id: 1, type: 'article' }, params: { api_token: 'xxx' } });
