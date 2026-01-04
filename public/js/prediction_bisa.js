// ========================================
// PREDICTION FORM JAVASCRIPT - SIMPLE VERSION
// ========================================

// Validation constants
const MIN_PRICE = 10000;
const MAX_PRICE = 200000;

// Function to generate Excel template using SheetJS
function generateExcelTemplate() {
    // Check if XLSX library is loaded
    if (typeof XLSX === 'undefined') {
        // Fallback to CSV if XLSX not available
        const csv = 'Harga\n26000\n26500\n27000\n27500\n28000\n28500\n29000\n';
        const blob = new Blob([csv], {type: 'text/csv'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'template_harga_7hari.csv';
        document.body.appendChild(a);
        a.click();
        setTimeout(() => { document.body.removeChild(a); URL.revokeObjectURL(url); }, 100);
        return;
    }
    
    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const wsData = [
        ['Harga'],      // Header
        [26000],        // Hari ke-1 (7 hari lalu)
        [26500],        // Hari ke-2
        [27000],        // Hari ke-3
        [27500],        // Hari ke-4
        [28000],        // Hari ke-5
        [28500],        // Hari ke-6
        [29000]         // Hari ke-7 (kemarin)
    ];
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    
    // Set column width
    ws['!cols'] = [{ wch: 15 }];
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Template Harga');
    
    // Generate and download
    XLSX.writeFile(wb, 'template_harga_7hari.xlsx');
}

// Custom Alert Function
function showCustomAlert(title, message, iconType = 'warning') {
    const modal = document.getElementById('customAlertModal');
    const icon = document.getElementById('alertIcon');
    const titleEl = document.getElementById('alertTitle');
    const messageEl = document.getElementById('alertMessage');
    
    if (!modal) return;
    
    // Set icon type
    icon.className = `custom-alert-icon ${iconType}`;
    
    // Set icon based on type
    let iconHTML = '';
    switch(iconType) {
        case 'warning':
            iconHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
            break;
        case 'error':
            iconHTML = '<i class="bi bi-x-circle-fill"></i>';
            break;
        case 'info':
            iconHTML = '<i class="bi bi-info-circle-fill"></i>';
            break;
        default:
            iconHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
    }
    icon.innerHTML = iconHTML;
    
    // Set content
    titleEl.textContent = title;
    messageEl.innerHTML = message;
    
    // Show modal
    modal.style.display = 'flex';
    
    // Close on click anywhere
    modal.onclick = function() {
        modal.style.display = 'none';
    };
}

// Helper function to check if all price fields are empty
function areAllPriceFieldsEmpty() {
    for(let i = 1; i <= 7; i++) {
        const input = document.getElementById(`day${i}`);
        if (input && input.value && input.value.trim() !== '') {
            return false;
        }
    }
    return true;
}

// INITIALIZE
document.addEventListener('DOMContentLoaded', function() {
        // Tab switching for input mode (Custom Design)
        const manualTab = document.getElementById('manual-tab');
        const excelTab = document.getElementById('excel-tab');
        const manualInput = document.getElementById('manualInput');
        const excelInput = document.getElementById('excelInput');
        if (manualTab && excelTab && manualInput && excelInput) {
            manualTab.addEventListener('click', function() {
                manualTab.classList.add('active');
                excelTab.classList.remove('active');
                manualInput.classList.add('show', 'active');
                excelInput.classList.remove('show', 'active');
            });
            excelTab.addEventListener('click', function() {
                excelTab.classList.add('active');
                manualTab.classList.remove('active');
                excelInput.classList.add('show', 'active');
                manualInput.classList.remove('show', 'active');
            });
        }

        // Excel file upload & preview
        const hargaExcelInput = document.getElementById('harga_excel');
        const excelPreview = document.getElementById('excelPreview');
        const excelPreviewTable = document.getElementById('excelPreviewTable');
        const selectedFileName = document.getElementById('selectedFileName');
        
        if (hargaExcelInput) {
            // Info message & template link di dalam upload zone
            const uploadZone = hargaExcelInput.closest('.upload-zone');
            if (uploadZone) {
                const infoBox = document.createElement('div');
                infoBox.className = 'upload-info mt-3';
                infoBox.innerHTML = `
                    <i class="bi bi-info-circle-fill"></i> 
                    <strong>Ketentuan File:</strong>
                    <ul class="mb-2 mt-2 text-start" style="font-size: 0.9rem;">
                        <li>File harus berisi <strong>7 baris harga</strong></li>
                        <li>Urut dari 7 hari lalu sampai kemarin</li>
                        <li>Hanya kolom pertama yang dibaca</li>
                    </ul>
                    <a href="#" id="downloadTemplate" class="btn btn-sm btn-success">
                        <i class="bi bi-download"></i> Download Template Excel
                    </a>
                `;
                uploadZone.appendChild(infoBox);
            }
            
            // Download template event - Generate real Excel file
            document.addEventListener('click', function(e) {
                if (e.target && (e.target.id === 'downloadTemplate' || e.target.closest('#downloadTemplate'))) {
                    e.preventDefault();
                    generateExcelTemplate();
                }
            });
            
            // Show selected file name
            hargaExcelInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                if (!file) {
                    // File belum dipilih - reset nama file
                    if (selectedFileName) {
                        selectedFileName.innerHTML = '';
                    }
                    excelPreview.style.display = 'block';
                    excelPreviewTable.innerHTML = `<div class='alert alert-warning'>
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>File belum dipilih!</strong><br><br>
                        📁 Silakan pilih file Excel (.xlsx, .xls) atau CSV (.csv)<br>
                        💡 File harus berisi 7 baris harga
                    </div>`;
                    return;
                }
                
                // Validasi wilayah harus dipilih dulu
                const regionSelect = document.getElementById('region');
                if (!regionSelect || !regionSelect.value || regionSelect.value === '') {
                    // Reset input file DAN nama file yang ditampilkan
                    hargaExcelInput.value = '';
                    if (selectedFileName) {
                        selectedFileName.innerHTML = ''; // Reset nama file
                    }
                    excelPreview.style.display = 'block';
                    excelPreviewTable.innerHTML = `<div class='alert alert-warning'>
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Wilayah belum dipilih!</strong><br><br>
                        📍 Silakan pilih wilayah terlebih dahulu<br>
                        📁 Kemudian upload file Excel/CSV<br><br>
                        💡 <em>Setiap wilayah memiliki model prediksi yang berbeda</em>
                    </div>`;
                    showCustomAlert('PERHATIAN!', 
                        `Wilayah belum dipilih!<br><br>` +
                        `📍 Silakan pilih wilayah terlebih dahulu<br>` +
                        `📁 Kemudian upload file Excel/CSV<br><br>` +
                        `💡 <em>Setiap wilayah memiliki model prediksi yang berbeda</em>`, 
                        'warning');
                    setTimeout(() => regionSelect.focus(), 100);
                    return;
                }
                
                // Wilayah sudah dipilih - tampilkan nama file
                if (selectedFileName) {
                    selectedFileName.innerHTML = `<i class="bi bi-file-earmark-check text-success"></i> <strong>${file.name}</strong>`;
                }
                
                // AJAX upload ke backend untuk validasi dan parsing
                const formData = new FormData();
                formData.append('harga_excel', file);
                formData.append('region', regionSelect.value);
                excelPreview.style.display = 'block';
                excelPreviewTable.innerHTML = '<div class="text-center text-muted py-3"><div class="spinner-border text-info"></div><br>Memproses file...</div>';
                fetch('/prediction/upload-excel', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    // Reset input file agar user bisa upload file dengan nama sama tanpa refresh
                    hargaExcelInput.value = '';
                    if (!data.success) {
                        excelPreviewTable.innerHTML = `<div class='alert alert-danger'>${data.error}</div>`;
                        return;
                    }
                    // Preview table
                    let html = '<table class="table table-bordered table-sm mb-0"><thead><tr><th>Hari ke-</th><th>Harga</th></tr></thead><tbody>';
                    data.harga.forEach((harga, idx) => {
                        html += `<tr><td>${idx+1}</td><td>Rp ${parseInt(harga).toLocaleString('id-ID')}</td></tr>`;
                    });
                    html += '</tbody></table>';
                    excelPreviewTable.innerHTML = html;
                    // Autofill manual fields
                    for(let i=1; i<=7; i++) {
                        const input = document.getElementById(`day${i}`);
                        if (input) input.value = parseInt(data.harga[i-1]);
                    }
                })
                .catch(() => {
                    hargaExcelInput.value = '';
                    excelPreviewTable.innerHTML = '<div class="alert alert-danger">Gagal memproses file. Coba lagi.</div>';
                });
            });
        }

        function handleExcelRows(rows) {
            // Cari baris harga (tanpa header)
            let hargaRows = rows.filter(r => r && r.length && !isNaN(parseFloat(r[0])));
            if (hargaRows.length === 0 && rows.length === 8) {
                // Mungkin ada header
                hargaRows = rows.slice(1);
            }
            if (hargaRows.length !== 7) {
                excelPreview.style.display = 'block';
                excelPreviewTable.innerHTML = '<div class="alert alert-danger">File harus berisi 7 baris harga (tanpa header atau dengan header di baris pertama).</div>';
                return;
            }
            // Preview table
            let html = '<table class="table table-bordered table-sm mb-0"><thead><tr><th>Hari ke-</th><th>Harga</th></tr></thead><tbody>';
            hargaRows.forEach((row, idx) => {
                html += `<tr><td>${idx+1}</td><td>Rp ${parseInt(row[0]).toLocaleString('id-ID')}</td></tr>`;
            });
            html += '</tbody></table>';
            excelPreview.style.display = 'block';
            excelPreviewTable.innerHTML = html;
            // Autofill manual fields
            for(let i=1; i<=7; i++) {
                const input = document.getElementById(`day${i}`);
                if (input) input.value = parseInt(hargaRows[i-1][0]);
            }
        }

        // Download template Excel
        const downloadTemplate = document.getElementById('downloadTemplate');
        if (downloadTemplate) {
            downloadTemplate.addEventListener('click', function(e) {
                e.preventDefault();
                const csv = 'Harga\n26000\n26500\n27000\n27500\n28000\n28500\n29000\n';
                const blob = new Blob([csv], {type: 'text/csv'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'template_harga_7hari.csv';
                document.body.appendChild(a);
                a.click();
                setTimeout(() => { document.body.removeChild(a); URL.revokeObjectURL(url); }, 100);
            });
        }
    console.log('Prediction form loaded');
    
    // Add validation for input fields
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
            
            // Visual feedback on valid/invalid input
            input.addEventListener('blur', function() {
                const value = parseInt(this.value);
                if (this.value && !isNaN(value)) {
                    if (value >= MIN_PRICE && value <= MAX_PRICE) {
                        this.style.borderColor = '#14919b';
                        this.style.backgroundColor = '#f0fffe';
                    } else {
                        this.style.borderColor = '#dc3545';
                        this.style.backgroundColor = '#fff5f5';
                        
                        // Alert real-time untuk nilai di luar rentang
                        if (value < MIN_PRICE) {
                            const message = `Harga <strong>Hari ke-${i}</strong> terlalu rendah<br><br>` +
                                          `<span style="font-size: 20px; color: #dc3545;">Rp ${value.toLocaleString('id-ID')}</span><br><br>` +
                                          `📉 <strong>Minimal harga:</strong> Rp 10.000<br>` +
                                          `💡 Mohon masukkan harga minimal Rp 10.000`;
                            showCustomAlert('PERINGATAN!', message, 'error');
                            setTimeout(() => {
                                this.focus();
                                this.select();
                            }, 100);
                        } else if (value > MAX_PRICE) {
                            const message = `Harga <strong>Hari ke-${i}</strong> terlalu tinggi<br><br>` +
                                          `<span style="font-size: 20px; color: #dc3545;">Rp ${value.toLocaleString('id-ID')}</span><br><br>` +
                                          `📈 <strong>Maksimal harga:</strong> Rp 200.000<br>` +
                                          `💡 Mohon masukkan harga maksimal Rp 200.000`;
                            showCustomAlert('PERINGATAN!', message, 'error');
                            setTimeout(() => {
                                this.focus();
                                this.select();
                            }, 100);
                        }
                    }
                } else if (this.value) {
                    this.style.borderColor = '#dc3545';
                    this.style.backgroundColor = '#fff5f5';
                } else {
                    this.style.borderColor = '#ced4da';
                    this.style.backgroundColor = '#fff';
                }
            });
        }
    }
    
    // Form validation
    const form = document.getElementById('predictionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            let emptyFields = [];
            let tooLowFields = [];
            let tooHighFields = [];
            
            // Validasi wilayah
            const regionSelect = document.getElementById('region');
            const allPricesEmpty = areAllPriceFieldsEmpty();
            
            // Cek mode input yang aktif (Manual atau Upload File)
            const excelTabActive = document.getElementById('excel-tab')?.classList.contains('active');
            const isUploadMode = excelTabActive;
            
            // ============================================
            // KONDISI UPLOAD FILE MODE
            // ============================================
            if (isUploadMode) {
                // Kondisi 1: Wilayah belum dipilih DAN file belum diupload (harga kosong)
                if (regionSelect && (!regionSelect.value || regionSelect.value === '') && allPricesEmpty) {
                    e.preventDefault();
                    const message = `Anda belum melengkapi data untuk upload file!<br><br>` +
                                  `❌ Wilayah belum dipilih<br>` +
                                  `❌ File Excel belum diupload<br><br>` +
                                  `💡 Langkah yang harus dilakukan:<br>` +
                                  `1. Pilih wilayah prediksi terlebih dahulu<br>` +
                                  `2. Upload file Excel/CSV berisi 7 harga`;
                    showCustomAlert('UPLOAD FILE GAGAL!', message, 'warning');
                    setTimeout(() => regionSelect.focus(), 100);
                    return;
                }
                
                // Kondisi 2: Wilayah sudah dipilih TAPI file belum diupload (harga kosong)
                if (regionSelect && regionSelect.value && allPricesEmpty) {
                    e.preventDefault();
                    const message = `File Excel belum diupload!<br><br>` +
                                  `✅ Wilayah: <strong>${regionSelect.value}</strong><br>` +
                                  `❌ File belum dipilih<br><br>` +
                                  `💡 Silakan upload file Excel/CSV yang berisi 7 harga<br>` +
                                  `📁 Klik tombol "Pilih File" untuk memilih file`;
                    showCustomAlert('FILE BELUM DIUPLOAD!', message, 'warning');
                    return;
                }
                
                // Kondisi 3: Wilayah belum dipilih TAPI file sudah diupload
                if (regionSelect && (!regionSelect.value || regionSelect.value === '') && !allPricesEmpty) {
                    e.preventDefault();
                    const message = `Wilayah belum dipilih!<br><br>` +
                                  `✅ File sudah diupload<br>` +
                                  `❌ Wilayah belum dipilih<br><br>` +
                                  `📍 Mohon pilih wilayah terlebih dahulu<br>` +
                                  `💡 Setiap wilayah memiliki model prediksi yang berbeda`;
                    showCustomAlert('WILAYAH BELUM DIPILIH!', message, 'warning');
                    setTimeout(() => regionSelect.focus(), 100);
                    return;
                }
            } 
            // ============================================
            // KONDISI INPUT MANUAL MODE
            // ============================================
            else {
                // Kondisi 1: Wilayah belum dipilih DAN semua harga kosong
                if (regionSelect && (!regionSelect.value || regionSelect.value === '') && allPricesEmpty) {
                    e.preventDefault();
                    const message = `Anda belum mengisi data apapun!<br><br>` +
                                  `❌ Wilayah belum dipilih<br>` +
                                  `❌ Semua harga belum diisi<br><br>` +
                                  `💡 Mohon lengkapi data berikut:<br>` +
                                  `1. Pilih wilayah prediksi<br>` +
                                  `2. Masukkan harga 7 hari terakhir (Rp 10.000 - Rp 200.000)`;
                    showCustomAlert('FORM BELUM DIISI!', message, 'warning');
                    setTimeout(() => regionSelect.focus(), 100);
                    return;
                }
                
                // Kondisi 2: Hanya wilayah yang kosong
                if (regionSelect && (!regionSelect.value || regionSelect.value === '')) {
                    e.preventDefault();
                    const message = `Wilayah belum dipilih!<br><br>` +
                                  `📍 Mohon pilih wilayah terlebih dahulu<br>` +
                                  `💡 Pilih salah satu wilayah dari dropdown di atas`;
                    showCustomAlert('WILAYAH BELUM DIPILIH!', message, 'warning');
                    setTimeout(() => regionSelect.focus(), 100);
                    return;
                }
            }
            
            // Validasi harga (berlaku untuk kedua mode)
            for(let i = 1; i <= 7; i++) {
                const input = document.getElementById(`day${i}`);
                if (!input) continue;
                
                const value = parseInt(input.value);
                
                if (!input.value || input.value.trim() === '') {
                    emptyFields.push(`Hari ke-${i}`);
                    isValid = false;
                } else if (isNaN(value)) {
                    emptyFields.push(`Hari ke-${i} (format tidak valid)`);
                    isValid = false;
                } else if (value < MIN_PRICE) {
                    tooLowFields.push(`Hari ke-${i} (Rp ${value.toLocaleString('id-ID')})`);
                    isValid = false;
                } else if (value > MAX_PRICE) {
                    tooHighFields.push(`Hari ke-${i} (Rp ${value.toLocaleString('id-ID')})`);
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                let errorMessage = '';
                let alertTitle = 'DATA TIDAK VALID!';
                
                // Jika mode upload dan ada field kosong, ubah pesan
                if (isUploadMode && emptyFields.length > 0) {
                    alertTitle = 'FILE BELUM DIPROSES!';
                    errorMessage = `<strong>❌ Data harga belum terisi:</strong><br>`;
                    errorMessage += emptyFields.map(f => `&nbsp;&nbsp;&nbsp;• ${f}`).join('<br>') + '<br><br>';
                    errorMessage += '💡 <em>Pastikan file Excel/CSV sudah diupload dan berisi 7 baris harga.</em>';
                    showCustomAlert(alertTitle, errorMessage, 'error');
                    return;
                }
                
                if (emptyFields.length > 0) {
                    errorMessage += '<strong>❌ Field yang masih kosong:</strong><br>';
                    errorMessage += emptyFields.map(f => `&nbsp;&nbsp;&nbsp;• ${f}`).join('<br>') + '<br><br>';
                }
                
                if (tooLowFields.length > 0) {
                    errorMessage += '<strong>📉 Harga terlalu rendah (Minimal Rp 10.000):</strong><br>';
                    errorMessage += tooLowFields.map(f => `&nbsp;&nbsp;&nbsp;• ${f}`).join('<br>') + '<br><br>';
                }
                
                if (tooHighFields.length > 0) {
                    errorMessage += '<strong>📈 Harga terlalu tinggi (Maksimal Rp 200.000):</strong><br>';
                    errorMessage += tooHighFields.map(f => `&nbsp;&nbsp;&nbsp;• ${f}`).join('<br>') + '<br><br>';
                }
                
                errorMessage += '💡 <em>Mohon perbaiki data di atas agar dapat melanjutkan prediksi.</em>';
                
                showCustomAlert(alertTitle, errorMessage, 'error');
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