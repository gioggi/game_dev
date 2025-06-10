<template>
  <div class="border border-gray-300 rounded p-4 bg-white">
    <div class="mb-4">
      <slot></slot>
    </div>
    <slot name="actions"></slot>
  </div>
</template>

<script>
export default {
  name: 'Card'
}
</script> 