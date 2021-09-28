<template>
  <PWAPromptCore
    :appName="appName"
    :logo="logo"
    :debug="debug"
    :delay="delay"
    :copyBody="copyBody"
    :promptOnVisit="promptOnVisit"
    :timesToShow="timesToShow"
    :copyClosePrompt="copyClosePrompt"
    :permanentlyHideOnDismiss="permanentlyHideOnDismiss"
  />
</template>

<script>
import PWAPromptCore from "./PWAPromptCore";
export default {
  components: {
    PWAPromptCore,
  },
  props: [
    "appName",
    "logo",
    "debug",
    "delay",
    "copyBody",
    "promptOnVisit",
    "timesToShow",
    "copyClosePrompt",
    "permanentlyHideOnDismiss",
  ],
  mounted() {
    console.log("copyClosePrompt", this.copyClosePrompt);
  },
};
</script>

<style></style>
