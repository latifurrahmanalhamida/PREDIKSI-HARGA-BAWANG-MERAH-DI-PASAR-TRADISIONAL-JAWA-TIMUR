// ========================================
// DASHBOARD SPECIFIC JAVASCRIPT
// ========================================

let priceChart;
let currentRegion = null;
let currentYear = '2024';
let currentMonth = 'all';

function initChart() {
    const ctx = document.getElementById('priceChart').getContext('2d');
    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Harga (Rp)',
                data:  [],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth:  2,
                pointRadius:  6,
                pointHoverRadius:  8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return app.formatCurrency(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode:  'index'
            }
        }
    });
}

function loadTrendChart(region = 'probolinggo', year = '2024', month = 'all') {
    currentRegion = region;
    currentYear = year;
    currentMonth = month;
    
    // Show loading
    document.getElementById('chartLoading').style.display = 'block';
    
    fetch(`/dashboard/trend-data?region=${region}&year=${year}&month=${month}`)
        .then(res => res.json())
        .then(data => {
            priceChart.data.labels = data.labels;
            priceChart.data.datasets[0].data = data.values;
            priceChart.update('active');
        })
        .catch(error => {
            console.error('Error loading trend data:', error);
            app.showNotification('Gagal memuat data trend untuk ' + region, 'error');
        })
        .finally(() => {
            document.getElementById('chartLoading').style.display = 'none';
        });
}

function loadRegionData(region) {
    // Update active button
    document.querySelectorAll('.region-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('.region-btn').classList.add('active');
    
    // Update selected region badge
    document.getElementById('selected-region').textContent = region;
    
    // Load real trend data from harga_harian table
    loadTrendChart(region.toLowerCase(), currentYear, currentMonth);
    
    // Show loading
    document.getElementById('chartLoading').style.display = 'block';
    
    // Fetch prediction data
    fetch(`/dashboard/region/${region}`)
        .then(response => response.json())
        .then(data => {
            updatePrediction(data.prediction, region);
            document.getElementById('prediction-panel').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            app.showNotification('Gagal memuat data untuk wilayah ' + region, 'error');
        })
        .finally(() => {
            document.getElementById('chartLoading').style.display = 'none';
        });
}

function updatePrediction(prediction, region) {
    document.getElementById('prediction-price').textContent = app.formatCurrency(prediction. price);
    document.getElementById('prediction-region').textContent = region;
    document.getElementById('confidence-score').textContent = prediction. confidence + '%';
    document.getElementById('response-time').textContent = '~1. 2s';
    
    const trendElement = document.getElementById('trend-indicator');
    if (prediction.trend === 'up') {
        trendElement. innerHTML = '<i class="bi bi-arrow-up-circle fs-2"></i>';
        trendElement.className = 'trend-up';
    } else if (prediction.trend === 'down') {
        trendElement.innerHTML = '<i class="bi bi-arrow-down-circle fs-2"></i>';
        trendElement. className = 'trend-down';
    } else {
        trendElement.innerHTML = '<i class="bi bi-dash-circle fs-2"></i>';
        trendElement.className = 'trend-stable';
    }
}

function compareAllRegions() {
    window.location.href = '/dashboard/comparison';
}

function changeFilter() {
    const year = document.getElementById('yearFilter').value;
    const month = document.getElementById('monthFilter').value;
    
    // Reload chart with new filter
    if (currentRegion) {
        loadTrendChart(currentRegion, year, month);
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});