<template>
    <div class="modal fade modal-post">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal" aria-label="Close">×</button>
                    <h4 class="modal-title">发布动态</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="/post/new" ref="postForm" enctype="multipart/form-data">
                        <div class="textarea-box img-upload-field">
                            <input type="hidden" name="_token" v-model="token" />
                            <textarea
                                name="body"
                                placeholder="再说点什么..."
                                v-model="description"
                                maxlength="500"
                            ></textarea>
                            <span class="word-count">{{ description.length }}/500</span>
                            <div class="img-preview-item clearfix" v-for="image in selectedImgs">
                                <img :src="image.url" alt class="as-height" />
                                <div class="img-del" @click="deleteImg(image)">
                                    <i class="iconfont icon-cha"></i>
                                </div>
                            </div>
                            <div v-if="videoPath" class="modal-video-box">
                                <div class="video-content">
                                    <video class="video" :src="videoPath" controls ref="video_ele"></video>
                                    <div class="progress_box" ref="progress_box">
                                        <loading :progress="progress"></loading>
                                    </div>
                                </div>
                                <div class="video-del" @click="deleteVideo">
                                    <i class="iconfont icon-cha"></i>
                                </div>
                            </div>
                            <div class="img-upload-btn">
                                <i class="iconfont icon-icon20"></i>
                                <div class="img-file">
                                    <input
                                        type="file"
                                        @change="uploadOnFileSelected"
                                        :accept="fileFormat"
                                        multiple
                                        ref="upload"
                                        name="video"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="tip-text">
                            <p class="desc">1、上传视频超过100M的视频请耐心等待哦，若上传失败建议压缩至100M以内</p>
                            <p class="desc">2、当前视频上传仅支持MP4格式</p>
                        </div>
                        <div v-if="alertInfo" class="alert alert-info alert-dismissible" role="alert">
                            亲,视频名称不能包含特殊字符哟！
                        </div>
                        <div>
                            <category-select placeholder="选择专题"></category-select>
                        </div>
                        <div class="img-selector">
                            <div :class="['ask-img-header', selectedImgs.length > 0 ? 'bigger' : '']">
                                <span class="desc">（非必选）</span>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" :value="user.id" />
                        <input v-for="img in selectedImgs" name="image_urls[]" type="hidden" :value="img.url" />
                        <input v-if="video_id" name="video_id" type="hidden" :value="video_id" />
                    </form>
                </div>
                <footer class="clearfix">
                    <button class="btn-base btn-handle btn-md pull-right" @click="submit">提交</button>
                </footer>
            </div>
        </div>
    </div>
</template>
<script>
import Dropzone from '../../plugins/Dropzone';

