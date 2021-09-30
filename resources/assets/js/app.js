require('es6-promise').polyfill();
require('./bootstrap');
require('./global');
require('./element');
const Vue = require('vue');

// Guest visible vue
Vue.component('blank-content', require('./components/BlankContent.vue').default);
Vue.component('loading-more', require('./components/pure/LoadingMore.vue').default);
Vue.component('single-list', require('./components/SingleList.vue').default);

Vue.component('follow', require('./components/button/Follow.vue').default);
Vue.component('favorite', require('./components/button/Favorite.vue').default);
Vue.component('like', require('./components/button/Like.vue').default);
Vue.component('video-like', require('./components/button/VideoLike.vue').default);
Vue.component('comment-module', require('./components/comment/CommentModule').default);
Vue.component('comments', require('./components/comment/Comments.vue').default);
Vue.component('new-comment', require('./components/comment/NewComment.vue').default);
Vue.component('reply-comment', require('./components/comment/ReplyComment.vue').default);

Vue.component('side-tool', require('./components/SideTool.vue').default);
Vue.component('to-comment', require('./components/ToComment.vue').default);
Vue.component('share-module', require('./components/ShareModule.vue').default);
Vue.component('close-share', require('./components/CloseShare.vue').default);

Vue.component('article-list', require('./components/article/ArticleList.vue').default);
Vue.component('action-list', require('./components/action/ActionList.vue').default);
Vue.component('category-list', require('./components/category/CategoryList.vue').default);
Vue.component('search-box', require('./components/search/SearchBox.vue').default);
Vue.component('recently', require('./components/search/Recently.vue').default);
Vue.component('hot-search', require('./components/search/Hot.vue').default);

Vue.component('share', require('./components/Share.vue').default);
Vue.component('video-list', require('./components/video/VideoList.vue').default);
Vue.component('authors-video', require('./components/video/AuthorsVideo.vue').default);
Vue.component('modal-share-wx', require('./components/modals/ModalShareWX.vue').default);

Vue.component('captcha', require('./components/logins/Captcha.vue').default);
Vue.component('social-login', require('./components/logins/SocialLogin.vue').default);
Vue.component('signs', require('./components/logins/Signs.vue').default);

// Authentication visible vue
Vue.component('follow-user-list', require('./components/follow/FollowUserList.vue').default);
Vue.component('follow-categories-list', require('./components/follow/FollowCategoriesList.vue').default);

Vue.component('basic-search', require('./components/search/BasicSearch.vue').default);
Vue.component('tags-input', require('./components/TagsInput.vue').default);
Vue.component('image-select', require('./components/image/ImageSelect.vue').default);
Vue.component('user-select', require('./components/UserSelect.vue').default);
Vue.component('modal-post', require('./components/modals/ModalPost.vue').default);
Vue.component('loading', require('./components/Loading.vue').default);
Vue.component('add-videol', require('./components/video/AddVideol.vue').default);
Vue.component('category-select', require('./components/category/CategorySelect.vue').default);

Vue.component('input-matching', require('./components/question/InputMatching.vue').default);
Vue.component('modal-ask-question', require('./components/question/ModalAskQuestion.vue').default);
Vue.component('delete-button', require('./components/button/DeleteButton.vue').default);

Vue.component('recommend-category', require('./components/category/RecommendCategory.vue').default);
Vue.component('recommend-authors', require('./components/aside/RecommendAuthors.vue').default);
Vue.component('modal-contribute', require('./components/modals/ModalContribute.vue').default);
Vue.component('modal-add-category', require('./components/modals/ModalAddCategory.vue').default);
Vue.component('modal-category-contribute', require('./components/modals/ModalCategoryContribute.vue').default);
Vue.component('modal-delete', require('./components/modals/ModalDelete.vue').default);
Vue.component('modal-admire', require('./components/modals/ModalAdmire.vue').default);
Vue.component('modal-withdraw', require('./components/modals/ModalWithdraw.vue').default);
Vue.component('modal-to-up', require('./components/modals/ModalToUp.vue').default);
Vue.component('modal-like-user', require('./components/modals/ModalLikeUsers.vue').default);
Vue.component('setting-aside', require('./components/setting/Aside.vue').default);

Vue.component('answer-tool', require('./components/question/AnswerTool.vue').default);
Vue.component('question-bottom', require('./components/question/QuestionBottom.vue').default);

Vue.component('request-covers', require('./components/video/RequestCovers.vue').default);

