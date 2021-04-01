<template>
    <div id="comment_module" class="comment-wrapper">
        <comment-send ref="commentSend" @submitComment="createComment" />
        <div class="module-head">
            <span class="count_comment">{{ totalComments || 0 }}</span>
            <span>评论</span>
            <div class="tabs-order">
                <ul class="clearfix">
                    <li
                        data-order="3"
                        :class="['only-author', order == 'author' && 'on']"
                        v-on:click="changeCommentsOrder('author')"
                    >
                        只看作者
                    </li>
                    <li data-order="1" :class="{ on: order == 'likes' }" v-on:click="changeCommentsOrder('likes')">
                        按热度排序
                    </li>
                    <li data-order="2" :class="{ on: order == 'time' }" v-on:click="changeCommentsOrder('time')">
                        按时间排序
                    </li>
                </ul>
            </div>
        </div>
        <div class="comment-container">
            <div class="comment-box">
                <div class="comment-list">
                    <comment-item v-for="comment in comments" :key="comment.id" :comment="comment" />
                </div>
                <pagination :count="Number(totalComments)" :offset="Number(pageOffset)" :current.sync="currentPage" />
                <!-- <comment-send v-if="totalComments>10" ref="commentSend" @submitComment="createComment" /> -->
            </div>
        </div>
    </div>
</template>

<script>
import commentApi from './api';
import CommentItem from './CommentItem';
import CommentSend from './CommentSend';
import Pagination from './Pagination';

export default {
    components: {
        CommentItem,
        CommentSend,
        Pagination,
    },
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
        type: {
            type: String,
            default: function() {
                return 'articles';
            },
        },
        authorId: {
            type: [String, Number],
            required: true,
        },
        countComments: {
            type: [String, Number],
            default: function() {
                return 0;
            },
        },
        pageOffset: {
            type: Number,
            default: function() {
                return 5;
            },
        },
    },
    created() {
        this.fetchData();
    },
    computed: {
        user() {
            return window.user || {};
        },
    },
    methods: {
        // 排序
        changeCommentsOrder(order) {
            this.order = order;
            this.currentPage = 1;
            this.comments = [];
            this.fetchData();
        },
        // 获取评论数据
        fetchData() {
            this.loading = true;
            commentApi['comment/query']({
                variables: {
                    id: this.id,
                    type: this.type,
                },
                params: {
                    order: this.order,
                    page: this.currentPage,
                    api_token: this.user.token,
                },
            })
                .then(function(response) {
                    if (response && response.data && typeof response.data === 'object') {
                        this.comments = response.data.data;
                        this.totalComments = response.data.total;
                    }
                })
                .catch(e => {})
                .then(() => {
                    this.loading = false;
                });
        },
        //写新评论
        createComment(body) {
            commentApi['comment/create']({
                variables: {
                    id: this.id,
                    type: this.type,
                },
                params: {
                    api_token: this.user.token,
                },
                data: {
                    body,
                    user: this.user,
                },
            })
                .then(response => {
                    if (response && response.data && typeof response.data === 'object') {
                        const newComment = response.data.data;
                        if (newComment) {
                            this.comments = [newComment, ...this.comments];
                        }
                    }
                })
                .catch(e => {})
                .then(() => {
                    this.$refs.commentSend.submitted();
                });
        },
    },
    watch: {
        currentPage(newV, oldV) {
            if (newV) {
                this.fetchData();
            }
        },
    },
    data() {
        return {
            order: 'likes',
            currentPage: 1,
            comments: [],
            totalComments: this.countComments,
            loading: false,
        };
    },
};
</script>

<style lang="scss" scoped>
.comment-wrapper {
    padding-top: 20px;
    border-top: 1px solid #e5e9ef;
}
.module-head {
    font-size: 18px;
    line-height: 24px;
    color: #222;
    padding: 12px 0;
    margin: 24px 0;
    border-bottom: 1px solid #e5e9ef;
    .count_comment {
        margin-right: 10px;
    }
    .tabs-order {
        float: right;
        li {
            background-color: transparent;
            border-radius: 0;
            border: 0;
            margin-right: 16px;
            position: relative;
            float: left;
            cursor: pointer;
            line-height: 24px;
            font-size: 14px;
            font-weight: 700;
            color: #222;
            &.on {
                color: #29b6f6;
            }
        }
        .only-author {
            &.on {
                color: #fb7299;
            }
        }
    }
}
.comment-container {
    position: relative;
}
.comment-box {
    font-family: Microsoft YaHei, Arial, Helvetica, sans-serif;
    font-size: 0;
    zoom: 1;
    min-height: 100px;
    background: #fff;
}
.comment-list {
    padding: 20px 0;
}
</style>
