import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

const routes = [
    //write 编辑
    { path: '/', redirect: '/notebooks' },
    { path: '/notebooks', component: require('../components/write/Notebooks.vue').default },
    {
        path: '/notebooks/:collectionId',
        component: require('../components/write/Notebooks.vue').default,
        props: true,
        children: [
            {
                path: 'notes/:articleId',
                component: require('../components/write/Notes.vue').default,
            },
        ],
    },
    { path: '/recycle', component: require('../components/write/Recycle.vue').default },
    { path: '/recycle/:recycleId', component: require('../components/write/Recycle.vue').default, props: true },

    //spa 关注 消息
    {
        path: '/comments',
        component: require('../components/notification/Comments.vue').default,
    },
    {
        path: '/chats',
        component: require('../components/notification/Chats.vue').default,
    },
    {
        path: '/chat/:id',
        component: require('../components/notification/Chat.vue').default,
    },
    {
        path: '/requests',
        component: require('../components/notification/Requests.vue').default,
    },
    {
        path: '/likes',
        component: require('../components/notification/Likes.vue').default,
    },
    {
        path: '/follows',
        component: require('../components/notification/Follows.vue').default,
    },
    {
        path: '/tips',
        component: require('../components/notification/Tips.vue').default,
    },
    {
        path: '/others',
        component: require('../components/notification/Others.vue').default,
    },

    {
        path: '/timeline',
        component: require('../components/follow/Timeline.vue').default,
    },
    {
        path: '/categories/:id',
        component: require('../components/follow/Category.vue').default,
    },
    {
        path: '/collections/:id',
        component: require('../components/follow/Collection.vue').default,
    },
    {
        path: '/users/:id',
        component: require('../components/follow/User.vue').default,
    },
    {
        path: '/recommend',
        component: require('../components/follow/Recommend.vue').default,
    },
    {
        path: '/submissions/:id',
        component: require('../components/notification/Submissions.vue').default,
    },
    {
        path: '/pending_submissions',
        component: require('../components/notification/PendingSubmissions.vue').default,
    },
    {
        path: '/base',
        component: require('../components/setting/Base.vue').default,
    },
    {
        path: '/profile',
        component: require('../components/setting/Profile.vue').default,
    },
    {
        path: '/reward',
        component: require('../components/setting/Reward.vue').default,
    },
];

export default new VueRouter({
    routes,
});
