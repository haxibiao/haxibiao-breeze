<template>
  <PWAPromptCore
    :debug="debug"
    :delay="delay"
    :copyTitle="copyTitle"
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
    "debug",
    "delay",
    "copyTitle",
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
