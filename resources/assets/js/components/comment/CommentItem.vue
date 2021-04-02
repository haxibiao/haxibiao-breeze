<template>
    <div class="comment-item" :data-id="comment.id">
        <div class="user-face">
            <a :href="`/user/${comment.user.id}`" target="_blank">
                <div class="app-avatar">
                    <img class="app-avatar-img app-avatar-img-radius" :src="comment.user.avatar" alt="头像" />
                </div>
            </a>
        </div>
        <div class="comment-body">
            <div class="user">
                <a :href="`/user/${comment.user.id}`" target="_blank" class="name">{{ comment.user.name }} </a>
            </div>
            <div class="time">{{ comment.time }}</div>
            <p class="content" v-html="comment.content"></p>
            <div class="info">
                <span :class="['like', comment.liked && 'liked']" @click="likeHandler">
                    <i></i>
                    <span>{{ comment.count_likes }}</span>
                </span>
                <span class="reply btn-hover" @click="toggleInput()">回复</span>
                <div class="operation more-operation">
                    <el-dropdown @command="handleCommand" trigger="click">
                        <div class="spot"></div>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="report">举报</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </div>
            <div class="reply-box">
                <CommentSubItem v-for="item in comment.reply_comments" :key="item.id" :comment="item" />
            </div>
            <transition name="fade">
                <comment-input
                    v-show="inputVisible"
                    ref="commentInput"
                    :comment="comment"
                    :commentable-id="commentableId"
                    :commentable-type="commentableType"
                    @update="updateComments"
                />
            </transition>
        </div>
    </div>
</template>

<script>
import serviceApi from './api';
import CommentInput from './CommentInput';
import CommentSubItem from './CommentSubItem';
export default {
    components: {
        CommentInput,
        CommentSubItem,
    },
    props: {
        comment: {
            type: Object,
            required: true,
        },
        commentableId: {
            type: [String, Number],
            required: true,
        },
        commentableType: {
            type: String,
            default: function() {
                return 'articles';
            },
        },
    },
    computed: {
        user() {
            return window.user || {};
        },
    },
    mounted() {
        GLOBAL.vueBus.$on('comment.toggleInput', ({ comment, subComment }) => {
            if (comment.id === this.comment.id) {
                this.inputVisible = true;
                if (subComment && typeof subComment === 'object') {
                    this.$refs.commentInput.body = '';
                    this.$refs.commentInput.replyingUser = subComment.user;
                    this.$refs.commentInput.placeholder = `@${subComment.user.name}`;
                } else {
                    this.$refs.commentInput.body = '';
                    this.$refs.commentInput.placeholder = '';
                    this.$refs.commentInput.replyingUser = null;
                }
            } else {
                this.inputVisible = false;
                this.$refs.commentInput.body = '';
                this.$refs.commentInput.placeholder = '';
                this.$refs.commentInput.replyingUser = null;
            }
        });
    },
    methods: {
        toggleInput(subComment) {
            GLOBAL.vueBus.$emit('comment.toggleInput', { comment: this.comment, subComment });
            // this.inputVisible = !this.inputVisible;
        },
        toggleLike() {
            this.comment.liked ? this.comment.count_likes-- : this.comment.count_likes++;
            this.comment.liked = !this.comment.liked;
        },
        likeHandler() {
            if (!this.user.token) {
                window.location.href = '/login';
                return;
            }
            this.toggleLike();
            serviceApi['comment/like']({
                variables: {
                    id: this.comment.id,
                },
            }).catch(err => {
                console.log('comment/like：err', err);
                this.toggleLike();
            });
        },
        handleCommand(c) {
            const handler = {
                report: this.reportHandler,
            };
            handler[c]();
        },
        reportHandler() {
            if (!this.user.token) {
                window.location.href = '/login';
                return;
            }
            serviceApi['comment/report']({
                variables: {
                    id: this.comment.id,
                },
            })
                .then(() => {
                    this.$message({
                        showClose: true,
                        message: '举报成功',
                        type: 'success',
                    });
                })
                .catch(err => {
                    console.log('comment/report：err', err);
                    this.$message({
                        showClose: true,
                        message: '举报失败',
                        type: 'error',
                    });
                });
        },
        //更新评论
        updateComments(subComment) {
            this.comment.reply_comments.splice(0, 0, subComment);
        },
    },
    data() {
        return {
            inputVisible: false,
        };
    },
};
</script>

<style lang="scss" scoped>
.comment-item {
    &:last-child {
        .comment-body {
            margin-bottom: 0;
            border-bottom: none;
        }
    }
    .user-face {
        float: left;
        margin: 0 0 0 5px;
        position: relative;
        .app-avatar {
            display: block;
            width: 40px;
            height: 40px;
            position: relative;
            background-image: url(data:image/gif;base64,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);
            background-size: cover;
            border-radius: 50%;
            border-width: 1px;
            border-color: #dcdee2;
            .app-avatar-img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
            .app-avatar-img-radius {
                border-radius: 50%;
            }
        }
    }
    .comment-body {
        position: relative;
        margin-left: 65px;
        padding: 0 0 14px;
        margin-bottom: 20px;
        border-bottom: 1px solid #dcdee2;
        .user {
            font-size: 14px;
            font-weight: 700;
            line-height: 18px;
            padding-bottom: 4px;
            display: block;
            word-wrap: break-word;
            position: relative;
            .name {
                color: #808695;
            }
            .vip-color {
                color: #fe7705;
            }
        }
        .time {
            color: #99a2aa;
            line-height: 18px;
            font-size: 12px;
            margin-top: 4px;
        }
        .content {
            line-height: 22px;
            padding: 2px 0;
            margin-top: 8px;
            font-size: 15px;
            text-shadow: none;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .info {
            color: #99a2aa;
            line-height: 26px;
            font-size: 12px;
            & > span {
                margin-right: 20px;
                display: inline-block;
                i {
                    display: inline-block;
                    width: 14px;
                    height: 14px;
                    margin-right: 5px;
                }
            }
            .reply {
                &.btn-hover {
                    padding: 0 5px;
                    border-radius: 4px;
                    margin-right: 15px;
                    cursor: pointer;
                    &:hover {
                        color: #00a1d6;
                        background: #e5e9ef;
                    }
                }
            }
            .like {
                cursor: pointer;
                i {
                    display: inline-block;
                    width: 14px;
                    height: 14px;
                    vertical-align: text-top;
                    margin-right: 5px;
                    background: url(data:image/png;base64,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)
                        no-repeat;
                    background-position: -153px -25px;
                }
                &.liked,
                &hover {
                    i {
                        background-position: -218px -25px;
                    }
                }
            }
            .operation {
                position: relative;
                width: 18px;
                float: right;
                margin-top: 5px;
                margin-right: 0;
                &.more-operation {
                    .spot {
                        width: 18px;
                        height: 18px;
                        cursor: pointer;
                        background: url(data:image/png;base64,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)
                            no-repeat;
                        background-position: -151px -280px;
                    }
                }
            }
        }
    }
}
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease-in-out;
    overflow: hidden;
}
.fade-enter {
    height: 0;
    opacity: 0;
}
.fade-enter-to {
    height: 107px;
    opacity: 1;
}
.fade-leave {
    height: 127px;
    opacity: 1;
}
.fade-leave-to {
    height: 0;
    opacity: 0;
}
</style>
