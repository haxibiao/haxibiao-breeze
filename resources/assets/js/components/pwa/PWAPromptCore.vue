<template>
  <PWAPromptModal
    v-if="debug || (belowMaxVisits && aboveMinVisits)"
    :delay="delay"
    :copyTitle="copyTitle"
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
    timesToShow: { type: Number, default: 1 },
    promptOnVisit: { type: Number, default: 1 },
    permanentlyHideOnDismiss: { type: Boolean, default: false },
    copyTitle: {
      type: String,
      default: '添加"极速版APP"到主屏幕',
    },
    copyBody: {
      type: String,
      default:
        "本网站有极速版APP功能. 把她添加到主屏幕，可以全屏模式和离线模式访问，速度更快.",
    },
    copyShareButtonLabel: {
      type: String,
      default: "1) 点击 下面中间的'分享' 按钮.",
    },
    copyAddHomeButtonLabel: {
      type: String,
      default: "2) 点击 '添加到主屏幕'.",
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
