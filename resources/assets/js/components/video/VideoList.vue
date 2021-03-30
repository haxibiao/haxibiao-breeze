<template>
    <div class="video-box">
        <div class="box-top">
            <div class="top-left">
                <i class="iconfont icon-fabulous"></i>
                <a href="/">
                    <p class="title">更多推荐</p>
                </a>
            </div>
        </div>
        <div class="box-body">
            <ul class="game-video-list">
                <li v-for="post in posts" v-bind:key="post.id" class="game-video-item">
                    <a :href="'/video/' + post.video.id" class="video-info" :target="isDesktop ? '_blank' : '_self'">
                        <img class="video-photo" :src="post.cover" />
                        <i class="hover-play"> </i>
                    </a>
                    <a :href="'/video/' + post.video.id" class="video-title" :target="isDesktop ? '_blank' : '_self'">{{
                        post.content
                    }}</a>
                    <div class="info">
                        <a class="user" :href="'/user/' + post.user.id">
                            <img :src="post.user.avatar" class="avatar" />
                            <span>{{ post.user.name }}</span>
                        </a>
                        <div class="num">
                            <i class="iconfont icon-liulan"> {{ post.hits }}</i>
                            <i class="iconfont icon-svg37" v-if="post.count_comments > 0"> {{ post.count_replies }}</i>
                            <i class="iconfont icon-svg37" v-else> 0</i>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <!-- 查看更多视频 -->
        <a class="btn-base btn-more" href="javascript:;">
            {{ page >= lastPage ? '已经到底了' : '正在加载更多' }}
        </a>
    </div>
</template>

<script>
export default {
    name: 'VideoList',

    props: ['api', 'startPage', 'isStick', 'isDesktop'],

    watch: {
        api(val) {
            this.clear();
            this.fetchData();
        }
    },

    computed: {
        apiUrl: {
            get() {
                var api_url =
                    this.api.indexOf('?') !== -1 ? this.api + '&page=' + this.page : this.api + '?page=' + this.page;
                if (this.isStick) api_url += '&stick=true';
                return api_url;
            }
        }
    },

    mounted() {
        this.listenScrollBotton();
        this.fetchData();
    },

    methods: {
        clear() {
            this.posts = [];
            this.page = 1;
        },
        listenScrollBotton() {
            var m = this;
            $(window).on('scroll', function() {
                var aheadMount = 5;
                var reachedBottom = $(this).scrollTop() >= $('body').height() - $(window).height() - aheadMount;
                if (reachedBottom) {
                    m.fetchMore();
                }
            });
        },
        fetchMore() {
            if (this.lastPage > 0 && this.page > this.lastPage) {
                console.log('已经到底了');
                return;
            }
            this.fetchData();
        },
        fetchData() {
            if (this.loading) return;
            this.loading = true;
            var that = this;
            window.axios
                .get(this.apiUrl)
                .then(function(response) {
                    const data = response.data.data;
                    if (data && data.length > 0) {
                        that.posts = that.posts.concat(data);
                        that.lastPage = response.data.lastPage;
                    }
                    ++that.page;
                    that.loading = false;
                })
                .catch(function(e) {
                    that.loading = false;
                });
        }
    },

    data() {
        return {
            posts: [],
            page: this.startPage || 0,
            lastPage: -1,
            loading: false
        };
    }
};
</script>

<style lang="css" scoped>
</style>
