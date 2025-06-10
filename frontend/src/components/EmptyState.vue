<template>
  <div class="text-center text-text-light p-8 bg-light rounded">
    <p>{{ message }}</p>
  </div>
</template>

<script>
export default {
  name: 'EmptyState',
  props: {
    message: {
      type: String,
      required: true
    }
  }
}
</script> 