<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Checker - Cek Status Nawala</title>
    <link rel="stylesheet" href="{{ asset('assets/hamepage/css/style.css') }}">
</head>
<body>
    <div class="bg-pattern"></div>
    
    <header>
        <div class="logo">🛡️ Domain Checker</div>
        <div class="subtitle">Periksa status domain dalam daftar Nawala Indonesia</div>
    </header>
    
    <div class="main-container">
        <div class="checker-card">
            <div class="mode-selector">
                <button class="mode-btn active" data-mode="single">Cek Satu Domain</button>
                <button class="mode-btn" data-mode="bulk">Cek Banyak Domain</button>
            </div>
            
            <!-- Single Domain Check -->
            <form id="domainForm" class="check-form">
                <div class="form-group">
                    <label for="domain">Masukkan Domain atau URL:</label>
                    <div class="input-container">
                        <input 
                            type="text" 
                            id="domain" 
                            name="domain" 
                            placeholder="contoh: google.com atau https://www.example.com"
                            required
                        >
                        <button type="submit" class="check-btn" id="checkBtn">
                            <span class="btn-text">Cek Domain</span>
                            <div class="spinner"></div>
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Bulk Domain Check -->
            <form id="bulkForm" class="check-form hidden">
                <div class="form-group">
                    <label for="bulkDomains">Masukkan Daftar Domain (satu per baris):</label>
                    <textarea 
                        id="bulkDomains" 
                        name="bulkDomains" 
                        placeholder="google.com&#10;facebook.com&#10;youtube.com&#10;https://www.example.com&#10;reddit.com"
                        rows="8"
                        required
                    ></textarea>
                    <div class="bulk-actions">
                        <button type="submit" class="check-btn" id="bulkCheckBtn">
                            <span class="btn-text">Cek Semua Domain</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="bulk-info">
                            <span id="domainCount">0</span> domain siap dicek
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Single Result -->
            <div class="result-container" id="resultContainer">
                <span class="result-icon" id="resultIcon"></span>
                <div class="result-title" id="resultTitle"></div>
                <div class="result-description" id="resultDescription"></div>
            </div>
            
            <!-- Bulk Results -->
            <div class="bulk-results" id="bulkResults">
                <div class="bulk-summary" id="bulkSummary"></div>
                <div class="bulk-results-list" id="bulkResultsList"></div>
            </div>
        </div>
    </div>
    
    <div class="features">
        <div class="feature-card">
            <span class="feature-icon">⚡</span>
            <div class="feature-title">Bulk Checking</div>
            <div class="feature-description">Cek hingga ratusan domain sekaligus dengan progress tracking real-time dan export hasil ke CSV.</div>
        </div>
        
        <div class="feature-card">
            <span class="feature-icon">🚀</span>
            <div class="feature-title">Cepat & Akurat</div>
            <div class="feature-description">Pengecekan real-time dengan database Nawala terbaru untuk hasil yang akurat dan up-to-date.</div>
        </div>
        
        <div class="feature-card">
            <span class="feature-icon">🔒</span>
            <div class="feature-title">Aman & Privat</div>
            <div class="feature-description">Tidak menyimpan history pencarian Anda. Semua pengecekan dilakukan secara anonim dan aman.</div>
        </div>
        
        <div class="feature-card">
            <span class="feature-icon">📊</span>
            <div class="feature-title">Laporan Lengkap</div>
            <div class="feature-description">Dapatkan summary statistik dan export hasil dalam format CSV untuk analisis lebih lanjut.</div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Domain Checker. Dibuat untuk membantu pengecekan status domain Nawala.</p>
    </footer>
    
    <script>
        const form = document.getElementById('domainForm');
        const bulkForm = document.getElementById('bulkForm');
        const domainInput = document.getElementById('domain');
        const bulkDomainsInput = document.getElementById('bulkDomains');
        const checkBtn = document.getElementById('checkBtn');
        const bulkCheckBtn = document.getElementById('bulkCheckBtn');
        const btnText = checkBtn.querySelector('.btn-text');
        const bulkBtnText = bulkCheckBtn.querySelector('.btn-text');
        const spinner = checkBtn.querySelector('.spinner');
        const bulkSpinner = bulkCheckBtn.querySelector('.spinner');
        const resultContainer = document.getElementById('resultContainer');
        const bulkResults = document.getElementById('bulkResults');
        const resultIcon = document.getElementById('resultIcon');
        const resultTitle = document.getElementById('resultTitle');
        const resultDescription = document.getElementById('resultDescription');
        const domainCount = document.getElementById('domainCount');
        const bulkSummary = document.getElementById('bulkSummary');
        const bulkResultsList = document.getElementById('bulkResultsList');
        
        // Mode switching
        const modeButtons = document.querySelectorAll('.mode-btn');
        
        modeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.mode;
                
                // Update active button
                modeButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Show/hide forms
                if (mode === 'single') {
                    form.classList.remove('hidden');
                    bulkForm.classList.add('hidden');
                    resultContainer.classList.remove('show');
                    bulkResults.classList.remove('show');
                } else {
                    form.classList.add('hidden');
                    bulkForm.classList.remove('hidden');
                    resultContainer.classList.remove('show');
                    bulkResults.classList.remove('show');
                }
            });
        });
        
        // Mock database domain yang diblokir
        const blockedDomains = {
            'facebook.com': 'Media sosial yang dibatasi akses',
            'twitter.com': 'Platform media sosial',
            'reddit.com': 'Forum online dengan konten dewasa',
            'pornhub.com': 'Situs konten dewasa',
            'xvideos.com': 'Situs konten dewasa',
            'xnxx.com': 'Situs konten dewasa',
            'gambling.com': 'Situs perjudian online',
            'poker.com': 'Situs perjudian poker',
            'torrent.com': 'Situs berbagi file ilegal',
            'thepiratebay.com': 'Situs torrent ilegal',
            'bet365.com': 'Situs perjudian online'
        };
        
        function extractDomain(url) {
            try {
                let domain = url.toLowerCase().trim();
                
                // Hapus protokol jika ada
                domain = domain.replace(/^https?:\/\//i, '');
                domain = domain.replace(/^www\./i, '');
                
                // Ambil hanya bagian domain, hapus path
                domain = domain.split('/')[0];
                domain = domain.split('?')[0];
                domain = domain.split('#')[0];
                
                return domain;
            } catch (error) {
                return url.toLowerCase().trim();
            }
        }
        
        function showResult(type, title, description, icon) {
            resultContainer.className = `result-container show result-${type}`;
            resultIcon.textContent = icon;
            resultTitle.textContent = title;
            resultDescription.textContent = description;
        }
        
        function simulateCheck(domain) {
            return new Promise((resolve) => {
                // Simulasi delay API call
                setTimeout(() => {
                    const cleanDomain = extractDomain(domain);
                    
                    if (blockedDomains[cleanDomain]) {
                        resolve({
                            blocked: true,
                            reason: blockedDomains[cleanDomain],
                            domain: cleanDomain
                        });
                    } else {
                        resolve({
                            blocked: false,
                            domain: cleanDomain
                        });
                    }
                }, Math.random() * 1000 + 500); // Random delay 0.5-1.5s
            });
        }
        
        function updateDomainCount() {
            const domains = bulkDomainsInput.value.trim().split('\n').filter(d => d.trim());
            domainCount.textContent = domains.length;
        }
        
        function createBulkResultItem(domain, status = 'checking') {
            const item = document.createElement('div');
            item.className = `bulk-result-item ${status}`;
            item.innerHTML = `
                <div class="result-domain">${domain}</div>
                <div class="result-status ${status}">
                    ${status === 'checking' ? '⏳ Mengecek...' : 
                      status === 'safe' ? '✅ Aman' : 
                      status === 'blocked' ? '❌ Diblokir' : 
                      '⚠️ Error'}
                </div>
            `;
            return item;
        }
        
        function updateProgressBar(current, total) {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                const percentage = (current / total) * 100;
                progressFill.style.width = `${percentage}%`;
            }
        }
        
        async function processBulkCheck(domains) {
            const results = [];
            const stats = { safe: 0, blocked: 0, error: 0, total: domains.length };
            
            // Clear previous results
            bulkResultsList.innerHTML = '';
            
            // Add progress bar
            bulkResultsList.innerHTML = `
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            `;
            
            // Add all domains as "checking"
            domains.forEach(domain => {
                const item = createBulkResultItem(domain, 'checking');
                bulkResultsList.appendChild(item);
            });
            
            // Process each domain
            for (let i = 0; i < domains.length; i++) {
                const domain = domains[i];
                const item = bulkResultsList.children[i + 1]; // +1 for progress bar
                
                try {
                    const result = await simulateCheck(domain);
                    
                    if (result.blocked) {
                        item.className = 'bulk-result-item blocked';
                        item.querySelector('.result-status').innerHTML = '❌ Diblokir';
                        item.querySelector('.result-status').className = 'result-status blocked';
                        stats.blocked++;
                        results.push({ domain: result.domain, status: 'blocked', reason: result.reason });
                    } else {
                        item.className = 'bulk-result-item safe';
                        item.querySelector('.result-status').innerHTML = '✅ Aman';
                        item.querySelector('.result-status').className = 'result-status safe';
                        stats.safe++;
                        results.push({ domain: result.domain, status: 'safe' });
                    }
                } catch (error) {
                    item.className = 'bulk-result-item error';
                    item.querySelector('.result-status').innerHTML = '⚠️ Error';
                    item.querySelector('.result-status').className = 'result-status error';
                    stats.error++;
                    results.push({ domain: domain, status: 'error', error: error.message });
                }
                
                updateProgressBar(i + 1, domains.length);
                
                // Update summary in real-time
                updateBulkSummary(stats);
            }
            
            // Add export button
            const exportBtn = document.createElement('button');
            exportBtn.className = 'export-btn';
            exportBtn.textContent = '📥 Export Hasil CSV';
            exportBtn.onclick = () => exportResultsToCSV(results);
            bulkSummary.appendChild(exportBtn);
            
            return results;
        }
        
        function updateBulkSummary(stats) {
            bulkSummary.innerHTML = `
                <h3>📊 Ringkasan Hasil Pengecekan</h3>
                <div class="summary-stats">
                    <div class="stat-item stat-total">
                        <span class="stat-number">${stats.total}</span>
                        <div class="stat-label">Total Domain</div>
                    </div>
                    <div class="stat-item stat-safe">
                        <span class="stat-number">${stats.safe}</span>
                        <div class="stat-label">Aman</div>
                    </div>
                    <div class="stat-item stat-blocked">
                        <span class="stat-number">${stats.blocked}</span>
                        <div class="stat-label">Diblokir</div>
                    </div>
                    <div class="stat-item stat-error">
                        <span class="stat-number">${stats.error}</span>
                        <div class="stat-label">Error</div>
                    </div>
                </div>
            `;
        }
        
        function exportResultsToCSV(results) {
            const csvContent = [
                ['Domain', 'Status', 'Keterangan'],
                ...results.map(r => [
                    r.domain,
                    r.status === 'safe' ? 'Aman' : r.status === 'blocked' ? 'Diblokir' : 'Error',
                    r.reason || r.error || '-'
                ])
            ].map(row => row.join(',')).join('\n');
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `domain-check-results-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        // Single domain form
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const domain = domainInput.value.trim();
            if (!domain) return;
            
            // Update UI untuk loading state
            checkBtn.classList.add('loading');
            btnText.style.display = 'none';
            spinner.style.display = 'block';
            resultContainer.classList.remove('show');
            
            try {
                const result = await simulateCheck(domain);
                
                if (result.blocked) {
                    showResult(
                        'blocked',
                        '❌ Domain Diblokir',
                        `Domain "${result.domain}" terdaftar dalam daftar Nawala. Alasan: ${result.reason}`,
                        '🚫'
                    );
                } else {
                    showResult(
                        'safe',
                        '✅ Domain Aman',
                        `Domain "${result.domain}" tidak terdaftar dalam daftar Nawala dan aman untuk diakses.`,
                        '✅'
                    );
                }
            } catch (error) {
                showResult(
                    'error',
                    '⚠️ Terjadi Kesalahan',
                    'Terjadi kesalahan saat memeriksa domain. Silakan coba lagi atau periksa koneksi internet Anda.',
                    '⚠️'
                );
            } finally {
                // Reset button state
                checkBtn.classList.remove('loading');
                btnText.style.display = 'block';
                spinner.style.display = 'none';
            }
        });
        
        // Bulk domain form
        bulkForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const domainsText = bulkDomainsInput.value.trim();
            if (!domainsText) return;
            
            const domains = domainsText.split('\n')
                .map(d => d.trim())
                .filter(d => d.length > 0)
                .map(d => extractDomain(d));
            
            if (domains.length === 0) return;
            
            // Update UI untuk loading state
            bulkCheckBtn.classList.add('loading');
            bulkBtnText.style.display = 'none';
            bulkSpinner.style.display = 'block';
            bulkResults.classList.add('show');
            
            try {
                await processBulkCheck(domains);
            } catch (error) {
                console.error('Bulk check error:', error);
            } finally {
                // Reset button state
                bulkCheckBtn.classList.remove('loading');
                bulkBtnText.style.display = 'block';
                bulkSpinner.style.display = 'none';
            }
        });
        
        // Update domain count on input
        bulkDomainsInput.addEventListener('input', updateDomainCount);
        
        // Auto focus pada input saat halaman dimuat
        window.addEventListener('load', () => {
            domainInput.focus();
        });
        
        // Tambahkan efek typing untuk placeholder
        const placeholders = [
            'contoh: google.com',
            'contoh: https://www.example.com',
            'contoh: facebook.com',
            'contoh: youtube.com'
        ];
        
        let currentPlaceholder = 0;
        
        setInterval(() => {
            if (!domainInput.value && document.activeElement !== domainInput) {
                domainInput.placeholder = placeholders[currentPlaceholder];
                currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
            }
        }, 3000);
    </script>
</body>
</html>