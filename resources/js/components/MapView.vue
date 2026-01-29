<template>
  <div id="map-container"></div>
</template>

<script>
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

export default {
  props: {
    properties: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      map: null,
    };
  },
  mounted() {
    this.initMap();
    this.addMarkers();
  },
  methods: {
    initMap() {
      this.map = L.map('map-container').setView([24.7136, 46.6753], 10); // Default to Riyadh
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      }).addTo(this.map);
    },
    addMarkers() {
      this.properties.forEach(property => {
        L.marker([property.latitude, property.longitude])
          .addTo(this.map)
          .bindPopup(`<b>${property.title}</b><br>${property.price_per_night} SAR/night`);
      });
    },
  },
  watch: {
    properties() {
      this.addMarkers();
    },
  },
};
</script>

<style scoped>
#map-container {
  height: 100%;
  width: 100%;
}
</style>