// Editor visible vue
Vue.component('editor', require('./components/Editor.vue').default);
//modals
Vue.component('my-image-list', require('./components/image/MyImageList.vue').default);
Vue.component('my-video-list', require('./components/video/MyVideoList.vue').default);
Vue.component('single-list-create', require('./components/SingleListCreate.vue').default);
Vue.component('single-list-select', require('./components/SingleListSelect.vue').default);

Vue.component('modal-images', require('./components/modals/ModalImages.vue').default);
Vue.component('image-list', require('./components/image/ImageList.vue').default);
Vue.component('upload-image', require('./components/image/UploadImage.vue').default);

Vue.component('modal-blacklist', require('./components/modals/ModalBlacklist.vue').default);
Vue.component('modal-report', require('./components/modals/ModalReport.vue').default);

//关注，消息spa页面
Vue.component('reply-comment', require('./components/comment/ReplyComment.vue').default);
Vue.component('blank-content', require('./components/BlankContent.vue').default);
Vue.component('loading-more', require('./components/pure/LoadingMore.vue').default);
Vue.component('follow', require('./components/button/Follow.vue').default);
Vue.component('article-list', require('./components/article/ArticleList.vue').default);
Vue.component('video-list', require('./components/video/VideoList.vue').default);
Vue.component('hot-search', require('./components/search/Hot.vue').default);
Vue.component('search-box', require('./components/search/SearchBox.vue').default);
Vue.component('recently', require('./components/search/Recently.vue').default);

Vue.component('modal-post', require('./components/modals/ModalPost.vue').default);
Vue.component('loading', require('./components/Loading.vue').default);

Vue.component('notification-aside', require('./components/notification/Aside.vue').default);
Vue.component('follow-aside', require('./components/follow/Aside.vue').default);
Vue.component('setting-aside', require('./components/setting/Aside.vue').default);
Vue.component('side-tool', require('./components/SideTool.vue').default);
Vue.component('to-comment', require('./components/ToComment.vue').default);
Vue.component('share-module', require('./components/ShareModule.vue').default);
Vue.component('close-share', require('./components/CloseShare.vue').default);

//write 编辑写作
Vue.component('write', require('./components/write/Write.vue').default);
Vue.component('note-books', require('./components/write/Notebooks.vue').default);
Vue.component('notes', require('./components/write/Notes.vue').default);
Vue.component('recycle', require('./components/write/Recycle.vue').default);

Vue.component('editor', require('./components/Editor.vue').default);
Vue.component('scroll-top', require('./components/write/ScrollTop.vue').default);
Vue.component('published', require('./components/write/Published.vue').default);
Vue.component('modal-tips', require('./components/modals/ModalTips.vue').default);

//图片素材
Vue.component('modal-images', require('./components/modals/ModalImages.vue').default);
Vue.component('image-list', require('./components/image/ImageList.vue').default);

//文集重命名
Vue.component('modification-name', require('./components/write/modificationName.vue').default);
//常见问题
Vue.component('frequently-asked-questions', require('./components/write/FAQ.vue').default);
//删除文集
Vue.component('delete-notebook', require('./components/write/deleteNotebook.vue').default);

//删除文章
Vue.component('delete-note', require('./components/write/deleteNote.vue').default);
//彻底删除
Vue.component('thorough-delete', require('./components/write/thoroughDelete.vue').default);

//播放器dplayer
Vue.component('dplayer', require('./components/video/DPlayer.vue').default);

//苹果pwa提示
Vue.component('ios-pwa-prompt', require('./components/pwa/PWAPrompt.vue').default);

//spa
import store from './store';
import VueRouter from 'vue-router';
import routes_write from './router/write';
import routes_spa from './router/spa';

let pathname = window.location.pathname;
if (pathname.indexOf('/write') !== -1) {
    Vue.use(VueRouter);
    // 编辑器 /write

    // Bin: 先做路由判断再 new VueRouter 对象就不会污染全局路由。
    const router = new VueRouter({
        routes: routes_write,
    });
    const app = new Vue({
        store,
        router,
    }).$mount('#app');
} else if (
    pathname.indexOf('/follow') !== -1 ||
    pathname.indexOf('/notification') !== -1 ||
    pathname.indexOf('/settings') !== -1
) {
    Vue.use(VueRouter);
    //关注，消息

    // Bin: 先做路由判断再 new VueRouter 对象就不会污染全局路由。
    const router = new VueRouter({
        routes: routes_spa,
    });
    const app = new Vue({
        router,
    }).$mount('#app');
} else {
    const app = new Vue({}).$mount('#app');
}
