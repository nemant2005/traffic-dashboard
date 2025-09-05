document.addEventListener("DOMContentLoaded", function () {
    const map = L.map('map').setView([19.0760, 72.8777], 12); // Example: Mumbai

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Sample marker
    L.marker([19.0760, 72.8777]).addTo(map)
        .bindPopup('Sample Traffic Incident')
        .openPopup();
});
