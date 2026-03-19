<template>
  <div class="search-wrapper">
    <div class="line-decorator"></div>
    <v-text-field
      v-model="model"
      :placeholder="placeholder"
      variant="outlined"
      density="compact"
      :loading="loading"
      hide-details
      @keydown.enter.prevent="$emit('search')"
      class="search-input"
    ></v-text-field>
    <v-btn
      color="primary"
      height="40"
      @click="$emit('search')"
      class="rounded-0 rounded-e"
      elevation="0"
    >
      <v-icon icon="mdi-plus"></v-icon>
    </v-btn>
    <div class="line-decorator"></div>
  </div>
</template>

<script setup>
const model = defineModel()

defineProps({
  loading: Boolean,
  placeholder: {
    type: String,
    default: 'Inserisci ISBN ',
  },
})

defineEmits(['search'])
</script>

<style scoped>
.search-wrapper {
  display: flex;
  align-items: center;
  width: 100%;
}
.search-input {
  max-width: 400px;
}
.line-decorator {
  flex-grow: 1;
  height: 1px;
  background-color: #2196f3;
  margin: 0 30px;
}
</style>
