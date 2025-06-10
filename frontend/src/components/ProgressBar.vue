<template>
  <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden mt-2">
    <div class="h-full bg-primary" :style="{ width: `${percentage}%` }"></div>
  </div>
</template>

<script>
export default {
  name: 'ProgressBar',
  props: {
    percentage: {
      type: Number,
      required: true,
      validator: (value) => value >= 0 && value <= 100
    }
  }
}
</script> 