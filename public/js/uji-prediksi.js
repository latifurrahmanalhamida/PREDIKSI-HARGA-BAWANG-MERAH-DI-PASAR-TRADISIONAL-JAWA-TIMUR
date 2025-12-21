// ========================================
// DATA UJI PREDIKSI - JAVASCRIPT
// ========================================

let comparisonChart = null;
let currentRegion = 'surabaya';
let currentMonth = 'all';

function loadUjiPrediksi(region = 'surabaya', month = 'all') {
    currentRegion = region;
    currentMonth = month;
    
    // Show loading for chart
    if (document.getElementById('chartLoading')) {
        document.getElementById('comparisonChart').style.display = 'none';
        document.getElementById('chartLoading').style.display = 'block';
    }
    
    fetch(`/uji-prediksi?region=${region}&month=${month}`)
        .then(res => res.json())
        .then(data => {
            let tbody = '';
            let minDate = '', maxDate = '';
            
            data.list.forEach((row, idx) => {
                // Format tanggal
                const tanggal = new Date(row.tanggal).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                
                // Format harga
                const hargaAktual = parseInt(row.harga_aktual).toLocaleString('id-ID');
                const hargaPrediksi = parseInt(row.harga_prediksi).toLocaleString('id-ID');
                const selisih = parseInt(row.selisih).toLocaleString('id-ID');
                const error = parseFloat(row.error).toFixed(2);
                
                // Error color coding
                let errorClass = 'text-success';
                if (error > 5) errorClass = 'text-danger';
                else if (error > 2) errorClass = 'text-warning';
                
                tbody += `<tr>
                    <td>${tanggal}</td>
                    <td>Rp ${hargaAktual}</td>
                    <td>Rp ${hargaPrediksi}</td>
                    <td>Rp ${selisih}</td>
                    <td class="${errorClass}"><strong>${error}%</strong></td>
                </tr>`;
                
                if (idx == 0) minDate = tanggal;
                maxDate = tanggal;
            });
            
            document.querySelector('#ujiPrediksiTable tbody').innerHTML = tbody;
            document.getElementById('avg-mape').textContent = data.avg_mape;
            document.getElementById('avg-mape2').textContent = data.avg_mape;
            document.getElementById('uji-count').textContent = data.count;
            document.getElementById('active-region').textContent = data.region;
            document.getElementById('uji-date-range').textContent = minDate + " s/d " + maxDate;
            
            // Update chart
            updateChart(data.list);
        })
        .catch(error => {
            console.error('Error loading uji prediksi:', error);
            document.querySelector('#ujiPrediksiTable tbody').innerHTML = 
                '<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>';
        });
}

function updateChart(dataList) {
    // Hide loading
    if (document.getElementById('chartLoading')) {
        document.getElementById('chartLoading').style.display = 'none';
        document.getElementById('comparisonChart').style.display = 'block';
    }
    
    // Prepare data for chart
    const labels = [];
    const actualData = [];
    const predictedData = [];
    
    dataList.forEach(row => {
        const date = new Date(row.tanggal);
        const formattedDate = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        labels.push(formattedDate);
        // Bulatkan ke bilangan bulat (buang 3 digit terakhir)
        actualData.push(Math.round(parseFloat(row.harga_aktual)));
        predictedData.push(Math.round(parseFloat(row.harga_prediksi)));
    });
    
    // Destroy existing chart
    if (comparisonChart) {
        comparisonChart.destroy();
    }
    
    // Create new chart
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    comparisonChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Harga Aktual',
                    data: actualData,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#2563eb',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Prediksi Model',
                    data: predictedData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 3,
                    pointBackgroundColor: '#f97316',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            // Format ke bilangan bulat tanpa desimal
                            const value = Math.round(context.parsed.y);
                            label += 'Rp ' + value.toLocaleString('id-ID');
                            return label;
                        },
                        afterBody: function(context) {
                            if (context.length > 0) {
                                const index = context[0].dataIndex;
                                const actual = Math.round(actualData[index]);
                                const predicted = Math.round(predictedData[index]);
                                const selisih = Math.abs(predicted - actual);
                                const error = Math.abs(((predicted - actual) / actual) * 100).toFixed(2);
                                return ['', `Selisih: Rp ${selisih.toLocaleString('id-ID')}`, `Error: ${error}%`];
                            }
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000).toFixed(0) + 'k';
                        },
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function applyFilter() {
    const month = document.getElementById('monthFilter').value;
    loadUjiPrediksi(currentRegion, month);
}

function resetFilter() {
    document.getElementById('monthFilter').value = 'all';
    loadUjiPrediksi(currentRegion, 'all');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if uji prediksi table exists
    if (document.getElementById('ujiPrediksiTable')) {
        loadUjiPrediksi('surabaya'); // Default region
    }
});
