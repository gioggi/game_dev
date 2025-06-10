<template>
  <div class="flex justify-between bg-light p-4 rounded mb-8">
    <div v-for="(stat, index) in stats" :key="index" class="flex flex-col items-center">
      <span class="text-sm text-text-light">{{ stat.label }}:</span>
      <span class="text-lg font-bold text-text">{{ formatValue(stat.value) }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatsBar',
  props: {
    stats: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(stat => 
          typeof stat.label === 'string' && 
          (typeof stat.value === 'number' || typeof stat.value === 'string')
        )
      }
    }
  },
  methods: {
    formatValue(value) {
      if (typeof value === 'number') {
        return value.toLocaleString()
      }
      return value
    }
  }
}
</script> 