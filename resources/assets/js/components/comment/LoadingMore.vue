<template>
    <div class="comment-placeholder">
        <div class="image"></div>
        <div class="content">
            <div class="title"></div>
            <div class="text"></div>
            <div class="text animation-delay"></div>
            <div class="meta">
                <div class="read"></div>
                <i class="iconfont icon-03xihuan"></i>
                <div class="small"></div>
                <i class="iconfont icon-svg37"></i>
                <div class="small"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LoadingMore',

    data() {
        return {};
    },
};
</script>

<style lang="scss">
.comment-placeholder {
    width: 100%;
    position: relative;
    .image {
        float: left;
        width: 40px;
        height: 40px;
        border-radius: 20px;
        background-color: #eaeaea;
    }
    .content {
        width: 100%;
        padding-left: 65px;
        .title,
        .text,
        .animation-delay {
            background-color: #eaeaea;
        }
        .title {
            width: 50%;
            height: 20px;
            margin-bottom: 12px;
        }
        .text {
            width: 100%;
            height: 16px;
            margin-bottom: 8px;
            animation: loading 0.5s ease-in-out infinite alternate;
        }
        .animation-delay {
            animation: loading 0.5s ease-in-out -0.5s infinite alternate;
        }
        .meta {
            height: 16px;
            div {
                display: inline-block;
                height: 100%;
                background-color: #eaeaea;
            }
            i {
                font-size: 16px;
                color: #eaeaea;
                margin-right: 4px;
            }
            .read {
                width: 15%;
                margin-right: 4px;
            }
            .small {
                width: 10%;
                margin-right: 4px;
            }
        }
    }
}
@keyframes loading {
    from {
        width: 100%;
    }
    to {
        width: 50%;
    }
}
</style>
