<template>
    <div id="dplayer"></div>
</template>
<script>
import Hls from 'hls.js';
import DPlayer from 'dplayer';

export default {
    props: ['source'],
    mounted() {
        if (Hls.isSupported()) {
            console.log('hello hls.js isSupported!');
        }
        let options = {
            container: document.getElementById('dplayer'),
            preload: true,
            autoplay: true,
            screenshot: true,
            video: {
                url: this.source,
                type: 'hls',
            },
            pluginOptions: {
                hls: {},
            },
        };
        this.player = new DPlayer(options);
        if (this.source) {
            console.log('开始播放 source:' + this.source);
            this.player.switchVideo({
                url: this.source,
            });
            this.player.play();
        }
    },
    watch: {
        source(newV, oldV) {
            console.log('开始播放 url:' + newV);
            if (this.player) {
                this.player.switchVideo({
                    url: newV,
                });
                this.player.play();
            }
        },
    },
    methods: {},
    data() {
        return {};
    },
};
</script>

<style lang="scss" scoped>
#dplayer {
    width: 100%;
    height: 100%;
}
</style>
