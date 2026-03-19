<template>
  <v-text-field
    v-model="userStore.searchQuery"
    label="Cerca utente (nome, email, tel...)"
    prepend-inner-icon="mdi-magnify"
    class="mb-5 mt-4"
    centered
  ></v-text-field>
</template>

<script setup>
import { useUserStore } from '@/stores/usersStore'
const userStore = useUserStore()
</script>
