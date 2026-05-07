<template>
  <section class="location-grid">
    <div class="info-text">
      <span class="badge">DOVE SIAMO</span>
      <h1>
        <strong>IIS GALILEI CREMA</strong><br />
        Via Matilde di Canossa, 21
      </h1>
      <p class="room-highlight">Presso l' <strong>AULA RISTORO</strong></p>
      <p class="sub-text">26013 - Crema (CR)</p>
      <div class="cta-area">
        <a href="https://maps.app.goo.gl/vmy5gNvR2uRME1rg9" target="_blank" class="btn-map">
          Visualizza su Google Maps
        </a>
      </div>
    </div>

    <div class="map-box">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1401.823330222827!2d9.683517240198778!3d45.355944443860324!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47813b38b8490dd5%3A0xa25b9d4504dbc03e!2sIstituto%20di%20Istruzione%20Superiore%20%E2%80%9CGalileo%20Galilei%E2%80%9D!5e0!3m2!1sit!2sus!4v1776242657191!5m2!1sit!2sus"
        width="600"
        height="450"
        style="border: 0"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </div>
  </section>
</template>

<style scoped>
.location-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  background: #ffffff;
  padding: 40px;
  border-radius: 20px;
  border: 1px solid #f0f0f0;
}

.badge {
  background: #00d4ff;
  color: #fff;
  padding: 5px 12px;
  font-size: 1.5rem;
  font-weight: bold;
  border-radius: 4px;
}

h1 {
  font-size: 2rem;
  margin: 15px 0;
  font-weight: 800;
}

strong {
  background: #000;
  color: #fff;
  padding: 2px 8px;
}

.sub-text {
  color: #666;
  font-size: 1.1rem;
}

.btn-map {
  display: inline-block;
  margin-top: 25px;
  background: #00d4ff;
  color: #fff;
  text-decoration: none;
  padding: 12px 25px;
  border-radius: 6px;
  font-weight: bold;
  transition: opacity 0.2s;
}

.btn-map:hover {
  opacity: 0.8;
}

.map-box {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

@media (max-width: 900px) {
  .location-grid {
    grid-template-columns: 1fr;
    padding: 20px;
    text-align: center;
  }
}
</style>
