<template>
  <div class="color-picker">
    <label v-if="label">{{ label }}</label>
    <input type="color" v-model="value" @input="$emit('update:value', value)" />
  </div>
</template>

<script>
export default {
  props: {
    value: { type: String, default: '#ffffff' },
    label: { type: String, default: '' },
  },
};
</script>

<style scoped>
.color-picker input[type="color"] {
  width: 48px;
  height: 32px;
  border: none;
}
</style>