export default {
    name: 'ModalPost',

    props: [],

    computed: {
        token() {
            return window.csrf_token;
        },
        user() {
            return window.user;
        },
        selectedImgs() {
            return _.filter(this.imgItems, ['selected', 1]);
        },
    },

    mounted() {
        Dropzone($('.img-upload-field')[0], this.dragDropUpload);
    },

    methods: {
        uploadOnFileSelected(e) {
            for (var i = 0; i < e.target.files.length; i++) {
                let firstFileObj = e.target.files[0];

                //视频:当第一个是视频，就按视频处理
                if (firstFileObj.type.indexOf('video') != -1) {
                    let _this = this;
                    this.fileFormat = '.avi,.wmv,.mpeg,.mp4,.mov,.mkv,.flv,.f4v,.m4v,.rmvb,.rm,.3gp,.dat,.ts,.mts,.vob';
                    //只能一次上传一个视频
                    this.videoObj = firstFileObj;
                    var regEn = /[`~!@#$%^&*()+<>?:"{},\/;'[\]]/im,
                        regCn = /[！#￥（——）：；“”‘，|《。》？【】[\]]/im;

                    if (regEn.test(this.videoObj.name) || regCn.test(this.videoObj.name)) {
                        this.alertInfo = true;
                        setTimeout(() => {
                            _this.alertInfo = false;
                        }, 3000);
                        return false;
                    }
                    //选择1个视频后，不能再添加图片或者视频
                    this.allowMore = false;
                    let reader = new FileReader();
                    reader.readAsDataURL(e.target.files[0]);
                    reader.onload = function(e) {
                        _this.videoPath = e.target.result;
                    };
                    if (this.videoObj && this.videoObj.length >= 1) {
                        break;
                    }
                    this.uploadVideoToVod(this.videoObj);
                }

                //图片：当第一个是图片
                if (firstFileObj.type.indexOf('image') != -1) {
                    this.fileFormat =
                        '.bmp,.jpg,.png,.tiff,.gif,.pcx,.tga,.exif,.fpx,.svg,.psd,.cdr,.pcd,.dxf,.ufo,.eps,.ai,.raw,.WMF,.webp';
                    if (this.filesCount >= 9) {
                        break;
                    }
                    let fileObj = e.target.files[i];
                    this.uploadImage(fileObj);
                    this.filesCount++;
                }
            }
        },
        submit() {
            this.$refs.postForm.submit();
        },
        dragDropUpload(fileObj, params) {
            if (this.filesCount >= 9) {
                return;
            }
            this.uploadImage(fileObj);
            this.filesCount++;
        },
        uploadImage(fileObj) {
            var api = window.tokenize('/api/image');
            var _this = this;
            let formdata = new FormData();
            formdata.append('from', 'post');
            formdata.append('photo', fileObj);
            let config = {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            };
            window.axios.post(api, formdata, config).then(function(res) {
                var image = res.data;
                _this.imgItems.push({
                    url: image.url,
                    id: image.id,
                    selected: 1,
                });
            });
        },
        uploadVideo(fileObj) {
            var api = window.tokenize('/api/video');
            var _this = this;
            let formdata = new FormData();
            formdata.append('from', 'post');
            formdata.append('video', fileObj);
            let config = {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            };
            window.axios.post(api, formdata, config).then(function(res) {
                let video = res.data;
                _this.video_id = video.id;
            });
        },
        uploadVideoToVod(videoFile) {
            var _this = this;
            _this.progress = 0;
            console.log(videoFile);

            console.log('start upload to qcvod ...');
            qcVideo.ugcUploader.start({
                videoFile: videoFile, //视频，类型为 File
                getSignature: function(callback) {
                    $.ajax({
                        url: window.haxiyun_endpoint + '/api/video/vod/sign/' + window.app, //获取客户端上传签名的 URL
                        type: 'GET',
                        success: function(signature) {
                            //result 是派发签名服务器的回包
                            //假设回包为 { "code": 0, "signature": "xxxx"  }
                            //将签名传入 callback，SDK 则能获取这个上传签名，用于后续的上传视频步骤。
                            callback(signature);
                        },
                    });
                },
                error: function(result) {
                    console.log('上传失败的原因：' + result.msg);
                },
                progress: function(result) {
                    let progress = parseInt(result.curr * 100);
                    console.log('上传进度:', progress);
                    _this.progress = progress;
                },
                finish: function(result) {
                    $(_this.$refs.upload).val('');
                    //上传成功时的回调函数
                    $(_this.$refs.video_ele).css({ opacity: '1' });
                    console.log('上传结果的fileId：' + result.fileId);
                    console.log('上传结果的视频名称：' + result.videoName);
                    console.log('上传结果的视频地址：' + result.videoUrl);
                    var _vm = _this;
                    $.ajax({
                        url: window.tokenize('/api/video?from=qcvod'),
                        type: 'POST',
                        data: result,
                        success: function(video) {
                            console.log(video);
                            _vm.video_id = video.id;
                        },
                    });

                    // alert('视频上传成功');
                },
            });
        },
        deleteImg(image) {
            image.selected = 0;
            this.imgItems = this.selectedImgs;
            this.filesCount--;
            if (this.imgItems.length < 1) {
                this.fileFormat = true;
            }
        },
        deleteVideo() {
            this.videoPath = null;
            this.qcvod_id = null;
            this.allowMore = true;
            if (!this.videoPath) {
                this.fileFormat = true;
            }
        },
    },

    data() {
        return {
            video_id: null,
            progress: 0,
            counter: 1,
            balance: window.user.balance,
            query: null,
            alertInfo: false,
            description: '',
            filesCount: 0,
            qcvod_id: null,
            videoPath: null,
            videoObj: null,
            allowMore: true,
            fileFormat: true,
            imgItems: [],
        };
    },
};
</script>
<style lang="scss">
.modal-backdrop {
    position: static !important;
}

.modal-post {
    @media (max-width: 1366px) {
        .modal-dialog {
            top: 47% !important;
            .textarea-box {
                padding-bottom: 30px !important;
            }
            .ask-img-header {
                padding-bottom: 0 !important;
            }
        }
    }
    .modal-dialog {
        padding-bottom: 20px;
        max-width: 720px !important;
        top: 42%;
        .modal-content {
            .modal-body {
                padding: 25px 40px 0px;
                max-height: 660px;
                overflow: auto;
                .input-question {
                    margin: 10px 0;
                }
                .textarea-box {
                    position: relative;
                    margin-bottom: 20px;
                    border: 1px solid #f0f0f0;
                    padding-bottom: 50px;
                    > textarea {
                        height: 180px;
                        background-color: #fff;
                        border: none;
                        margin-bottom: 15px;
                    }
                    textarea::-webkit-scrollbar {
                        width: 4px;
                    }
                    textarea::-webkit-scrollbar-thumb {
                        border-radius: 10px;
                        -webkit-box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
                        background: rgba(217, 106, 95, 0.9);
                    }
                    .img-preview-item {
                        border: 1px solid #e8e8e8;
                        margin: 5px;
                        width: 90px;
                        height: 90px;
                        display: inline-block;
                        position: relative;
                        overflow: hidden;
                        vertical-align: middle;
                        .as-height {
                            height: 100%;
                        }
                        .img-del {
                            width: 18px;
                            height: 18px;
                            position: absolute;
                            z-index: 2;
                            top: 0;
                            right: 0;
                            background-color: rgba(0, 0, 0, 0.5);
                            border-radius: 0 0 0 4px;
                            padding: 1px;
                            cursor: pointer;
                            text-align: center;
                            line-height: 18px;
                            i {
                                font-size: 14px;
                                color: white;
                            }
                        }
                    }
                    .modal-video-box {
                        position: relative;
                        width: 310px;
                        height: 174px;
                        overflow: hidden;
                        display: inline-block;
                        vertical-align: middle;
                        text-align: center;
                        background: #000;
                        margin-left: 5px;
                        .video-content {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            display: inline-block;
                        }
                        .video {
                            height: 300px;
                            opacity: 0.2;
                        }
                        .progress_box {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                        }
                        .video-del {
                            width: 18px;
                            height: 18px;
                            position: absolute;
                            z-index: 2;
                            top: 0;
                            right: 0;
                            background-color: rgba(0, 0, 0, 0.5);
                            padding: 1px;
                            cursor: pointer;
                            text-align: center;
                            line-height: 18px;
                            color: #fff;
                        }
                    }

                    .img-upload-btn {
                        position: relative;
                        text-align: center;
                        width: 90px;
                        height: 90px;
                        line-height: 80px;
                        border: 1px solid #e8e8e8;
                        display: inline-block;
                        vertical-align: middle;
                        margin-left: 15px;
                        border-radius: 4px;
                        i {
                            font-size: 56px;
                            color: #d8d8d8;
                        }
                        .img-click-here,
                        .img-limit {
                            font-size: 14px;
                            color: #2b89ca;
                            display: block;
                            margin-top: 16px;
                            line-height: 1;
                        }
                        .img-file {
                            position: absolute;
                            overflow: hidden;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            cursor: pointer;
                            input {
                                width: 100%;
                                height: 100%;
                                opacity: 0;
                                cursor: pointer;
                            }
                        }
                        .img-limit {
                            color: #969696;
                            margin-top: 12px;
                        }
                    }
                }
                .tip-text {
                    padding-bottom: 10px;
                    .desc {
                        font-size: 12px;
                    }
                }
            }
        }
    }
    .multiselect__content-wrapper {
        z-index: 2;
    }
}
</style>
