<template>
    <div :class="['comment-send', comment && 'small', !user.token && 'no-login']">
        <div class="user-face">
            <img class="user-head" :src="user.avatar || '/images/movie/noavatar.png'" />
        </div>
        <div class="textarea-container clearfix">
            <div class="baffle-wrap">
                <div class="baffle">
                    请先
                    <a class="app-btn btn-mini-Login" href="/login" target="_blank">登录</a>后发表评论 (・ω・)
                </div>
            </div>
            <textarea
                v-model="body"
                :disabled="loading"
                cols="80"
                name="msg"
                rows="5"
                :placeholder="placeholder || '发条友善的评论'"
                class="ipt-txt"
            ></textarea>
            <button
                v-on:click="onSend"
                type="submit"
                :class="['comment-submit', loading && 'loading']"
                :disabled="body ? false : 'disabled'"
            >
                {{ loading ? '正在提交' : '发表评论' }}
            </button>
        </div>
    </div>
</template>

<script>
import serviceApi from './api';

export default {
    props: {
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
        comment: {
            type: Object,
        },
    },
    computed: {
        user() {
            return window.user || {};
        },
    },
    methods: {
        onSend() {
            this.loading = true;
            const data = {
                body: this.body,
                user: this.user,
                commentable_id: this.commentableId,
                commentable_type: this.commentableType,
            };
            if (this.comment && typeof this.comment === 'object') {
                data.comment_id = this.comment.id;
            }
            if (this.replyingUser) {
                data.user = this.replyingUser;
            }
            this.createComment(data);
        },
        //写新评论
        createComment(data) {
            serviceApi['comment/create']({
                params: {
                    api_token: this.user.token,
                },
                data,
            })
                .then(response => {
                    console.log('comment/create：response', response);
                    if (response.data && typeof response.data === 'object') {
                        const newComment = response.data;
                        if (newComment) {
                            this.body = null;
                            this.$message({
                                showClose: true,
                                message: '评论发表成功',
                                type: 'success',
                            });
                            if (data.comment) {
                                GLOBAL.vueBus.$emit('comment.reply', newComment);
                            } else {
                                GLOBAL.vueBus.$emit('comment.new', newComment);
                            }
                        }
                    }
                })
                .catch(err => {
                    console.log('comment/create：err', err);
                    this.$message({
                        showClose: true,
                        message: '评论发表失败',
                        type: 'error',
                    });
                })
                .then(() => {
                    this.loading = false;
                });
        },
    },
    data() {
        return {
            body: null,
            loading: false,
            replyingUser: null,
            placeholder: '',
        };
    },
};
</script>

<style lang="scss" scoped>
.comment-send {
    position: relative;
    margin: 10px 0;
}
.user-face {
    float: left;
    position: relative;
    .user-head {
        width: 48px;
        height: 48px;
        border-radius: 50%;
    }
}
.textarea-container {
    position: relative;
    margin-left: 68px;
    .baffle {
        display: none;
    }
    & > textarea {
        font-size: 12px;
        display: inline-block;
        box-sizing: border-box;
        background-color: #f4f5f7;
        border: 1px solid #e5e9ef;
        overflow: auto;
        border-radius: 4px;
        color: #555;
        width: 100% !important;
        height: 80px;
        transition: 0s;
        padding: 5px 10px;
        line-height: normal;
    }
    .comment-submit {
        position: relative;
        float: right;
        width: 80px;
        height: auto;
        margin-top: 10px;
        padding: 5px 2px;
        font-size: 14px;
        color: #fff;
        border-radius: 4px;
        text-align: center;
        min-width: 60px;
        vertical-align: top;
        cursor: pointer;
        background-color: #29b6f6;
        border: 1px solid #29b6f6;
        transition: 0.1s;
        user-select: none;
        outline: none;
        &.loading {
            cursor: progress;
            background-color: #e5e9ef !important;
            border-color: #e5e9ef !important;
            color: #b8c0cc !important;
        }
    }
}
.comment-send.no-login {
    .textarea-container {
        .baffle {
            display: block;
            position: absolute;
            z-index: 102;
            width: 100%;
            top: 0;
            line-height: 80px;
            font-size: 12px;
            border-radius: 4px;
            text-align: center;
            color: #777;
            background-color: #e5e9ef;
            overflow: hidden;
            .app-btn {
                padding: 4px 9px;
                margin: 0 3px;
                color: #fff;
                background-color: #29b6f6;
                border-radius: 4px;
                cursor: pointer;
            }
        }
        textarea {
            background-color: #e5e9ef;
        }
        .comment-submit {
            cursor: default;
            background-color: #e5e9ef !important;
            border-color: #e5e9ef !important;
            color: #b8c0cc !important;
        }
    }
}
.comment-send.small {
    margin: 10px 0 0;
    &.no-login {
        .textarea-container {
            .baffle {
                line-height: 64px;
            }
        }
    }
    .user-face {
        .user-head {
            width: 40px;
            height: 40px;
        }
    }
    .textarea-container {
        margin-left: 60px;
        & > textarea {
            height: 64px;
        }
        .comment-submit {
            width: 64px;
        }
    }
}
</style>
