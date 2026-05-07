<template>
  <div class="gallery-section">
    <div class="container">
      <div class="image-scroll-container">
        <div class="image-scroll" ref="imageScroll">
          <div v-for="(img, index) in galleryImages" :key="index" class="image-wrapper">
            <img :src="img" :alt="'Immagine ' + (index + 1)" class="gallery-img" />
          </div>
        </div>
      </div>

      <div class="scroll-controls">
        <button @click="scrollLeft" class="scroll-btn" :disabled="!canScrollLeft">←</button>
        <button @click="scrollRight" class="scroll-btn" :disabled="!canScrollRight">→</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImageGallery',
  data() {
    return {
      title: 'La Nostra Galleria',
      // Definiamo solo i nomi dei file
      imageNames: ['galleria1.jpg', 'galleria3.jpg', 'galleria4.jpg', 'galleria2.jpg'],
      canScrollLeft: false,
      canScrollRight: true,
    }
  },
  computed: {
    // Trasformiamo i nomi in percorsi validi per Vite
    galleryImages() {
      return this.imageNames.map((name) => {
        // Questa sintassi permette a Vite di trovare il file in src/assets/
        return new URL(`../assets/${name}`, import.meta.url).href
      })
    },
  },
  mounted() {
    this.checkScrollButtons()
    if (this.$refs.imageScroll) {
      this.$refs.imageScroll.addEventListener('scroll', this.checkScrollButtons)
    }
  },
  methods: {
    scrollLeft() {
      this.$refs.imageScroll.scrollBy({ left: -400, behavior: 'smooth' })
    },
    scrollRight() {
      this.$refs.imageScroll.scrollBy({ left: 400, behavior: 'smooth' })
    },
    checkScrollButtons() {
      const container = this.$refs.imageScroll
      if (container) {
        this.canScrollLeft = container.scrollLeft > 5
        this.canScrollRight =
          container.scrollLeft < container.scrollWidth - container.clientWidth - 5
      }
    },
  },
}
</script>

<style scoped>
.gallery-section {
  padding: 3rem 0;
  background: #ffffff;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.gallery-title {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 2rem;
  font-weight: 600;
}

.image-scroll-container {
  position: relative;
}

.image-scroll {
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  padding: 1rem 0;
}

.image-scroll::-webkit-scrollbar {
  display: none;
}

/* CARD */
.image-wrapper {
  flex: 0 0 auto;
  width: 320px;
  height: 220px;
  border-radius: 16px;
  overflow: hidden;
  scroll-snap-align: center;
  box-shadow: 0 8px 25px rgba(0, 152, 222, 0.56);
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}

.image-wrapper:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgb(22, 7, 7);
}

.gallery-img {
  width: 100%;
  height: 100%;
  transition: transform 0.4s ease;
}

.scroll-controls {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 2rem;
}

.scroll-btn {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.scroll-btn:hover:not(:disabled) {
  transform: scale(1.1);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
}

.scroll-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .image-wrapper {
    width: 260px;
    height: 180px;
  }

  .gallery-title {
    font-size: 1.6rem;
  }
}

@media (max-width: 480px) {
  .image-wrapper {
    width: 220px;
    height: 160px;
  }
}
</style>
