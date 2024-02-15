<template>
  <k-field v-bind="$props" class="k-color-palette-field">
    <k-box v-if="emptyOptions" theme="info" class="color-palette_empty-options">
      {{ emptyOptionsPlaceholder }}
    </k-box>
    <div v-else class="color-palette_input">
      <ul class="color-palette_input-list">
        <li
          v-for="(color, index) in colors"
          :key="index"
          :class="[size, { active: isValue(color) }, { unselect: unselect }]"
          @click="input(color, index)"
        >
          <div
            class="color-palette_input-color"
            :data-tooltip="toTooltip(color)"
            :style="inlineStyle(color)"
          ></div>
        </li>
      </ul>
    </div>
  </k-field>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      palette: []
    }
  },
  props: {
    options: [Object, Array],
    display: String,
    size: String,
    unselect: Boolean,
    default: [String, Boolean],
    limit: Number,
    uri: String,

    // general options
    label: String,
    disabled: Boolean,
    help: String,
    parent: String,
    value: [String, Array],
    name: [String, Number],
    required: Boolean,
    type: String
  },
  computed: {
    selected() {
      return Array.isArray(this.value) ? this.value[0] : this.value
    },
    emptyOptions() {
      let options = this.isObject(this.options)
        ? Object.keys(this.options).length
        : this.options.length
      return !options && !this.extractor
    },
    emptyOptionsPlaceholder() {
      return this.$t("palette.empty.options")
    },
    colors() {
      if (this.isQueryOptions(this.options)) {
        return this.options.map((el) => {
          return el["value"]
        })
      } else return this.options
    }
  },
  created() {
    if (!this.value && this.default) {
      if (Array.isArray(this.colors) && this.colors.find((el) => el == this.default)) {
        this.value = this.default
        this.input()
      } else if (
        this.isObject(this.colors) &&
        Object.keys(this.colors).find((el) => el == this.default)
      ) {
        this.value = this.colors[this.default]
        this.input()
      }
    }
  },
  methods: {
    isValue(color) {
      if (this.isObject(color)) {
        if (this.selected == color) return true
        else {
          if (this.isObject(this.selected)) {
            return this.isEquivalent(this.selected, color)
          } else {
            return false
          }
        }
      }
      return this.selected == color
    },
    inlineStyle(color) {
      // display: duo
      if (this.display == "duo" && this.isObject(color)) {
        return (
          "background: linear-gradient(to right, " +
          this.firstColor(color) +
          " 50%, " +
          this.secondColor(color) +
          " 50%);"
        )
      }

      return "background:" + this.firstColor(color)
    },
    toTooltip(color) {
      return this.isObject(color) && color["tooltip"] ? color["tooltip"] : false
    },
    firstColor(color) {
      if (this.isString(color)) return color
      else if (this.isObject(color)) return color[Object.keys(color)[0]]
    },
    secondColor(color) {
      if (this.isObject(color)) return color[Object.keys(color)[1]]
      return false
    },
    isString(color) {
      return typeof color === "string"
    },
    isObject(color) {
      return color !== null && color !== undefined && typeof color === "object"
    },
    isQueryOptions(options) {
      if (!options.length) return false
      let option = options[0]
      return (
        this.isObject(option) &&
        Object.keys(option).length == 2 &&
        Object.keys(option).includes("text") &&
        Object.keys(option).includes("value")
      )
    },
    isEquivalent(a, b) {
      let aKeys = Object.keys(a)
      let bKeys = Object.keys(b)

      // Make sure to compare without the keys
      let aKeyIndex = aKeys.indexOf("key")
      if (aKeyIndex !== -1) aKeys.splice(aKeyIndex, 1)
      let bKeyIndex = bKeys.indexOf("key")
      if (bKeyIndex !== -1) bKeys.splice(bKeyIndex, 1)

      // Different keys? not equivalent
      if (aKeys.length != bKeys.length) {
        return false
      }

      for (var i = 0; i < aKeys.length; i++) {
        let key = aKeys[i]
        // Different values? not equivalent
        if (a[key] !== b[key]) {
          return false
        }
      }
      return true
    },
    input(color = false, index) {
      if (color) {
        if (this.unselect && this.isValue(color)) color = ""
        this.value = this.extractor ? [color, this.extracted] : color
        if (this.isObject(this.value)) {
          this.value["key"] = index
        }
      }
      this.$emit("input", this.value)
    }
  }
}
</script>

