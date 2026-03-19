<script setup>
import { defineAsyncComponent } from 'vue'
const Navbar = defineAsyncComponent(() => import('./components/Navbar.vue'))
</script>

<template>
  <Navbar />
  <RouterView />
</template>
