<template>
    <div id="comment_module" class="comment-wrapper">
        <comment-input :commentable-id="id" :commentable-type="type" @update="updateComments" />
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
                <div v-if="loading" style="margin:20px 5px">
                    <loading-more />
                </div>
                <div v-else class="comment-list">
                    <comment-item
                        v-for="comment in comments"
                        :key="comment.id"
                        :comment="comment"
                        :commentable-id="id"
                        :commentable-type="type"
                    />
                </div>
                <pagination :totalPage="Number(lastPage)" :current.sync="currentPage" />
                <comment-input v-if="totalComments >= 5" :commentable-id="id" :commentable-type="type" />
            </div>
        </div>
    </div>
</template>

<script>
import serviceApi from './api';
import CommentItem from './CommentItem';
import CommentInput from './CommentInput';
import LoadingMore from './LoadingMore';
import Pagination from './Pagination';

export default {
    components: {
        CommentItem,
        CommentInput,
        LoadingMore,
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
            serviceApi['comment/query']({
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
                    console.log('comment/query：response', response);
                    if (response && response.data && typeof response.data === 'object') {
                        this.comments = response.data.data;
                        this.totalComments = response.data.total;
                        this.lastPage = response.data.last_page;
                    }
                })
                .catch(e => {
                    console.log('comment/query：err', e);
                })
                .then(() => {
                    this.loading = false;
                });
        },
        //更新评论
        updateComments(comment) {
            this.comments.splice(0, 0, comment);
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
            loading: false,
            order: 'likes',
            currentPage: 1,
            lastPage: 1,
            comments: [],
            totalComments: this.countComments,
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
    padding: 20px 0 0;
}
</style>
