<template>
  <div>
    <div
      :class="['pwaPromptOverlay', visibilityClass, iOSClass, 'iOSPWA-overlay']"
      aria-label="Close"
      role="button"
      @click="dismissPrompt"
      @transitionEnd="onTransitionOut"
    />
    <div
      :class="['pwaPrompt', visibilityClass, iOSClass, 'iOSPWA-container']"
      aria-describedby="pwa-prompt-description"
      aria-labelledby="pwa-prompt-title"
      role="dialog"
      @transitionEnd="onTransitionOut"
    >
      <div :class="['pwaPromptHeader', 'iOSPWA-header']">
        <div :class="['pwaPromptHeaderInfo']">
          <img :src="logo" :class="['pwaPromptHeaderLogo']" />
          <p id="pwa-prompt-title" :class="['pwaPromptTitle', 'iOSPWA-title']">
            添加“{{ appName }}”到桌面
          </p>
        </div>
        <button
          :class="['pwaPromptCancel', 'iOSPWA-cancel']"
          @click="dismissPrompt"
        >
          <!-- {{ copyClosePrompt }} -->
          <CloseIcon
            :class="['pwaPromptShareIcon', 'iOSPWA-step1-icon']"
            :modern="isiOS13AndUp"
          />
        </button>
      </div>
      <div :class="['pwaPromptBody', 'iOSPWA-body']">
        <div :class="['pwaPromptDescription', 'iOSPWA-description']">
          <p
            id="pwa-prompt-description"
            :class="['pwaPromptCopy', 'iOSPWA-description-copy']"
          >
            {{ copyBody }}
          </p>
        </div>
      </div>
      <div :class="['pwaPromptInstruction', 'iOSPWA-steps']">
        <div :class="['pwaPromptInstructionStep', 'iOSPWA-step1']">
          <ShareIcon
            :class="['pwaPromptShareIcon', 'iOSPWA-step1-icon']"
            :modern="isiOS13AndUp"
          />
          <p :class="['pwaPromptCopy', 'bold', 'iOSPWA-step1-copy']">
            {{ copyShareButtonLabel }}
          </p>
        </div>
        <div :class="['pwaPromptInstructionStep', 'iOSPWA-step2']">
          <HomeScreenIcon
            :class="['pwaPromptHomeIcon', 'iOSPWA-step2-icon']"
            :modern="isiOS13AndUp"
          />
          <p :class="['pwaPromptCopy', 'bold', 'iOSPWA-step2-copy']">
            {{ copyAddHomeButtonLabel }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HomeScreenIcon from "./HomeScreenIcon";
import ShareIcon from "./ShareIcon";
import CloseIcon from "./CloseIcon";

export default {
  components: {
    HomeScreenIcon,
    ShareIcon,
    CloseIcon,
  },
  props: {
    appName:{ type: String },
    logo:{ type: String },
    delay: { type: Number, default: 1000 },
    copyBody: { type: String, required: true },
    copyAddHomeButtonLabel: { type: String, required: true },
    copyShareButtonLabel: { type: String, required: true },
    copyClosePrompt: { type: String, required: true },
    permanentlyHideOnDismiss: { type: Boolean, default: true },
    promptData: { type: Object, default: () => ({}) },
    maxVisits: { type: Number, default: null },
    onClose: { type: Function, default: () => {} },
    debug: { type: Boolean, default: false },
  },

  data() {
    return {
      isVisible: false,
    };
  },

  created() {
    // eslint-disable-next-line no-extra-boolean-cast
    this.isVisible = !Boolean(this.delay);

    if (this.delay) {
      setTimeout(() => {
        // Prevent keyboard appearing over the prompt if a text input has autofocus set
        if (document.activeElement) {
          document.activeElement.blur();
        }

        this.isVisible = true;
      }, this.delay);
    }
  },

  computed: {
    isiOS13AndUp() {
      return /OS (13|14)/.test(window.navigator.userAgent);
    },
    visibilityClass() {
      return this.isVisible ? "visible" : "hidden";
    },
    iOSClass() {
      return this.isiOS13AndUp ? "modern" : "legacy";
    },
  },

  methods: {
    dismissPrompt(evt) {
      document.body.classList.remove("noScroll");
      this.isVisible = false;

      if (this.permanentlyHideOnDismiss) {
        localStorage.setItem(
          "iosPwaPrompt",
          JSON.stringify({
            ...this.promptData,
            visits: this.maxVisits,
          })
        );
      }

      if (typeof this.onClose === "function") {
        this.onClose(evt);
      }
    },

    onTransitionOut(evt) {
      if (!this.isVisible) {
        evt.currentTarget.style.display = "none";
      }
    },
  },
};
</script>

<style lang="scss" scoped>
$overlay-color-legacy: rgba(0, 0, 0, 0.22);
$overlay-color-modern-light: rgba(10, 10, 10, 0.5);
$overlay-color-modern-dark: rgba(10, 10, 10, 0.5);

$bg-color-legacy: rgba(250, 250, 250, 0.8);
$bg-color-modern-light: rgba(255, 255, 255, 1);
$bg-color-modern-dark: rgba(92, 92, 92, 0.88);

$border-color-legacy: rgba(0, 0, 0, 0.1);
$border-color-modern-light: rgba(230, 230, 230, 1);
$border-color-modern-dark: rgba(230, 230, 230, 0.52);

$font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto,
  "微软雅黑";

$title-color-legacy: rgb(51, 51, 51);
$title-color-modern-light: rgba(55, 63, 87, 1);
$title-color-modern-dark: rgba(255, 255, 255, 1);

$font-color-legacy: rgb(123, 123, 122);
$font-color-modern-light: rgba(55, 63, 87, 1);
$font-color-modern-dark: rgba(255, 255, 255, 1);

$blue-color-legacy: rgb(45, 124, 246);
$blue-color-modern-light: rgba(69, 146, 254, 1);
$blue-color-modern-dark: rgba(69, 146, 254, 1);

.noScroll {
  overflow: hidden;
}

.pwaPromptOverlay {
  background-color: $overlay-color-legacy;
  left: 0;
  min-height: 100vh;
  min-height: -webkit-fill-available;
  opacity: 0;
  position: fixed;
  top: 0;
  transition: opacity 0.2s ease-in;
  width: 100vw;
  z-index: 999999;

  &.visible {
    opacity: 1;
    display: block;
  }

  &.hidden {
    pointer-events: none;
    touch-action: none;
  }

  &.modern {
    @media (prefers-color-scheme: dark) {
      & {
        background: $overlay-color-modern-dark;
        color: $font-color-modern-dark;
      }
    }
  }
}

.pwaPrompt {
  -webkit-backdrop-filter: blur(10px);
  backdrop-filter: blur(10px);
  background-color: $bg-color-legacy;
  border-radius: 15px;
  bottom: 0;
  color: black;
  left: 0;
  margin: 12px;
  overflow: hidden;
  position: fixed;
  transform: translateY(calc(100% + 10px));
  transition: transform 0.4s cubic-bezier(0.4, 0.24, 0.3, 1);
  width: calc(100vw - 24px);
  z-index: 999999;

  &.visible {
    transform: translateY(0);
    display: block;
  }

  &.hidden {
    pointer-events: none;
    touch-action: none;
  }

  &.modern {
    background: $bg-color-modern-light;

    @media (prefers-color-scheme: dark) {
      & {
        background: $bg-color-modern-dark;
      }
    }
  }
}

.pwaPromptHeader {
  align-items: center;
  border-bottom: 1px solid $border-color-legacy;
  border-top: 0px;
  border-left: 0px;
  border-right: 0px;
  border-width: 0.5px;
  display: flex;
  flex-flow: row nowrap;
  justify-content: space-between;
  padding: 14px 20px;

  .modern & {
    border-color: $border-color-modern-light;

    @media (prefers-color-scheme: dark) {
      & {
        border-color: $border-color-modern-dark;
      }
    }
  }

  .pwaPromptHeaderLogo {
    width: 40px;
    height: 40px;
  }

  .pwaPromptHeaderInfo {
    display: flex;
    align-items: center;
  }

  .pwaPromptTitle {
    color: $title-color-legacy;
    font-size: 16px;
    font-weight: bold;
    line-height: 23px;
    letter-spacing: 0.21px;
    margin: 0 0 0 10px;

    .modern & {
      color: $title-color-modern-light;

      @media (prefers-color-scheme: dark) {
        & {
          color: $title-color-modern-dark;
        }
      }
    }
  }

  .pwaPromptCancel {
    color: $blue-color-legacy;
    font-size: 16px;
    padding: 5px;
    margin: 0;
    border: 0;
    background: transparent;
    display: inherit;

    .modern & {
      color: $blue-color-modern-light;

      @media (prefers-color-scheme: dark) {
        & {
          color: $blue-color-modern-dark;
        }
      }
    }
  }
}

.pwaPromptBody {
  display: flex;
  width: 100%;

  .pwaPromptDescription {
    border-bottom: 1px solid $border-color-legacy;
    border-top: 0px;
    border-left: 0px;
    border-right: 0px;
    border-width: 0.5px;
    color: inherit;
    margin: 0 20px;
    padding: 14px 0;
    width: 100%;

    .modern & {
      border-color: $border-color-modern-light;

      @media (prefers-color-scheme: dark) {
        & {
          border-color: $border-color-modern-dark;
        }
      }
    }
  }
}

.pwaPromptCopy {
  color: $font-color-legacy;
  font-size: 13px;
  line-height: 22px;
  letter-spacing: 0.17px;
  margin: 0;
  padding: 0;

  &.bold {
    font-weight: 400;
  }

  .modern & {
    color: $font-color-modern-light;

    @media (prefers-color-scheme: dark) {
      & {
        color: $font-color-modern-dark;
      }
    }
  }
}

.pwaPromptInstruction {
  color: inherit;
  margin: 20px 30px;

  .pwaPromptInstructionStep {
    align-items: center;
    display: flex;
    flex-flow: row nowrap;
    justify-content: flex-start;
    text-align: left;
    margin-bottom: 20px;

    &:last-of-type {
      margin-bottom: 0;
    }
  }

  .pwaPromptShareIcon,
  .pwaPromptHomeIcon {
    flex: 0 0 auto;
    height: 23px;
    margin-right: 12px;
    width: 23px;
  }

  .pwaPromptHomeIcon {
    color: $blue-color-legacy;

    .modern & {
      color: black;
      fill: black;

      @media (prefers-color-scheme: dark) {
        & {
          color: white;
          fill: white;
        }
      }
    }
  }

  .pwaPromptShareIcon {
    color: $blue-color-legacy;
    fill: $blue-color-legacy;

    .modern & {
      color: $blue-color-modern-light;
      fill: $blue-color-modern-light;

      @media (prefers-color-scheme: dark) {
        & {
          color: $blue-color-modern-dark;
          fill: $blue-color-modern-dark;
        }
      }
    }
  }
}
</style>
