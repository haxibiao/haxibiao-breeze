<template>
    <div id="comments">
        <!-- {{-- 写评论 --}} -->
        <new-comment @sendComment="postComment"></new-comment>
        <!-- {{-- 默认评论 --}} -->
        <div class="normal-comment">
            <div>
                <!-- {{-- 顶部信息 --}} -->
                <div class="top">
                    <span>{{ commentReplies }}条评论</span>
                    <a
                        :class="['btn-base', 'btn-light', 'btn-sm', isOnlyAuthor ? 'active' : '']"
                        @click="showOnlyAuthor"
                        >只看作者</a
                    >
                    <div class="pull-right">
                        <a :class="order == 'like' ? 'active' : ''" @click="sortComments('like')">按点赞排序</a>
                        <a
                            :class="order == 'timeAsc' ? 'active hidden-xs' : 'hidden-xs'"
                            @click="sortComments('timeAsc')"
                            >按时间正序</a
                        >
                        <a
                            :class="order == 'timeDesc' ? 'active hidden-xs' : 'hidden-xs'"
                            @click="sortComments('timeDesc')"
                            >按时间倒序</a
                        >
                    </div>
                </div>
                <!-- {{-- 评论 --}} -->
                <div class="comment-item" v-for="(comment, index) in comments" :key="comment.id">
                    <div>
                        <div class="user-info info-xs">
                            <a :href="'/user/' + comment.user.id" class="avatar"><img :src="comment.user.avatar"/></a>
                            <div class="title">
                                <a :href="'/user/' + comment.user.id" class="nickname">{{ comment.user.name }}</a>
                            </div>
                            <div class="info">
                                <span>{{ lou(comment.lou) }} · {{ comment.time }}</span>
                            </div>
                        </div>
                        <div class="comment-dtl">
                            <p v-html="comment.body"></p>
                            <div class="tool-group">
                                <a :class="['like', comment.liked ? 'active' : '']" @click="like(comment)"
                                    ><i :class="['iconfont', comment.liked ? 'icon-dianzan' : 'icon-fabulous']"></i>
                                    <span
                                        ><b v-show="comment.likes">{{ comment.count_likes + '人' }}</b
                                        >赞</span
                                    ></a
                                >
                                <!-- <a class="reply" @click="toggleReplyComment(comment, index)"><i class="iconfont icon-xinxi"></i> <span>回复</span></a> -->
                                <a href="javascript:;" class="report" @click="report(comment)"
                                    ><span>{{ comment.reported ? '已举报' : '举报' }}</span></a
                                >
                            </div>
                        </div>
                        <blockquote class="sub-comment-list" v-if="comment.replying || comment.reply_comments.length">
                            <div class="sub-comment" v-for="reply in comment.reply_comments" v-if="!reply.hide">
                                <p>
                                    <a :href="'/user/' + reply.user.id">{{ reply.user.name }}</a
                                    >：
                                    <span v-html="reply.body"> </span>
                                </p>
                                <div class="sub-tool-group">
                                    <span>{{ reply.created_at }}</span>
                                    <a class="reply" @click="toggleReplyComment(comment, index, reply)"
                                        ><i class="iconfont icon-xinxi"></i> <span>回复</span></a
                                    >
                                    <a href="javascript:;" class="report" @click="report(reply)"><span>举报</span></a>
                                </div>
                            </div>
                            <div class="more-comment" v-if="comment.reply_comments.length">
                                <a class="add-comment-btn" @click="toggleReplyComment(comment, index)">
                                    <i class="iconfont icon-xie"></i>
                                    <span>添加新评论</span>
                                </a>
                                <span class="line-warp" v-if="comment.reply_comments.length > 3">
                                    <span v-if="!comment.expanded"
                                        >还有{{ comment.reply_comments.length - 3 }}条评论，</span
                                    >
                                    <a v-if="!comment.expanded" @click="openReplyComment(comment)">展开查看</a>
                                    <a v-else @click="packupReplyComment(comment)">收起</a>
                                </span>
                            </div>
                            <reply-comment
                                :is-show="comment.replying"
                                :body="commentBody(comment)"
                                @sendReply="(body) => sendReply(body, comment, index)"
                                @toggle-replycomment="toggleReplyComment(comment, index)"
                            ></reply-comment>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
        <!-- {{-- 分页 --}} -->
        <ul class="paging" v-if="lastPage > 1">
            <li @click="prevPage" v-if="currentPage != 1"><a>上一页</a></li>
            <li v-for="page in lastPage" @click="goPage(page)">
                <a :class="page == currentPage ? 'active' : ''">{{ page }}</a>
            </li>
            <li @click="nextPage" v-if="currentPage != lastPage"><a>下一页</a></li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'Comments',

    props: ['id', 'type', 'authorId', 'commentReplies'],

    mounted() {
        this.loadComments();
    },

    computed: {
        user() {
            return window.user;
        },
        isLogin() {
            return window.user !== undefined;
        },
    },

    methods: {
        sortComments(order) {
            this.order = order;
            switch (order) {
                case 'timeAsc':
                    this.comments.sort(function(a, b) {
                        return a.id - b.id;
                    });
                    break;
                case 'timeDesc':
                    this.comments.sort(function(a, b) {
                        return b.id - a.id;
                    });
                    break;
                default:
                    this.comments.sort(function(a, b) {
                        return b.likes - a.likes;
                    });
                    break;
            }
        },
        showOnlyAuthor() {
            this.isOnlyAuthor = !this.isOnlyAuthor;
            if (this.isOnlyAuthor) {
                this.commentsAll = this.comments;
                this.comments = this.comments.filter((item) => item.user_id == this.authorId);
            } else {
                this.comments = this.commentsAll;
            }
        },
        lou: function(lou) {
            if (lou == 1) return '沙发';
            if (lou == 2) return '板凳';
            return lou + '楼';
        },
        postApiUrl: function() {
            return window.tokenize('/api/comment');
        },
        getApiUrl: function() {
            var api = '/api/comment/' + this.id + '/' + this.type;
            if (window.user) {
                api = '/api/comment/' + this.id + '/' + this.type + '/with-token';
            }
            if (this.currentPage > 1) {
                api += '?page=' + this.currentPage;
            }
            if (window.user) {
                api = window.tokenize(api);
            }
            return api;
        },
        actionApiUrl: function(id, action) {
            return window.tokenize('/api/comment/' + id + '/' + action);
        },
        //写新评论
        postComment(body) {
            //乐观更新
            this.newComment.lou = this.total + 1;
            this.newComment.body = body;
            this.newComment.user = this.user;
            this.newComment.reply_comments = [];
            this.newComment.replying = false;
            this.comments = this.comments.concat(this.newComment);

            var _this = this;
            window.axios.post(this.postApiUrl(), this.newComment).then(function(response) {
                //发布成功后，替换服务器返回数据
                response.data.reply_comments = [];
                response.data.replying = false;
                _this.comments.pop();
                _this.comments = _this.comments.concat(response.data);
            });
        },
        //回复评论
        sendReply(body, comment, index) {
            var _this = this;
            let replyComment = _this.gotReplyComment(body, comment);
            _this.comments[index].reply_comments.push(replyComment);
            window.axios.post(this.postApiUrl(), replyComment).then(function(response) {
                _this.comments[index].reply_comments.pop();
                _this.comments[index].reply_comments.push(response.data);
                _this.toggleReplyComment(comment, index);
            });
        },
        //获取回复内容
        gotReplyComment(body, comment) {
            let replyComment = {};
            replyComment.comment_id = comment.id;
            replyComment.commentable_type = this.type;
            replyComment.commentable_id = this.id;
            replyComment.user = this.user;
            replyComment.body = body;
            (replyComment.time = '刚刚'), (replyComment.is_reply = 1);
            replyComment.likes = 0;
            replyComment.reports = 0;
            return replyComment;
        },
        //回复框开关
        // reply表示回复类型
        // comment.replying  代表评论框是否开启
        // comment.replyUser 代表评论框是否带有@用户
        // comment.replyUserId 代表@用户id
        toggleReplyComment(comment, index, reply) {
            if (this.checkLogin()) {
                if (reply) {
                    if (comment.replying) {
                        if (comment.replyUser) {
                            if (comment.replyUser !== reply.user.name) {
                                comment.replyUser = reply.user.name;
                                comment.replyUserId = reply.user.id;
                            } else {
                                comment.replyUser = null;
                                comment.replyUserId = null;
                                comment.replying = !comment.replying;
                            }
                        } else {
                            comment.replyUser = reply.user.name;
                            comment.replyUserId = reply.user.id;
                        }
                    } else {
                        comment.replyUser = reply.user.name;
                        comment.replyUserId = reply.user.id;
                        comment.replying = !comment.replying;
                    }
                } else {
                    if (comment.replyUser) {
                        comment.replyUser = null;
                        comment.replyUserId = null;
                    } else {
                        comment.replying = !comment.replying;
                    }
                }

                //获取更新后的 DOM
                this.$nextTick(() => {
                    $('.commenTable').each(function() {
                        $(this).atwho(at_config);
                    });
                });

                this.$set(this.comments, index, comment);
            }
        },
        openReplyComment(comment) {
            comment.reply_comments.forEach(function(el, index) {
                if (index >= 3) {
                    el.hide = false;
                }
            });
            this.$set(comment, 'expanded', true);
        },
        packupReplyComment(comment) {
            comment.reply_comments.forEach(function(el, index) {
                if (index >= 3) {
                    el.hide = true;
                }
            });
            this.$set(comment, 'expanded', false);
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadComments();
            }
        },
        nextPage: function() {
            if (this.currentPage < this.lastPage) {
                this.currentPage++;
                this.loadComments();
            }
        },
        goPage(page) {
            this.currentPage = page;
            this.loadComments();
        },

        checkLogin() {
            if (!this.isLogin) {
                window.location.href = '/login';
                return false;
            }
            return true;
        },

        //actions ...
        like: function(comment) {
            if (this.checkLogin()) {
                this.$set(comment, 'liked', !comment.liked);
                window.axios.get(this.actionApiUrl(comment.id, 'like')).then(function(response) {
                    comment.count_likes = response.data.count_likes;
                    comment.liked = response.data.liked;
                });
            }
        },
        report: function(comment) {
            if (this.checkLogin()) {
                this.$set(comment, 'reported', !comment.reported);
                window.axios.get(this.actionApiUrl(comment.id, 'report')).then(function(response) {
                    comment.reports = response.data.reports;
                });
            }
        },
        loadComments: function() {
            var _this = this;
            window.axios.get(this.getApiUrl()).then(function(response) {
                _this.comments = response.data.data;
                //折叠楼中评论3条之后的
                _this.comments.forEach(function(el) {
                    if (el.reply_comments.length > 3) {
                        el.reply_comments.forEach(function(reply, index) {
                            if (index >= 3) {
                                reply.hide = true;
                            }
                        });
                    }
                });
                _this.lastPage = response.data.last_page;
                _this.total = response.data.total;
                //_this.count_likes = response.data.count_likes;
            });
        },
        commentBody: function(comment) {
            var link = '';
            //插入回复用户Dom
            if (comment.replyUser && comment.replyUserId) {
                link =
                    '<span class="atwho-inserted" data-atwho-at-query="@" contenteditable="false"><span data-id="' +
                    comment.replyUserId +
                    '">' +
                    '@' +
                    comment.replyUser +
                    '</span></span>&nbsp;';
            }

            return link;
        },
    },

    data() {
        return {
            currentPage: 1,
            lastPage: null,
            total: 0,
            isOnlyAuthor: false,
            commentsAll: [],
            comments: [],
            order: 'timeAsc',
            newComment: {
                user: {},
                is_reply: null,
                time: '刚刚',
                body: null,
                commentable_id: this.id,
                commentable_type: this.type,
                likes: 0,
                reports: 0,
            },
        };
    },
};
</script>

