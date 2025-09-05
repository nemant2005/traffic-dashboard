document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('severityChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Low', 'Moderate', 'High'],
            datasets: [{
                label: 'Traffic Incidents',
                data: [12, 8, 5], // Replace with dynamic data using AJAX or PHP
                backgroundColor: ['green', 'orange', 'red']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Traffic Severity Overview' }
            }
        }
    });
});
