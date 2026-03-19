<template>
  <div v-if="items.length > 0" class="tabella-container">
    <div class="d-flex table-header mb-2 px-4">
      <div
        v-for="col in columns"
        :key="col.key"
        :style="{ width: col.width }"
        :class="[col.class, 'text-uppercase']"
      >
        {{ col.label }}
      </div>
      <div class="w-5"></div>
    </div>

    <v-divider class="mb-4"></v-divider>

    <div
      v-for="(item, index) in items"
      :key="index"
      class="d-flex align-center table-row mb-4 px-4"
    >
      <div
        v-for="col in columns"
        :key="col.key"
        :style="{ width: col.width }"
        :class="col.class"
        class="px-2"
      >
        <span v-if="col.type === 'badge'" class="custom-badge">
          {{ item[col.key] }}
        </span>

        <v-text-field
          v-else-if="col.type === 'input'"
          v-model="item[col.key]"
          :type="col.inputType || 'text'"
          variant="underlined"
          density="compact"
          hide-details
          :class="{ 'centered-input': col.centered }"
        ></v-text-field>

        <span v-else class="text-body-2 font-weight-medium text-uppercase text-truncate d-block">
          {{ item[col.key] }}
        </span>
      </div>

      <div class="w-5 text-right">
        <v-btn
          icon="mdi-delete"
          variant="text"
          color="red-darken-2"
          size="small"
          @click="$emit('remove', index)"
        ></v-btn>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  columns: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['remove'])
</script>

<style scoped>
.table-header {
  font-weight: bold;
  color: #3f51b5;
  font-size: 0.8rem;
}
.w-5 {
  width: 5%;
}
.custom-badge {
  background-color: #3f51b5;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.85rem;
}
:deep(.centered-input input) {
  text-align: center;
}
.table-row {
  border-bottom: 1px solid #eee;
  padding-bottom: 8px;
}
.table-row:hover {
  background-color: #fafafa;
}
</style>
