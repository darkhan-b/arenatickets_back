<template>
  <a class="radio-tariff-group" :class="{ active, disabled }">
    <span class="radio-click">
      <span v-if="active" class="radio-active"></span>
    </span>
    {{ label }}
  </a>
</template>

<script>
export default {
  name: "RadioGroup",
  props: {
    value: {},
    label: {
      type: String,
      required: true
    },
    active: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style scoped lang="scss">
.radio-tariff-group {
  background: #FAFAFA;
  border: 1px solid #E8EBED;
  border-radius: 10px;
  height: 40px;
  color: #1A1A1A;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 5px;
  cursor: pointer;
  font-size: 14px;
  &.active {
    border-color: #4BB0FE;
    background: #F2F9FF;
    .radio-click {
      border-color: #4BB0FE;
    }
  }
  .radio-click {
    width: 16px;
    height: 16px;
    background: #FFFFFF;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 100%;
    border: 1px solid #E3E3EA;
    margin-right: 8px;
  }
  .radio-active {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 100%;
    background: #4BB0FE;
  }

}

@media(max-width: 768px) {
  .radio-tariff-group {
    height: 36px;
    font-size: 13px;
  }
}
</style>
