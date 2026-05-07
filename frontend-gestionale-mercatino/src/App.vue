<script setup>
import { defineAsyncComponent } from 'vue'
const Navbar = defineAsyncComponent(() => import('./components/Navbar.vue'))
</script>

<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container fluid class="page-container">
        <RouterView />
      </v-container>
    </v-main>
  </v-app>
</template>

<style>
.page-container {
  width: 95%;
  max-width: 1400px;
  margin: 0 auto 10rem auto;
}
</style>
