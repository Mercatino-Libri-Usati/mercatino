<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'

const props = defineProps({
  isbn: {
    type: String,
    default: null,
  },
})

const imageRef = ref(null)
const isVisible = ref(false)
const isLoaded = ref(false)
const hasError = ref(false)

let observer = null

const webpSrc = computed(
  () => `https://www.libriusaticrema.it/imgs/libri/webp_ottimizzati/${props.isbn}.webp`,
)
const avifSrc = computed(
  () => `https://www.libriusaticrema.it/imgs/libri/avif_ottimizzati/${props.isbn}.avif`,
)
const jpgSrc = computed(() => `https://www.libriusaticrema.it/imgs/libri/${props.isbn}.jpg`)

const onLoad = () => {
  isLoaded.value = true
}

const onError = () => {
  isLoaded.value = true
  hasError.value = true
}

watch(
  () => props.isbn,
  () => {
    isLoaded.value = false
    hasError.value = false
    isVisible.value = false
    if (imageRef.value && observer) {
      observer.disconnect()
      observer.observe(imageRef.value)
    }
  },
)

onMounted(() => {
  if (!props.isbn) {
    hasError.value = true
    isLoaded.value = true
    return
  }

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        isVisible.value = true
        observer.disconnect()
      }
    },
    { rootMargin: '100px' },
  )

  if (imageRef.value) {
    observer.observe(imageRef.value)
  }
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<template>
  <div ref="imageRef" class="img-container">
    <!-- Skeleton -->
    <div v-if="!isLoaded" class="skeleton"></div>

    <!-- Immagine WEBP o AVIF con fallback a JPG -->
    <picture v-if="isVisible && !hasError">
      <source :srcset="webpSrc" type="image/webp" />
      <source :srcset="avifSrc" type="image/avif" />
      <img
        :src="jpgSrc"
        alt="Copertina"
        :class="{ loaded: isLoaded }"
        @load="onLoad"
        @error="onError"
      />
    </picture>

    <!-- Fallback -->
    <div v-if="hasError && isLoaded" class="fallback">
      <span>📚</span>
    </div>
  </div>
</template>

<style scoped>
.img-container {
  width: 100%;
  height: 100%;
  position: relative;
  background: #f5f5f5;
  border-radius: 10px;
  overflow: hidden;
}

.skeleton {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.img-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0;
  transition: opacity 0.2s;
}

.img-container img.loaded {
  opacity: 1;
}

.fallback {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  font-size: 2rem;
}
</style>
