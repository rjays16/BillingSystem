<template>
  <div>
    <h1>Invoice #{{ id }}</h1>
    <p>This is a placeholder invoice detail page.</p>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const id = route.params.id
</script>
