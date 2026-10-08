document.addEventListener('DOMContentLoaded', function() {
    let html5QrcodeScanner = null;
    const scanResultOverlay = document.getElementById('scan-result');
    const scanIcon = document.getElementById('scan-icon');
    const scanMessage = document.getElementById('scan-message');
    const scanName = document.getElementById('scan-participant-name');
    const statsCheckedIn = document.getElementById('stats-checked-in');
    const recentTbody = document.getElementById('recent-attendance-tbody');
    const recentCount = document.getElementById('recent-count');
    
    let isScanning = true;
    let scanTimeout = null;

    // Web Audio Beeper for instant feedback
    function playBeep(type = 'success') {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.15);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.2);
            } else if (type === 'warning') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(440, ctx.currentTime);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            } else {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(220, ctx.currentTime);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            }
        } catch (e) {
            // Audio context not allowed or not supported
        }
    }

    // Helper: Escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // Initialize Camera Scanner
    function initScanner() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            {
                fps: 15,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    // 0.72 creates a spacious ~49px unused margin around the scanning box
                    const edge = Math.max(180, Math.floor(minEdge * 0.72));
                    return { width: edge, height: edge };
                },
                aspectRatio: 1.0
            },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Translate and style HTML5 QR Code Scanner UI
        translateScannerUI();
    }

    function translateScannerUI() {
        const readerEl = document.getElementById('reader');

        const observer = new MutationObserver(function() {
            // Stop Button & Scanning state
            const stopBtn = document.getElementById('html5-qrcode-button-camera-stop') || document.getElementById('reader__camera_stop_button');
            if (stopBtn && stopBtn.style.display !== 'none' && !stopBtn.hidden) {
                if (readerEl && !readerEl.classList.contains('is-scanning')) {
                    readerEl.classList.add('is-scanning');
                }
                if (!stopBtn.querySelector('.bi-stop-circle')) {
                    stopBtn.innerHTML = '<i class="bi bi-stop-circle"></i><span>Stop Scanning</span>';
                }
            } else {
                if (readerEl && readerEl.classList.contains('is-scanning')) {
                    readerEl.classList.remove('is-scanning');
                }
            }

            // Camera Permission Button
            const permBtn = document.getElementById('html5-qrcode-button-camera-permission') || document.getElementById('reader__camera_permission_button');
            if (permBtn && !permBtn.dataset.translated) {
                permBtn.dataset.translated = "true";
                permBtn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> អនុញ្ញាតបើកកាមេរ៉ាស្កេន';
                permBtn.className = "btn btn-primary fw-bold px-3 py-2";
            }

            // File scan link
            const fileScanLink = document.getElementById('html5-qrcode-anchor-scan-type-change') || document.querySelector('#reader__dashboard_section_fsr a');
            if (fileScanLink && !fileScanLink.dataset.translated) {
                fileScanLink.dataset.translated = "true";
                fileScanLink.innerHTML = '<i class="bi bi-image me-1"></i> ជ្រើសរើសរូបថតកូដ QR ពីកុំព្យូទ័រ';
            }

            // Start Camera Button
            const startBtn = document.getElementById('html5-qrcode-button-camera-start') || document.getElementById('reader__camera_start_button');
            if (startBtn && !startBtn.dataset.translated) {
                startBtn.dataset.translated = "true";
                startBtn.innerHTML = '<i class="bi bi-camera-video me-1"></i> ចាប់ផ្តើមកាមេរ៉ា';
                startBtn.className = "btn btn-success fw-bold px-3 py-2";
            }
        });

        if (readerEl) {
            observer.observe(readerEl, { childList: true, subtree: true, attributes: true });
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (!isScanning) return;
        isScanning = false;
        
        if (html5QrcodeScanner) {
            try { html5QrcodeScanner.pause(); } catch(e){}
        }

        processScan(decodedText);
    }

    function onScanFailure(error) {
        // ignore scan ticks
    }

    function processScan(token) {
        let fd = new FormData();
        fd.append('_csrf_token', CSRF_TOKEN);
        fd.append('token', token);
        fd.append('workshop_id', WORKSHOP_ID);
        fd.append('scan_type', 'checkin');

        fetch(APP_URL + '/api/checkin/scan', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            showResult(data);
            updateParticipantCard(data);
            if (data.success && !data.already_in && data.participant) {
                addRecentCheckin(data.participant);
            }
        })
        .catch(err => {
            console.error(err);
            showResult({success: false, message: 'បញ្ហាភ្ជាប់បណ្តាញ ឬប្រព័ន្ធមិនឆ្លើយតប។'});
        });
    }

    function showResult(data) {
        scanResultOverlay.className = 'scan-result-overlay';
        scanResultOverlay.style.display = 'flex';
        
        if (data.success && !data.already_in) {
            playBeep('success');
            scanResultOverlay.classList.add('scan-success');
            scanIcon.className = 'bi bi-check-circle-fill display-1 mb-2';
            
            // update stats
            if (statsCheckedIn) {
                let parts = statsCheckedIn.innerText.split('/');
                if (parts.length === 2) {
                    statsCheckedIn.innerText = (parseInt(parts[0].trim()) + 1) + ' / ' + parts[1].trim();
                }
            }
        } else if (data.success && data.already_in) {
            playBeep('warning');
            scanResultOverlay.classList.add('scan-warning');
            scanIcon.className = 'bi bi-exclamation-triangle-fill display-1 mb-2';
        } else {
            playBeep('error');
            scanResultOverlay.classList.add('scan-error');
            scanIcon.className = 'bi bi-x-circle-fill display-1 mb-2';
        }

        scanMessage.innerText = data.message || 'កំហុស';
        scanName.innerText = data.participant ? data.participant.name : '';

        const fastMode = document.getElementById('fastModeToggle') ? document.getElementById('fastModeToggle').checked : true;
        
        if (scanTimeout) clearTimeout(scanTimeout);
        
        if (fastMode) {
            scanTimeout = setTimeout(resetScanner, 2000);
        } else {
            scanResultOverlay.onclick = resetScanner;
        }
    }

    function resetScanner() {
        scanResultOverlay.style.display = 'none';
        scanResultOverlay.onclick = null;
        isScanning = true;
        if (html5QrcodeScanner) {
            try { html5QrcodeScanner.resume(); } catch(e){}
        }
    }

    let currentScannedParticipant = null;

    function updateParticipantCard(data) {
        if (!data.participant) return;
        
        currentScannedParticipant = data.participant;
        const waitingState = document.getElementById('waiting-state');
        const card = document.getElementById('participant-info-card');
        if (waitingState) waitingState.style.display = 'none';
        if (card) card.style.display = 'block';
        
        const p = data.participant;
        document.getElementById('pi-name').innerText = p.name || '';
        document.getElementById('pi-company').innerText = p.company || 'មិនបានបញ្ជាក់ស្ថាប័ន';
        document.getElementById('pi-ticket').innerText = p.ticket || 'ទូទៅ';
        document.getElementById('pi-code').innerText = p.reg_code || '';
        document.getElementById('pi-phone').innerText = p.phone || '-';
        document.getElementById('pi-idcard').innerText = p.id_card || '-';
        document.getElementById('pi-payment').innerText = p.payment_status || 'បានបង់រួច';
        
        const provEl = document.getElementById('pi-province');
        if (provEl) {
            provEl.innerText = p.province || 'ទូទៅ';
        }

        const photoImg = document.getElementById('pi-photo-img');
        const initialsEl = document.getElementById('pi-initials');
        const photoBadge = document.getElementById('pi-photo-badge');

        if (p.photo_url) {
            photoImg.src = p.photo_url;
            photoImg.style.display = 'inline-block';
            initialsEl.style.display = 'none';
            if (photoBadge) photoBadge.style.display = 'inline-block';
        } else {
            photoImg.style.display = 'none';
            initialsEl.style.display = 'inline-flex';
            let initials = p.name ? p.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : '?';
            initialsEl.innerText = initials;
            if (photoBadge) photoBadge.style.display = 'none';
        }
        
        const vipEl = document.getElementById('pi-vip');
        if (vipEl) vipEl.style.display = p.is_vip ? 'inline-block' : 'none';

        const msg = document.getElementById('already-in-msg');
        if (msg) {
            if (window._msgHideTimer) {
                clearTimeout(window._msgHideTimer);
                window._msgHideTimer = null;
            }
            msg.style.display = 'block';
            msg.style.opacity = '1';
            msg.style.transition = 'opacity 0.4s ease';

            if (data.already_in) {
                msg.className = 'alert alert-warning py-2 mb-3';
                msg.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'បានស្កេនរួចហើយ');
            } else if (data.success) {
                msg.className = 'alert alert-success py-2 mb-3';
                msg.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (data.message || 'កត់ត្រាវត្តមានជោគជ័យ');

                // Auto hide successful banner after 3 seconds
                window._msgHideTimer = setTimeout(() => {
                    msg.style.opacity = '0';
                    setTimeout(() => {
                        msg.style.display = 'none';
                        msg.style.opacity = '1';
                    }, 400);
                }, 3000);
            } else {
                msg.className = 'alert alert-danger py-2 mb-3';
                msg.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + (data.message || 'កំហុស');
            }
        }

        const colReset = document.getElementById('col-reset-attendance');
        const btnReset = document.getElementById('btn-reset-attendance');
        if (btnReset) {
            if (p.reg_id && (data.already_in || data.success)) {
                if (colReset) colReset.style.display = 'block';
                btnReset.style.display = 'block';
                btnReset.onclick = function() {
                    if (confirm(`តើអ្នកពិតជាចង់លុបវត្តមាន (Reset) របស់ ${p.name || ''} មែនទេ?`)) {
                        resetAttendance(p.reg_id);
                    }
                };
            } else {
                if (colReset) colReset.style.display = 'none';
                btnReset.style.display = 'none';
            }
        }
    }

    function addRecentCheckin(p) {
        if (!recentTbody) return;
        const noRecentRow = document.getElementById('no-recent-row');
        if (noRecentRow) noRecentRow.remove();

        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' + 
                        String(now.getMinutes()).padStart(2, '0') + ':' + 
                        String(now.getSeconds()).padStart(2, '0');

        const tr = document.createElement('tr');
        tr.className = 'table-success';
        tr.dataset.regId = p.reg_id || '';
        tr.dataset.regCode = p.reg_code || '';
        tr.innerHTML = `
            <td class="ps-3 fw-bold text-dark text-nowrap">${escapeHtml(p.name)}</td>
            <td><span class="badge bg-primary-subtle text-primary border">${escapeHtml(p.province || 'ទូទៅ')}</span></td>
            <td class="text-muted small text-truncate" style="max-width: 130px;" title="${escapeHtml(p.company || '-')}">${escapeHtml(p.company || '-')}</td>
            <td class="text-muted small text-truncate" style="max-width: 100px;" title="${escapeHtml(p.position || '-')}">${escapeHtml(p.position || '-')}</td>
            <td class="text-success fw-bold text-nowrap">${timeStr}</td>
            <td class="text-end pe-3 text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-danger fw-normal" style="font-size: 0.75rem; padding: 3px 7px; line-height: 1.2;" title="លុបវត្តមាន (Reset)" onclick="resetAttendance(${p.reg_id})"><i class="bi bi-arrow-counterclockwise" style="margin-right: 2px;"></i>លុបវត្តមាន</button>
            </td>
        `;
        recentTbody.prepend(tr);
        setTimeout(() => tr.classList.remove('table-success'), 3000);

        if (recentCount) {
            let current = parseInt(recentCount.innerText) || 0;
            recentCount.innerText = (current + 1) + ' នាក់';
        }
    }

    // Manual Search & Check-in
    const manualInput = document.getElementById('manual-input');
    const manualBtn = document.getElementById('btn-manual-search');
    const manualResults = document.getElementById('manual-results');
    let searchDebounce = null;

    function performManualSearch() {
        const q = manualInput.value.trim();
        if (!q) {
            manualResults.style.display = 'none';
            manualResults.innerHTML = '';
            return;
        }

        let fd = new FormData();
        fd.append('_csrf_token', CSRF_TOKEN);
        fd.append('workshop_id', WORKSHOP_ID);
        fd.append('query', q);

        fetch(APP_URL + '/api/checkin/search', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                manualResults.style.display = 'block';
                manualResults.innerHTML = '<div class="alert alert-light border small text-muted text-center py-2 mb-0">រកមិនឃើញសិក្ខាកាមត្រូវគ្នានោះទេ។</div>';
                return;
            }

            manualResults.style.display = 'block';
            let html = '<div class="list-group list-group-flush border rounded shadow-sm">';
            res.data.forEach(p => {
                const isChecked = !!p.checked_in_at;
                const tokenOrCode = p.token || p.registration_code;
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <div>
                            <div class="fw-bold text-dark">${escapeHtml(p.name)} <span class="badge bg-light text-dark border font-monospace ms-1">${escapeHtml(p.registration_code)}</span></div>
                            <div class="small text-muted">${escapeHtml(p.phone || '')} ${p.company ? '• ' + escapeHtml(p.company) : ''}</div>
                        </div>
                        <div>
                            ${isChecked 
                                ? `<div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-success-subtle text-success border border-success small"><i class="bi bi-check2-circle me-1"></i> បានស្កេនរួច</span>
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2" title="លុបវត្តមាន (Reset)" onclick="resetAttendance(${p.registration_id})"><i class="bi bi-arrow-counterclockwise"></i></button>
                                   </div>`
                                : `<button class="btn btn-sm btn-success fw-bold px-3" onclick="checkinByCode('${escapeHtml(tokenOrCode)}')"><i class="bi bi-check-lg me-1"></i> កត់ត្រា</button>`
                            }
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            manualResults.innerHTML = html;
        })
        .catch(err => console.error(err));
    }

    if (manualInput) {
        manualInput.addEventListener('input', function() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(performManualSearch, 300);
        });

        manualInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performManualSearch();
            }
        });
    }

    if (manualBtn) {
        manualBtn.addEventListener('click', performManualSearch);
    }

    window.checkinByCode = function(tokenOrCode) {
        processScan(tokenOrCode);
        if (manualResults) manualResults.style.display = 'none';
        if (manualInput) manualInput.value = '';
    };

    window.resetAttendance = function(regId) {
        if (!regId) return;
        let fd = new FormData();
        fd.append('_csrf_token', CSRF_TOKEN);
        fd.append('workshop_id', WORKSHOP_ID);
        fd.append('registration_id', regId);

        fetch(APP_URL + '/api/checkin/reset', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // 1. Remove attendee row from recent attendance table
                if (recentTbody) {
                    const rows = recentTbody.querySelectorAll('tr');
                    let removed = false;
                    const curCode = (currentScannedParticipant && currentScannedParticipant.reg_code) ? currentScannedParticipant.reg_code : null;
                    rows.forEach(r => {
                        const rId = r.dataset.regId || r.getAttribute('data-reg-id');
                        const rCode = r.dataset.regCode || r.getAttribute('data-reg-code');
                        const cellText = r.innerText || '';
                        if ((rId && String(rId) === String(regId)) || (rCode && rCode === curCode) || (curCode && cellText.includes(curCode))) {
                            r.remove();
                            removed = true;
                        }
                    });
                    if (removed && recentCount) {
                        let current = parseInt(recentCount.innerText) || 0;
                        let updated = Math.max(0, current - 1);
                        recentCount.innerText = updated + ' នាក់';
                    }
                    if (recentTbody.querySelectorAll('tr').length === 0) {
                        recentTbody.innerHTML = '<tr id="no-recent-row"><td colspan="6" class="text-center py-4 text-muted small">មិនទាន់មានការស្កេនវត្តមាននៅឡើយទេ។</td></tr>';
                    }
                }

                // 2. Decrement top stats counter
                if (statsCheckedIn) {
                    let parts = statsCheckedIn.innerText.split('/');
                    if (parts.length === 2) {
                        let cur = Math.max(0, parseInt(parts[0].trim()) - 1);
                        statsCheckedIn.innerText = cur + ' / ' + parts[1].trim();
                    }
                }

                // 3. Show message and return to ready state
                const msg = document.getElementById('already-in-msg');
                if (msg) {
                    msg.style.display = 'block';
                    msg.className = 'alert alert-secondary py-2 mb-3';
                    msg.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> ' + (res.message || 'បានកំណត់វត្តមានឡើងវិញដោយជោគជ័យ');
                }
                const colReset = document.getElementById('col-reset-attendance');
                if (colReset) colReset.style.display = 'none';
                const btnReset = document.getElementById('btn-reset-attendance');
                if (btnReset) btnReset.style.display = 'none';

                // Automatically return to waiting state after 2 seconds
                setTimeout(() => {
                    const card = document.getElementById('participant-info-card');
                    const waitingState = document.getElementById('waiting-state');
                    if (card && waitingState) {
                        card.style.display = 'none';
                        waitingState.style.display = 'block';
                    }
                    currentScannedParticipant = null;
                }, 2000);

                // 4. Refresh manual search if active
                if (manualInput && manualInput.value.trim()) {
                    performManualSearch();
                }
            } else {
                alert(res.message || 'មិនអាចកំណត់វត្តមានឡើងវិញបានទេ។');
            }
        })
        .catch(err => {
            console.error(err);
            alert('បញ្ហាបណ្តាញពេលលុបវត្តមាន');
        });
    };

    // Badge Printing Modal Handler
    const btnPrintBadge = document.getElementById('btn-print-badge');
    if (btnPrintBadge) {
        btnPrintBadge.addEventListener('click', function() {
            if (!currentScannedParticipant) return;
            const p = currentScannedParticipant;

            document.getElementById('badge-name').innerText = p.name || '';
            document.getElementById('badge-province').innerText = p.province || 'ទូទៅ';
            document.getElementById('badge-company').innerText = p.company || '';
            document.getElementById('badge-code').innerText = p.reg_code || '';
            document.getElementById('badge-qr').src = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(p.reg_code)}`;

            const bPhotoImg = document.getElementById('badge-photo-img');
            const bPhotoPlaceholder = document.getElementById('badge-photo-placeholder');
            if (p.photo_url) {
                bPhotoImg.src = p.photo_url;
                bPhotoImg.style.display = 'inline-block';
                bPhotoPlaceholder.style.display = 'none';
            } else {
                bPhotoImg.style.display = 'none';
                bPhotoPlaceholder.style.display = 'inline-flex';
            }

            const modal = new bootstrap.Modal(document.getElementById('badgeModal'));
            modal.show();
        });
    }

    window.printBadge = function() {
        const printContents = document.getElementById('printable-badge-area').innerHTML;
        const win = window.open('', '', 'height=600,width=450');
        win.document.write('<html><head><title>Print Badge</title>');
        win.document.write('<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap" rel="stylesheet">');
        win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        win.document.write('<style>body{font-family:"Kantumruy Pro", sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0;} @page{size:80mm 110mm; margin:5mm;}</style>');
        win.document.write('</head><body>');
        win.document.write(printContents);
        win.document.write('</body></html>');
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); win.close(); }, 500);
    };

    initScanner();
});
