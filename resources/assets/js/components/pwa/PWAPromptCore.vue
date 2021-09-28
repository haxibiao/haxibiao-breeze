<template>
  <PWAPromptModal
    v-if="debug || (belowMaxVisits && aboveMinVisits)"
    :appName="appName"
    :logo="logo"
    :delay="delay"
    :copyBody="copyBody"
    :copyAddHomeButtonLabel="copyAddHomeButtonLabel"
    :copyShareButtonLabel="copyShareButtonLabel"
    :copyClosePrompt="copyClosePrompt"
    :permanentlyHideOnDismiss="permanentlyHideOnDismiss"
    :promptData="promptData"
    :maxVisits="timesToShow + promptOnVisit"
    :onClose="onClose"
  />
</template>

<script>
import PWAPromptModal from "./PWAPromptModal";

const deviceCheck = () => {
  const isiOS = /iphone|ipad|ipod/.test(
    window.navigator.userAgent.toLowerCase()
  );
  const isiPadOS =
    navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1;
  const isStandalone =
    "standalone" in window.navigator && window.navigator.standalone;
  console.log("deviceCheck isiOS=" + isiOS);
  return (isiOS || isiPadOS) && !isStandalone;
};

export default {
  components: {
    PWAPromptModal,
  },
  props: {
    appName:{ type: String },
    logo:{ type: String },
    timesToShow: { type: Number, default: 1 },
    promptOnVisit: { type: Number, default: 1 },
    permanentlyHideOnDismiss: { type: Boolean, default: false },
    copyBody: {
      type: String,
      default:
        "将该网站添加到桌面以便离线时都可以随时使用，无需通过应用商店安装与下载",
    },
    copyShareButtonLabel: {
      type: String,
      default: "1）点击底部工具栏“分享”按钮",
    },
    copyAddHomeButtonLabel: {
      type: String,
      default: "2）在弹出框中选择“添加到主屏幕”即可",
    },
    copyClosePrompt: {
      type: String,
      default: "关闭",
    },
    delay: { type: Number, default: 1000 },
    debug: { type: Boolean, default: true },
    onClose: { type: Function, default: () => {} },
  },

  mounted() {
    let promptData = this.promptData
      ? this.promptData
      : JSON.parse(localStorage.getItem("iosPwaPrompt"));

    if (promptData === null) {
      promptData = { isiOS: deviceCheck(), visits: 0 };
      localStorage.setItem("iosPwaPrompt", JSON.stringify(promptData));
    }

    if (promptData.isiOS || this.debug) {
      this.aboveMinVisits = promptData.visits + 1 >= this.promptOnVisit;
      this.belowMaxVisits =
        promptData.visits + 1 < this.promptOnVisit + this.timesToShow;

      if (this.belowMaxVisits || this.debug) {
        localStorage.setItem(
          "iosPwaPrompt",
          JSON.stringify({
            ...promptData,
            visits: promptData.visits + 1,
          })
        );
      }
    }

    this.promptData = promptData;
    console.log("promptData.visits", promptData.visits);
  },

  data() {
    return {
      promptData: null,
      aboveMinVisits: false,
      belowMaxVisits: true,
    };
  },
};
</script>