<style lang="scss">
.k-color-palette-field {
  .k-button {
    cursor: pointer;
  }
  .k-button-figure .k-icon {
    margin-right: -4px;
  }
}

.color-palette_input {
  &-list {
    display: flex;
    flex-wrap: wrap;
    padding: 0.75rem;
    padding-bottom: calc(0.75rem - 6px);
    background: darken(#efefef, 2%);
    li {
      position: relative;
      transition: transform 0.3s ease-out;
      margin-bottom: 6px;
      border: 4px solid transparent;

      /* Size
			------------------------*/
      &.small {
        width: 28px;
        height: 28px;
      }
      &.medium {
        width: 38px;
        height: 38px;
      }
      &.large {
        width: 48px;
        height: 48px;
      }

      &:not(:last-child) {
        margin-right: 6px;
      }
      &.active {
        pointer-events: none;
        border: 4px solid white;
        outline: 1px solid #cccccc;

        /* Unselect?
				------------------------*/
        &.unselect {
          pointer-events: auto;
          cursor: pointer;
        }
      }
      &:not(.active) {
        cursor: pointer;
      }
    }
  }

  /* Style
	------------------------*/
  &-color {
    width: 100%;
    height: 100%;
    &[data-tooltip] {
      position: relative;
      z-index: 2;
      cursor: pointer;
      &:before,
      &:after {
        visibility: hidden;
        pointer-events: none;
      }
      &:before {
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translate(-50%);
        margin-bottom: 5px;
        padding: 7px 12px;
        width: auto;
        background-color: #000;
        color: #fff;
        content: attr(data-tooltip);
        text-align: center;
        font-size: 14px;
        line-height: 16px;
        white-space: pre;
      }
      &:after {
        position: absolute;
        bottom: 120%;
        left: 50%;
        margin-left: -5px;
        width: 0;
        border-top: 5px solid #000;
        border-right: 5px solid transparent;
        border-left: 5px solid transparent;
        content: "";
        font-size: 0;
        line-height: 0;
      }
      &:hover {
        &:before,
        &:after {
          visibility: visible;
        }
      }
    }
  }
}

.color-palette_empty {
  &-palette {
    padding: 0;
    display: flex;
    align-items: center;
    flex-direction: row;
    justify-content: flex-start;
    .k-icon {
      border-right: 1px solid #d6d6d6;
      margin-bottom: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      svg {
        width: 14px;
      }
    }
    p {
      line-height: 1.25rem;
      text-align: left;
    }
    &.small {
      .k-icon {
        width: calc(28px + 1.5rem);
        height: calc(28px + 1.5rem);
      }
      p {
        padding: 0.75rem 1rem;
      }
    }
    &.medium {
      .k-icon {
        width: calc(38px + 1.5rem);
        height: calc(38px + 1.5rem);
      }
      p {
        padding: 0.75rem 1.25rem;
      }
    }
    &.large {
      .k-icon {
        width: calc(48px + 1.5rem);
        height: calc(48px + 1.5rem);
      }
      p {
        padding: 0.75rem 1.75rem;
      }
    }
  }
  &-loading {
    padding: 0.75rem;
    background: darken(#efefef, 2%);
    .loader-ctn {
      display: flex;
      justify-content: center;
      align-items: center;
      &.small {
        height: 28px;
      }
      &.medium {
        height: 38px;
      }
      &.large {
        height: 48px;
      }

      .loader {
        display: inline-block;
        overflow: hidden;
        height: 1.3em;
        margin-top: -0.3em;
        line-height: 1.5em;
        vertical-align: text-bottom;
        &::after {
          content: "⠋\A⠙\A⠹\A⠸\A⠼\A⠴\A⠦\A⠧\A⠇\A⠏";
          display: inline-table;
          white-space: pre;
          text-align: left;
          animation: spin10 0.8s steps(10) infinite;
        }
      }

      @keyframes spin10 {
        to {
          transform: translateY(-15em);
        }
      }
    }
  }
}
</style>
