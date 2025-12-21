// ========================================
// IMPROVED PREDICTION FORM JAVASCRIPT
// ========================================

let inputChart;
let updateTimeout;
let isChartInitialized = false;
let isUpdating = false;
// Validation constants
const MIN_PRICE = 10000;
const MAX_PRICE = 200000;
const CHART_MIN = 5000;
const CHART_MAX = 100000;

function initInputChart() {
    try {
        const canvas = document.getElementById('inputChart');
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }

        const ctx = canvas.getContext('2d');
        inputChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Kemarin'],
                datasets: [{
                    label: 'Harga Input (Rp)',
                    data: new Array(7).fill(null),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: false,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    spanGaps: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio:  false,
                animation: {
                    duration: 300
                },
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: CHART_MIN,
                        max: CHART_MAX,
                        ticks:  {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.1)'
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
                    mode: 'index'
                }
            }
        });
        
        isChartInitialized = true;
        console.log('Chart initialized successfully');
        
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
}

function updatePreviewChart() {
    if (!isChartInitialized) {
        console.warn('Chart not initialized yet');
        return;
    }
    
    if (isUpdating) {
        return;
    }
    isUpdating = true;

    const prices = [];
    let validCount = 0;
    let invalidCount = 0;
    
    // Collect and validate data
    for(let i = 1; i <= 7; i++) {
        const input = document.getElementById(`day${i}`);
        if (!input) continue;
        
        const raw = input.value;
        const value = parseInt(raw);
        
        // empty -> null (not plotted)
        if (!raw || raw.trim() === '') {
            prices.push(null);
            input.style.borderColor = '#ced4da';
            input.style.backgroundColor = '#fff';
            continue;
        }
        
        // invalid number
        if (isNaN(value)) {
            prices.push(null);
            invalidCount++;
            input.style.borderColor = '#dc3545';
            input.style.backgroundColor = '#fff5f5';
            continue;
        }
        
        // out of allowed range -> mark invalid and do not include in dataset
        if (value < MIN_PRICE || value > MAX_PRICE) {
            prices.push(null);
            invalidCount++;
            input.style.borderColor = '#dc3545';
            input.style.backgroundColor = '#fff5f5';
            continue;
        }
        
        // valid
        prices.push(value);
        validCount++;
        input.style.borderColor = '#28a745';
        input.style.backgroundColor = '#f8fff9';
    }
    
    const previewChart = document.getElementById('previewChart');
    if (!previewChart) return;
    
    // Show warning if there are invalid values
    toggleChartWarning(invalidCount > 0);
    
    if(validCount >= 2) {
        previewChart.style.display = 'block';
        previewChart.classList.add('active');
        inputChart.data.datasets[0].data = prices;
        inputChart.update('none');
    } else {
        previewChart.style.display = 'none';
        previewChart.classList.remove('active');
        // clear chart points to avoid stray rendering
        inputChart.data.datasets[0].data = new Array(7).fill(null);
        inputChart.update('none');
    }
    
    isUpdating = false;
}

// Rest of the functions remain the same...
function debouncedUpdateChart() {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(() => {
        updatePreviewChart();
    }, 500);
}

// Show/hide warning for invalid values
function toggleChartWarning(show) {
    const previewChart = document.getElementById('previewChart');
    if (!previewChart) return;
    let warn = document.getElementById('chartWarning');
    if (show) {
        if (!warn) {
            warn = document.createElement('div');
            warn.id = 'chartWarning';
            warn.className = 'alert alert-warning mt-2';
            warn.innerText = 'Beberapa nilai tidak valid atau di luar rentang (10.000 - 200.000). Nilai tersebut tidak akan ditampilkan pada grafik.';
            previewChart.parentNode.insertBefore(warn, previewChart);
        }
    } else {
        if (warn) warn.remove();
    }
}

function fillSampleData(type) {
    let prices = [];
    
    switch(type) {
        case 'trend_up': 
            prices = [45000, 47000, 48000, 50000, 52000, 54000, 56000];
            break;
        case 'trend_down':
            prices = [60000, 58000, 56000, 54000, 52000, 50000, 48000];
            break;
        case 'stable': 
            prices = [50000, 51000, 49500, 50500, 49800, 50200, 50000];
            break;
    }
    
    for(let i = 1; i <= 7; i++) {
        const input = document.getElementById(`day${i}`);
        if (input) {
            input.value = prices[i-1];
        }
    }
    
    updatePreviewChart();
}

function clearForm() {
    for(let i = 1; i <= 7; i++) {
        const input = document.getElementById(`day${i}`);
        if (input) {
            input.value = '';
            input.style.borderColor = '#ced4da';
            input.style.backgroundColor = '#fff';
        }
    }
    
    const previewChart = document.getElementById('previewChart');
    if (previewChart) {
        previewChart.style.display = 'none';
        previewChart.classList.remove('active');
    }
    
    // Clear chart data
    if (inputChart && isChartInitialized) {
        inputChart.data.datasets[0].data = new Array(7).fill(null);
        inputChart.update('none');
    }
}

// Expose updatePreviewChart to window for manual button trigger
window.refreshChartPreview = function() {
    updatePreviewChart();
}

// INITIALIZE
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing chart...');
    
    // Wait a bit for DOM to be fully ready
    setTimeout(() => {
        initInputChart();
    }, 100);
    
    // Add event listeners
    for(let i = 1; i <= 7; i++) {
        const input = document.getElementById(`day${i}`);
        if (input) {
            // Prevent invalid characters
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                    e.preventDefault();
                }
            });
            
            // Prevent paste of non-numeric
            input.addEventListener('paste', function(e) {
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                if (isNaN(parseInt(pasted.trim()))) e.preventDefault();
            });
        }
    }
    
    // Form validation
    const form = document.getElementById('predictionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            let emptyFields = [];
            
            for(let i = 1; i <= 7; i++) {
                const input = document. getElementById(`day${i}`);
                if (!input) continue;
                
                const value = parseInt(input.value);
                
                if (! input.value) {
                    emptyFields.push(`Hari ke-${i}`);
                    isValid = false;
                } else if (value < 10000 || value > 200000) {
                    isValid = false;
                }
            }
            
            if (! isValid) {
                e. preventDefault();
                if (emptyFields.length > 0) {
                    alert(`Mohon isi data untuk:  ${emptyFields.join(', ')}`);
                } else {
                    alert('Mohon perbaiki data yang tidak valid (harga harus antara 10.000 - 200.000)');
                }
                return;
            }
            
            // Loading state
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses Prediksi...';
                submitBtn.disabled = true;
            }
        });
    }
});