<style lang="scss">
#comments {
    padding-top: 20px;
    .normal-comment {
        margin-top: 30px;
        .top {
            padding-bottom: 20px;
            font-size: 17px;
            font-weight: 700;
            border-bottom: 1px solid #f0f0f0;
            .btn-base {
                margin: -2px 0 0 10px;
                &.active {
                    background-color: #d96a5f;
                    color: #fff !important;
                    border-color: #d96a5f !important;
                }
            }
            .pull-right {
                a {
                    margin-left: 10px;
                    font-size: 12px;
                    font-weight: 400;
                    color: #969696;
                    display: inline-block;
                    cursor: pointer;
                    &.active {
                        color: #252525;
                    }
                    &:hover {
                        color: #252525;
                    }
                }
            }
        }
        @media screen and (max-width: 600px) {
            .top {
                font-size: 15px;
                .btn-base {
                    padding: 3px 5px;
                }
                .pull-right {
                    a {
                        margin-left: 0;
                    }
                }
            }
        }
        // 评论列表项
        .comment-item {
            padding: 20px 0 30px;
            border-bottom: 1px solid #f0f0f0;
            .comment-dtl {
                p {
                    font-size: 16px;
                    margin: 10px 0;
                    word-break: break-all;
                    a {
                        color: #2b89ca;
                    }
                }
            }
        }
        // 回复楼层
        .sub-comment-list {
            margin-bottom: 0;
            color: #969696;
            border-left: 2px solid #c4c4c4;
            .sub-comment {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px dashed #f0f0f0;
                color: #252525;
                p {
                    margin: 0 0 5px;
                    font-size: 14px;
                    line-height: 1.5;
                    a {
                        color: #2b89ca;
                    }
                }
            }
            .more-comment {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px dashed #f0f0f0;
                font-size: 14px;
                color: #969696;
                &:last-child {
                    margin: 0;
                    padding: 0;
                    border: none;
                }
                i {
                    margin-right: 5px;
                }
                .add-comment-btn {
                    cursor: pointer;
                    &:hover {
                        color: #252525;
                    }
                }
                .line-warp {
                    margin-left: 10px;
                    padding-left: 10px;
                    border-left: 1px solid #d9d9d9;
                    a {
                        color: #2b89ca;
                        cursor: pointer;
                    }
                }
            }
        }
    }
}
</style>
