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

    // Modern & Beautiful Dialogs for Workshop OS (SweetAlert2)
    const AppDialog = {
        toast: function(message, icon = 'success') {
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2600,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
                Toast.fire({
                    icon: icon,
                    title: message
                });
            } else {
                console.log(message);
            }
        },

        confirm: async function(options) {
            if (typeof Swal === 'undefined') {
                return window.confirm(options.text || options.title || '');
            }

            const isDanger = options.isDanger || false;
            const result = await Swal.fire({
                title: options.title || 'បញ្ជាក់ការសម្រេចចិត្ត',
                html: options.html || (options.text ? `<div>${options.text.replace(/\n/g, '<br>')}</div>` : ''),
                icon: options.icon || (isDanger ? 'warning' : 'question'),
                showCancelButton: true,
                confirmButtonText: options.confirmText || (isDanger ? '<i class="bi bi-trash3-fill me-1"></i> យល់ព្រម' : '<i class="bi bi-check-circle-fill me-1"></i> យល់ព្រម'),
                cancelButtonText: options.cancelText || 'បោះបង់',
                confirmButtonColor: options.confirmColor || (isDanger ? '#dc3545' : (options.isSuccess ? '#198754' : '#0d6efd')),
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: isDanger,
                buttonsStyling: true
            });

            return result.isConfirmed;
        },

        alert: function(title, text, icon = 'info') {
            if (typeof Swal === 'undefined') {
                window.alert((title ? title + ': ' : '') + text);
                return Promise.resolve();
            }

            return Swal.fire({
                title: title || (icon === 'error' ? 'មានបញ្ហា!' : 'ជោគជ័យ!'),
                html: text ? `<div>${text.replace(/\n/g, '<br>')}</div>` : '',
                icon: icon,
                confirmButtonText: '<i class="bi bi-check2 me-1"></i> យល់ព្រម',
                confirmButtonColor: icon === 'error' ? '#dc3545' : '#0d6efd',
                buttonsStyling: true
            });
        },

        success: function(title, text) {
            return this.alert(title, text, 'success');
        },

        error: function(title, text) {
            return this.alert(title, text, 'error');
        }
    };

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
            } else if (type === 'focus') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1400, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.08);
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
        const preferredCam = localStorage.getItem('workshopos_selected_camera_id');
        if (preferredCam) {
            try {
                localStorage.setItem('html5qrcode__last_used_camera_id', preferredCam);
            } catch(e){}
        }

        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            {
                fps: 20,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    // 0.72 creates a spacious ~49px unused margin around the scanning box
                    const edge = Math.max(180, Math.floor(minEdge * 0.72));
                    return { width: edge, height: edge };
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    width: { min: 640, ideal: 1920 },
                    height: { min: 480, ideal: 1080 },
                    facingMode: "environment"
                },
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Translate and style HTML5 QR Code Scanner UI
        translateScannerUI();

        // Enumerate Cameras and Setup Switcher & Zoom
        initCameraDevicesAndZoom();
    }

    function initCameraDevicesAndZoom() {
        if (typeof Html5Qrcode !== 'undefined' && Html5Qrcode.getCameras) {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length > 0) {
                    const select = document.getElementById('camera-select');
                    const wrapper = document.getElementById('camera-select-wrapper');
                    if (select && wrapper) {
                        select.innerHTML = '';
                        let savedCam = localStorage.getItem('workshopos_selected_camera_id');
                        let logiDev = null;

                        devices.forEach(dev => {
                            const opt = document.createElement('option');
                            opt.value = dev.id;
                            opt.textContent = dev.label || ('កាមេរ៉ា ' + dev.id.substring(0, 8));
                            if (savedCam && dev.id === savedCam) {
                                opt.selected = true;
                            } else if (!savedCam && /logi|logitech|1080|c92|c93|brio/i.test(dev.label)) {
                                opt.selected = true;
                                logiDev = dev.id;
                            }
                            select.appendChild(opt);
                        });

                        if (devices.length > 1) {
                            wrapper.style.display = 'block';
                        }

                        // Auto-switch to Logi 1080p if found and not yet explicitly set
                        if (!savedCam && logiDev && devices.length > 1) {
                            localStorage.setItem('workshopos_selected_camera_id', logiDev);
                            localStorage.setItem('html5qrcode__last_used_camera_id', logiDev);
                        }

                        select.addEventListener('change', function() {
                            const chosenId = this.value;
                            localStorage.setItem('workshopos_selected_camera_id', chosenId);
                            localStorage.setItem('html5qrcode__last_used_camera_id', chosenId);
                            location.reload();
                        });
                    }
                }
            }).catch(e => console.warn('Camera enumeration error:', e));
        }

        // Setup Zoom Controls when video is running
        setupZoomControls();
    }

    // Zoom management: 1x, 2x, 3x, 5x & Auto Focus
    let currentZoomLevel = 1;

    function applyZoom(zVal) {
        currentZoomLevel = zVal;

        // Update active class on zoom buttons (only buttons with data-zoom)
        document.querySelectorAll('.btn-zoom[data-zoom]').forEach(btn => {
            const bVal = parseFloat(btn.dataset.zoom) || 1;
            if (bVal === zVal) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        try {
            localStorage.setItem('workshopos_camera_zoom', zVal.toString());
        } catch(e) {}

        const videoEl = document.querySelector('#reader video');
        if (!videoEl) return;

        let hardwareApplied = false;

        // 1. Try Hardware Camera Track Zoom (Logi HD / UVC Zoom)
        if (videoEl.srcObject) {
            try {
                const stream = videoEl.srcObject;
                const track = stream.getVideoTracks()[0];
                if (track && track.getCapabilities && track.applyConstraints) {
                    const caps = track.getCapabilities();
                    if (caps.zoom) {
                        let targetZoom;
                        if (caps.zoom.min >= 50) {
                            // Some UVC webcams report zoom in percentage (e.g. 100 - 400/500)
                            targetZoom = Math.min(caps.zoom.max, Math.max(caps.zoom.min, zVal * 100));
                        } else {
                            targetZoom = Math.min(caps.zoom.max, Math.max(caps.zoom.min, zVal));
                        }
                        track.applyConstraints({ advanced: [{ zoom: targetZoom }] })
                            .then(() => {
                                // Hardware track zoom succeeded: reset CSS transform
                                videoEl.style.transform = 'none';
                            })
                            .catch(err => {
                                console.warn('Hardware zoom constraint not applied, fallback to CSS zoom:', err);
                                applyCssZoomFallback(videoEl, zVal);
                            });
                        hardwareApplied = true;
                    }
                }
            } catch (e) {
                console.warn('Hardware zoom error:', e);
            }
        }

        // 2. Fallback to CSS digital zoom if hardware zoom is not supported
        if (!hardwareApplied) {
            applyCssZoomFallback(videoEl, zVal);
        }
    }

    function applyCssZoomFallback(videoEl, zVal) {
        if (!videoEl) return;
        if (zVal === 1) {
            videoEl.style.transform = 'none';
        } else {
            videoEl.style.transform = `scale(${zVal})`;
            videoEl.style.transformOrigin = 'center center';
            videoEl.style.transition = 'transform 0.25s ease';
        }
    }

    function triggerAutoFocus() {
        const afBtn = document.getElementById('btn-autofocus');
        const reticle = document.getElementById('camera-focus-reticle');

        if (afBtn) afBtn.classList.add('focusing');
        if (reticle) {
            reticle.classList.remove('focused');
            reticle.classList.add('active');
        }

        playBeep('focus');

        const videoEl = document.querySelector('#reader video');
        if (videoEl && videoEl.srcObject) {
            try {
                const stream = videoEl.srcObject;
                const track = stream.getVideoTracks()[0];
                if (track && track.applyConstraints) {
                    const caps = track.getCapabilities ? track.getCapabilities() : {};

                    // Hardware Autofocus execution
                    if (caps.focusMode && Array.isArray(caps.focusMode)) {
                        if (caps.focusMode.includes('single-shot')) {
                            track.applyConstraints({ advanced: [{ focusMode: 'single-shot' }] })
                                .then(() => {
                                    setTimeout(() => {
                                        if (caps.focusMode.includes('continuous')) {
                                            track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }).catch(() => {});
                                        }
                                    }, 400);
                                }).catch(() => {});
                        } else if (caps.focusMode.includes('continuous')) {
                            track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }).catch(() => {});
                        }
                    } else {
                        // Re-trigger constraint application to force hardware sensor recalculation
                        const curConstraints = track.getConstraints ? track.getConstraints() : {};
                        track.applyConstraints(curConstraints).catch(() => {});
                    }
                }
            } catch(e) {
                console.warn('Autofocus error:', e);
            }
        }

        // Green locked-on state feedback after 350ms
        setTimeout(() => {
            if (reticle) reticle.classList.add('focused');
        }, 350);

        // Reset visual reticle and button state after 850ms
        setTimeout(() => {
            if (reticle) {
                reticle.classList.remove('active', 'focused');
            }
            if (afBtn) {
                afBtn.classList.remove('focusing');
            }
        }, 850);
    }

    function setupZoomControls() {
        // Restore saved zoom level if any
        const savedZoom = parseFloat(localStorage.getItem('workshopos_camera_zoom')) || 1;
        currentZoomLevel = savedZoom;

        // Initialize zoom buttons (1x, 2x, 3x, 5x)
        document.querySelectorAll('.btn-zoom[data-zoom]').forEach(btn => {
            const bVal = parseFloat(btn.dataset.zoom) || 1;
            if (bVal === savedZoom) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const zVal = parseFloat(this.dataset.zoom) || 1;
                applyZoom(zVal);
            });
        });

        // Initialize Auto Focus Button
        const afBtn = document.getElementById('btn-autofocus');
        if (afBtn) {
            afBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                triggerAutoFocus();
            });
        }

        // Tap on viewfinder video to also focus
        const readerEl = document.getElementById('reader');
        if (readerEl) {
            readerEl.addEventListener('click', function(e) {
                if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && !e.target.closest('button') && !e.target.closest('a')) {
                    triggerAutoFocus();
                }
            });
        }

        // Watch for video stream to apply initial zoom
        let appliedInitial = false;
        const checkVideoTimer = setInterval(() => {
            const videoEl = document.querySelector('#reader video');
            if (videoEl && videoEl.srcObject && videoEl.readyState >= 2) {
                if (!appliedInitial) {
                    if (savedZoom > 1) {
                        applyZoom(savedZoom);
                    }
                    appliedInitial = true;
                }
            }
        }, 300);
        setTimeout(() => clearInterval(checkVideoTimer), 12000);
    }

    function translateScannerUI() {
        const readerEl = document.getElementById('reader');

        const observer = new MutationObserver(function() {
            // Stop Button & Scanning state
            const stopBtn = document.getElementById('html5-qrcode-button-camera-stop') || document.getElementById('reader__camera_stop_button');
            const zoomSidebar = document.getElementById('zoom-sidebar');
            if (stopBtn && stopBtn.style.display !== 'none' && !stopBtn.hidden) {
                if (readerEl && !readerEl.classList.contains('is-scanning')) {
                    readerEl.classList.add('is-scanning');
                }
                if (zoomSidebar && zoomSidebar.style.display !== 'flex') {
                    zoomSidebar.style.display = 'flex';
                }
                if (stopBtn.dataset.text !== 'stop_scan_kh') {
                    stopBtn.dataset.text = 'stop_scan_kh';
                    stopBtn.innerHTML = '<i class="bi bi-stop-circle"></i><span>បិទស្កេន</span>';
                }
                if (currentZoomLevel > 1) {
                    const videoEl = document.querySelector('#reader video');
                    if (videoEl && !videoEl.dataset.zoomActive) {
                        videoEl.dataset.zoomActive = "true";
                        applyZoom(currentZoomLevel);
                    }
                }
            } else {
                if (readerEl && readerEl.classList.contains('is-scanning')) {
                    readerEl.classList.remove('is-scanning');
                }
                if (zoomSidebar && zoomSidebar.style.display !== 'none') {
                    zoomSidebar.style.display = 'none';
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
                if (typeof fetchLiveMonitorData === 'function') {
                    fetchLiveMonitorData();
                }
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
                btnReset.onclick = async function() {
                    const confirmed = await AppDialog.confirm({
                        title: 'កំណត់វត្តមានឡើងវិញ (Reset)?',
                        html: `តើអ្នកពិតជាចង់កំណត់វត្តមានរបស់ «<strong>${escapeHtml(p.name || '')}</strong>» ឡើងវិញមែនទេ?`,
                        icon: 'warning',
                        isDanger: true,
                        confirmText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Reset ឡើងវិញ'
                    });
                    if (confirmed) {
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
                if (typeof fetchLiveMonitorData === 'function') {
                    fetchLiveMonitorData();
                }
                AppDialog.toast('បានកំណត់វត្តមានឡើងវិញជោគជ័យ!', 'success');
            } else {
                AppDialog.error('កំហុស', res.message || 'មិនអាចកំណត់វត្តមានឡើងវិញបានទេ។');
            }
        })
        .catch(err => {
            console.error(err);
            AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាបណ្តាញពេលលុបវត្តមាន');
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

    // ==========================================
    // Helper Passes (Temporary Scanner Links)
    // ==========================================
    function initHelperPassManager() {
        const formCreate = document.getElementById('form-create-helper-pass');
        const passesTbody = document.getElementById('helper-passes-tbody');
        const countBadge = document.getElementById('helper-passes-count');
        const headerBadge = document.getElementById('active-helper-count-badge');
        const btnCopyNp = document.getElementById('btn-copy-np-url');
        const npUrlInput = document.getElementById('np-url-input');
        const newPassResult = document.getElementById('new-pass-result');
        const npQrBox = document.getElementById('np-qrcode');
        const listTabBtn = document.getElementById('list-pass-tab');
        const modalEl = document.getElementById('helperPassModal');

        if (formCreate) {
            formCreate.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = document.getElementById('btn-submit-create-pass');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបង្កើត...';

                const fd = new FormData(this);
                fd.append('_csrf_token', CSRF_TOKEN);
                fd.append('workshop_id', WORKSHOP_ID);

                fetch(APP_URL + '/api/checkin/helper-passes/create', {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> បង្កើតលីងជំនួយការ';

                    if (!data.success) {
                        AppDialog.error('កំហុសពេលបង្កើត', data.message || 'កំហុសពេលបង្កើតលីង');
                        return;
                    }

                    AppDialog.toast('បានបង្កើតលីងជំនួយការថ្មីជោគជ័យ!', 'success');

                    // Display Result
                    newPassResult.style.display = 'block';
                    npUrlInput.value = data.pass.url;
                    document.getElementById('np-expiry-text').innerText = 'ផុតសុពលភាព៖ ' + data.pass.expires_at;

                    // Generate QR Code
                    npQrBox.innerHTML = '';
                    if (typeof QRCode !== 'undefined') {
                        new QRCode(npQrBox, {
                            text: data.pass.url,
                            width: 140,
                            height: 140,
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }

                    loadHelperPasses();
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> បង្កើតលីងជំនួយការ';
                    AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ។');
                });
            });
        }

        if (btnCopyNp && npUrlInput) {
            btnCopyNp.addEventListener('click', function() {
                navigator.clipboard.writeText(npUrlInput.value).then(() => {
                    const orig = btnCopyNp.innerHTML;
                    btnCopyNp.innerHTML = '<i class="bi bi-check2"></i> បានចម្លង!';
                    btnCopyNp.classList.replace('btn-outline-primary', 'btn-success');
                    setTimeout(() => {
                        btnCopyNp.innerHTML = orig;
                        btnCopyNp.classList.replace('btn-success', 'btn-outline-primary');
                    }, 2000);
                });
            });
        }

        function formatRemaining(expiresAtStr) {
            try {
                const exp = new Date(expiresAtStr.replace(/-/g, '/')).getTime();
                const now = new Date().getTime();
                const diff = exp - now;
                if (diff <= 0) return 'ផុតកំណត់';
                const h = Math.floor(diff / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                if (h > 0) return `នៅសល់ ${h} ម៉ោង ${m} ន.`;
                return `នៅសល់ ${m} នាទី`;
            } catch (e) {
                return expiresAtStr;
            }
        }

        function getAvatarInitials(name) {
            if (!name) return '👤';
            const parts = name.trim().split(/\s+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        }

        let currentPassFilter = 'active'; // 'active', 'closed', 'all'
        let allCachedPasses = [];

        window.filterPassList = function(filter) {
            currentPassFilter = filter;

            const btnActive = document.getElementById('btn-filter-active');
            const btnClosed = document.getElementById('btn-filter-closed');
            const btnAll = document.getElementById('btn-filter-all');

            if (btnActive) {
                if (filter === 'active') {
                    btnActive.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-primary text-white shadow-sm';
                } else {
                    btnActive.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary';
                }
            }

            if (btnClosed) {
                if (filter === 'closed') {
                    btnClosed.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-danger text-white shadow-sm';
                } else {
                    btnClosed.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary';
                }
            }

            if (btnAll) {
                if (filter === 'all') {
                    btnAll.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-dark text-white shadow-sm';
                } else {
                    btnAll.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary';
                }
            }

            renderPassesList();
        };

        function renderPassesList() {
            const container = document.getElementById('helper-passes-container') || document.getElementById('helper-passes-tbody');
            if (!container) return;

            let filtered = [];
            if (currentPassFilter === 'active') {
                filtered = allCachedPasses.filter(p => p.is_active && !p.is_expired);
            } else if (currentPassFilter === 'closed') {
                filtered = allCachedPasses.filter(p => !p.is_active || p.is_expired);
            } else {
                filtered = allCachedPasses;
            }

            if (filtered.length === 0) {
                if (currentPassFilter === 'active') {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted bg-light rounded-4 border border-dashed">
                            <i class="bi bi-qr-code display-4 text-primary opacity-50 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">មិនមានតុជំនួយការកំពុងសកម្មនៅឡើយទេ</h6>
                            <p class="small text-muted mb-0">សូមចុចលើផ្ទាំង «បង្កើតលីងថ្មី» ខាងលើ ដើម្បីបង្កើតច្រកស្កេនជំនួយការ។</p>
                        </div>
                    `;
                } else if (currentPassFilter === 'closed') {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted bg-light rounded-4 border border-dashed">
                            <i class="bi bi-archive display-4 text-secondary opacity-50 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">គ្មានលីងដែលបានបិទ ឬផុតកំណត់ឡើយ</h6>
                            <p class="small text-muted mb-0">លីងទាំងអស់នៅដំណើរការ ឬមិនទាន់មានការបិទណាមួយនៅឡើយ។</p>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted bg-light rounded-4 border border-dashed">
                            <i class="bi bi-inbox display-4 text-muted opacity-50 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">មិនទាន់មានទិន្នន័យតុជំនួយការនៅឡើយទេ</h6>
                        </div>
                    `;
                }
                return;
            }

            let html = '';
            filtered.forEach(p => {
                let badgeClass = 'bg-secondary text-white';
                let statusIcon = '<i class="bi bi-check-circle-fill me-1"></i>';
                if (p.is_active && !p.is_expired) {
                    badgeClass = 'bg-success text-white';
                    statusIcon = '<i class="bi bi-check-circle-fill me-1"></i>';
                } else if (p.is_active && p.is_expired) {
                    badgeClass = 'bg-warning text-dark';
                    statusIcon = '<i class="bi bi-clock-history me-1"></i>';
                } else if (!p.is_active) {
                    badgeClass = 'bg-danger text-white';
                    statusIcon = '<i class="bi bi-slash-circle me-1"></i>';
                }

                const remainingText = formatRemaining(p.expires_at);

                // Helpers list
                let helpersListHtml = '';
                if (p.devices && p.devices.length > 0) {
                    helpersListHtml = `
                        <div class="d-flex flex-column gap-2 mt-2">
                            ${p.devices.map(d => `
                                <div class="d-flex align-items-center justify-content-between p-2.5 px-3 rounded-3 border ${d.is_active ? 'bg-white shadow-2xs' : 'bg-danger bg-opacity-10 border-danger border-opacity-25'}" style="min-height: 58px;">
                                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                        <div class="rounded-circle ${d.is_active ? 'bg-primary text-white' : 'bg-secondary text-white'} d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; font-size: 0.82rem;">
                                            ${getAvatarInitials(d.helper_name)}
                                        </div>
                                        <div class="text-truncate" style="line-height: 1.35;">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-bold ${d.is_active ? 'text-dark' : 'text-decoration-line-through text-danger'}" style="font-size: 0.92rem;">${escapeHtml(d.helper_name)}</span>
                                                ${d.is_active ? `
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">
                                                        <i class="bi bi-check-circle-fill me-0.5"></i> សកម្ម
                                                    </span>
                                                ` : `
                                                    <span class="badge bg-danger text-white rounded-pill" style="font-size: 0.65rem;">
                                                        <i class="bi bi-slash-circle me-0.5"></i> បានបិទ
                                                    </span>
                                                `}
                                            </div>
                                            <div class="text-muted small d-flex align-items-center gap-2 mt-0.5 flex-wrap" style="font-size: 0.74rem;">
                                                <span><i class="bi bi-phone me-1 text-secondary"></i>${escapeHtml(d.device_name)}</span>
                                                <span>•</span>
                                                <span><i class="bi bi-clock me-1 text-secondary"></i>សកម្ម៖ ${escapeHtml(d.last_active_at)}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0 ms-2">
                                        ${d.is_active ? `
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2.5 py-1 d-inline-flex align-items-center gap-1 rounded-pill" style="font-size: 0.74rem;" title="បិទមិនឱ្យអ្នកនេះស្កេន" onclick="toggleDevice(${d.id}, 0, '${escapeHtml(d.helper_name)}')">
                                                <i class="bi bi-slash-circle"></i> <span>បិទ</span>
                                            </button>
                                        ` : `
                                            <button type="button" class="btn btn-sm btn-outline-success px-2.5 py-1 d-inline-flex align-items-center gap-1 rounded-pill" style="font-size: 0.74rem;" title="បើកឱ្យអ្នកនេះស្កេនវិញ" onclick="toggleDevice(${d.id}, 1, '${escapeHtml(d.helper_name)}')">
                                                <i class="bi bi-check-circle"></i> <span>បើកវិញ</span>
                                            </button>
                                        `}
                                        <button type="button" class="btn btn-sm btn-light border text-danger px-2 py-1 rounded-pill" style="font-size: 0.74rem;" title="លុបចេញ (ដោះលែងកន្លែងឧបករណ៍)" onclick="removeDevice(${d.id}, '${escapeHtml(d.helper_name)}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    helpersListHtml = `
                        <div class="p-3 bg-white rounded-3 text-center text-muted border border-dashed small mt-2">
                            <i class="bi bi-phone text-secondary me-1"></i>
                            <span>មិនទាន់មានអ្នកចូលស្កេននៅឡើយទេ (សូមផ្ញើ Link ឬឱ្យគាត់ស្កេន QR ខាងលើ)</span>
                        </div>
                    `;
                }

                html += `
                    <div class="card border rounded-3 shadow-xs mb-3 bg-white helper-pass-card ${!p.is_active ? 'opacity-75 border-secondary border-opacity-50' : ''}" style="flex-shrink: 0 !important;">
                        <!-- Pass Header -->
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="${p.is_active ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary'} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-qr-code-scan fs-5"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <h6 class="fw-bold mb-0 text-dark">${escapeHtml(p.label)}</h6>
                                        <span class="badge ${badgeClass} rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">${statusIcon}${escapeHtml(p.status_label)}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-0.5 text-muted small" style="font-size: 0.74rem;">
                                        <span class="font-monospace text-secondary">${p.token.substring(0, 10)}...</span>
                                        <span>•</span>
                                        <span title="ផុតកំណត់៖ ${p.expires_at}"><i class="bi bi-clock-history ${p.is_expired ? 'text-danger' : 'text-primary'} me-1"></i>${remainingText}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 ms-auto ms-sm-0">
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;" title="បង្ហាញកូដ QR" onclick="showPassQr('${escapeHtml(p.label)}', '${p.url}', '${p.expires_at}')">
                                    <i class="bi bi-qr-code"></i> <span>កូដ QR</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;" title="ចម្លងតំណភ្ជាប់" onclick="copyPassUrl('${p.url}', this)">
                                    <i class="bi bi-clipboard"></i> <span>ចម្លង Link</span>
                                </button>
                                ${p.is_active ? `
                                    <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;" title="បិទលីងតុនេះទាំងមូល" onclick="revokePass(${p.id}, '${escapeHtml(p.label)}')">
                                        <i class="bi bi-x-circle"></i> <span>បិទតុ</span>
                                    </button>
                                ` : `
                                    <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;" title="បើកដំណើរការតុនេះឡើងវិញ" onclick="reactivatePass(${p.id}, ${p.is_expired ? 1 : 0}, '${escapeHtml(p.label)}')">
                                        <i class="bi bi-play-circle-fill"></i> <span>បើកឡើងវិញ</span>
                                    </button>
                                `}
                            </div>
                        </div>

                        <!-- Pass Body (Helpers Section) -->
                        <div class="card-body p-3 bg-light bg-opacity-50">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                                    <i class="bi bi-people-fill text-primary"></i>
                                    <span>អ្នកជួយស្កេនវត្តមាន (Connected Helpers)</span>
                                </div>
                                <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.74rem;">
                                    <i class="bi bi-phone me-1 text-primary"></i>${p.device_count} / ${p.max_devices} គ្រឿង
                                </span>
                            </div>

                            ${helpersListHtml}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function loadHelperPasses() {
            const container = document.getElementById('helper-passes-container') || document.getElementById('helper-passes-tbody');
            if (!container) return;

            fetch(APP_URL + '/api/checkin/helper-passes?workshop_id=' + WORKSHOP_ID)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.passes) {
                    container.innerHTML = '<div class="text-center py-4 text-muted">មិនមានទិន្នន័យ។</div>';
                    return;
                }

                allCachedPasses = data.passes || [];
                const activePasses = allCachedPasses.filter(p => p.is_active && !p.is_expired);
                const closedPasses = allCachedPasses.filter(p => !p.is_active || p.is_expired);

                if (countBadge) countBadge.innerText = activePasses.length;
                if (headerBadge) {
                    headerBadge.innerText = activePasses.length;
                    headerBadge.style.display = activePasses.length > 0 ? 'inline-block' : 'none';
                }

                // Update filter count badges
                const fActive = document.getElementById('filter-count-active');
                const fClosed = document.getElementById('filter-count-closed');
                const fAll = document.getElementById('filter-count-all');
                if (fActive) fActive.innerText = activePasses.length;
                if (fClosed) fClosed.innerText = closedPasses.length;
                if (fAll) fAll.innerText = allCachedPasses.length;

                renderPassesList();
            })
            .catch(() => {
                container.innerHTML = '<div class="text-center py-4 text-danger">បញ្ហាទាញទិន្នន័យ។</div>';
            });
        }

        window.toggleDevice = async function(deviceId, isActive, helperName) {
            const actionText = isActive ? 'បើកដំណើរការឡើងវិញ' : 'បិទដំណើរការ';
            const confirmed = await AppDialog.confirm({
                title: `${actionText}?`,
                html: `តើអ្នកពិតជាចង់${actionText}សម្រាប់អ្នកជួយ «<strong>${escapeHtml(helperName)}</strong>» មែនទេ?`,
                icon: isActive ? 'question' : 'warning',
                isDanger: !isActive,
                isSuccess: isActive,
                confirmText: isActive ? '<i class="bi bi-check-circle-fill me-1"></i> បើកវិញ' : '<i class="bi bi-slash-circle me-1"></i> បិទដំណើរការ',
                confirmColor: isActive ? '#198754' : '#dc3545',
                cancelText: 'បោះបង់'
            });

            if (!confirmed) return;

            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            fd.append('workshop_id', WORKSHOP_ID);
            fd.append('device_id', deviceId);
            fd.append('is_active', isActive);

            fetch(APP_URL + '/api/checkin/helper-passes/toggle-device', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    AppDialog.toast(isActive ? `បានបើកដំណើរការសម្រាប់ «${helperName}» វិញហើយ` : `បានបិទដំណើរការ «${helperName}»`, 'success');
                    loadHelperPasses();
                } else {
                    AppDialog.error('កំហុសពេលផ្លាស់ប្តូរស្ថានភាព', data.message || 'មិនអាចផ្លាស់ប្តូរបានទេ');
                }
            })
            .catch(() => AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ។'));
        };

        window.removeDevice = async function(deviceId, helperName) {
            const confirmed = await AppDialog.confirm({
                title: 'លុបឧបករណ៍ចេញពីប្រព័ន្ធ?',
                html: `តើអ្នកពិតជាចង់លុបឧបករណ៍របស់អ្នកជួយ «<strong>${escapeHtml(helperName)}</strong>» ចេញពីប្រព័ន្ធមែនទេ?<br><div class="mt-2 small text-muted"><i class="bi bi-info-circle text-primary me-1"></i>ការលុបនេះនឹងដោះលែងកន្លែងឱ្យឧបករណ៍ផ្សេងទៀតអាចចូលបាន។</div>`,
                icon: 'warning',
                isDanger: true,
                confirmText: '<i class="bi bi-trash3-fill me-1"></i> លុបឧបករណ៍',
                confirmColor: '#dc3545',
                cancelText: 'បោះបង់'
            });

            if (!confirmed) return;

            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            fd.append('workshop_id', WORKSHOP_ID);
            fd.append('device_id', deviceId);

            fetch(APP_URL + '/api/checkin/helper-passes/remove-device', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    AppDialog.toast(`បានលុបឧបករណ៍របស់អ្នកជួយ «${helperName}» រួចរាល់`, 'success');
                    loadHelperPasses();
                } else {
                    AppDialog.error('កំហុសពេលលុបឧបករណ៍', data.message || 'មិនអាចលុបបានទេ');
                }
            })
            .catch(() => AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ។'));
        };

        window.copyPassUrl = function(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                AppDialog.toast('បានចម្លងតំណភ្ជាប់ (Link) រួចរាល់!', 'success');
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                btn.classList.replace('btn-outline-secondary', 'btn-success');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.replace('btn-success', 'btn-outline-secondary');
                }, 1500);
            });
        };

        window.showPassQr = function(label, url, expiresAt) {
            document.getElementById('vqr-label').innerText = label;
            document.getElementById('vqr-expiry').innerText = 'ផុតសុពលភាព៖ ' + expiresAt;
            document.getElementById('vqr-url').value = url;

            const qrBox = document.getElementById('vqr-qrcode-box');
            qrBox.innerHTML = '';
            if (typeof QRCode !== 'undefined') {
                new QRCode(qrBox, {
                    text: url,
                    width: 180,
                    height: 180,
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('viewPassQrModal'));
            modal.show();
        };

        document.getElementById('btn-vqr-copy')?.addEventListener('click', function() {
            const inp = document.getElementById('vqr-url');
            if (inp) {
                navigator.clipboard.writeText(inp.value).then(() => {
                    AppDialog.toast('បានចម្លងតំណភ្ជាប់ (Link) រួចរាល់!', 'success');
                    this.innerHTML = '<i class="bi bi-check2"></i>';
                    setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1500);
                });
            }
        });

        window.revokePass = async function(passId, label = '') {
            const confirmed = await AppDialog.confirm({
                title: 'បិទដំណើរការតុស្កេន?',
                html: `តើអ្នកពិតជាចង់បិទតុជំនួយការ ${label ? '«<strong>' + escapeHtml(label) + '</strong>» ' : ''}នេះមែនទេ?<br><span class="text-danger small mt-2 d-inline-block"><i class="bi bi-shield-exclamation me-1"></i>គ្រប់ទូរស័ព្ទដែលកំពុងប្រើលីងនេះនឹងត្រូវកាត់ផ្តាច់ភ្លាមៗ។</span>`,
                icon: 'warning',
                isDanger: true,
                confirmText: '<i class="bi bi-x-circle-fill me-1"></i> បិទតុស្កេន',
                confirmColor: '#dc3545',
                cancelText: 'បោះបង់'
            });

            if (!confirmed) return;

            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            fd.append('workshop_id', WORKSHOP_ID);
            fd.append('pass_id', passId);

            fetch(APP_URL + '/api/checkin/helper-passes/revoke', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    AppDialog.toast('បានបិទតុស្កេនជំនួយការនេះរួចរាល់', 'success');
                    loadHelperPasses();
                } else {
                    AppDialog.error('កំហុសពេលបិទលីង', data.message || 'មិនអាចបិទបានទេ');
                }
            })
            .catch(() => AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ។'));
        };

        window.reactivatePass = async function(passId, isExpired, label) {
            const confirmed = await AppDialog.confirm({
                title: isExpired ? 'បើកឡើងវិញ & បន្ថែមម៉ោង' : 'បើកដំណើរការតុឡើងវិញ',
                html: isExpired 
                    ? `តុស្កេន «<strong>${escapeHtml(label)}</strong>» បានផុតសុពលភាពហើយ។<br><div class="p-2 rounded bg-light border mt-2 small text-dark"><i class="bi bi-clock-history text-primary me-1"></i>ប្រព័ន្ធនឹងបើកដំណើរការឡើងវិញ និង<strong>បន្ថែមសុពលភាព ៤ ម៉ោង</strong>បន្តទៀតភ្លាមៗ។</div>`
                    : `តើអ្នកចង់បើកដំណើរការតុស្កេន «<strong>${escapeHtml(label)}</strong>» នេះឡើងវិញមែនទេ?`,
                icon: isExpired ? 'info' : 'question',
                isSuccess: true,
                confirmText: '<i class="bi bi-play-circle-fill me-1"></i> បើកដំណើរការឡើងវិញ',
                confirmColor: '#198754',
                cancelText: 'បោះបង់'
            });

            if (!confirmed) return;

            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            fd.append('workshop_id', WORKSHOP_ID);
            fd.append('pass_id', passId);
            if (isExpired) {
                fd.append('extend_hours', 4);
            }

            fetch(APP_URL + '/api/checkin/helper-passes/reactivate', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    AppDialog.toast('បានបើកដំណើរការតុស្កេនឡើងវិញជោគជ័យ!', 'success');
                    loadHelperPasses();
                } else {
                    AppDialog.error('កំហុស', data.message || 'កំហុសពេលបើកដំណើរការឡើងវិញ');
                }
            })
            .catch(() => AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ។'));
        };

        if (listTabBtn) {
            listTabBtn.addEventListener('shown.bs.tab', loadHelperPasses);
        }

        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', loadHelperPasses);
        }

        // Initial load of count badge
        loadHelperPasses();
    }

    initHelperPassManager();

    // ========================================================
    // ADMIN LIVE MONITOR DASHBOARD & MODE SWITCHER
    // ========================================================
    let currentCheckinMode = localStorage.getItem('workshopos_checkin_mode') || 'dashboard';
    let scannerInitialized = false;
    let liveMonitorTimer = null;
    let isFetchingLiveMonitor = false;
    let knownStreamIds = new Set();
    let isFirstStreamLoad = true;

    function ensureScannerInitialized() {
        if (!scannerInitialized) {
            initScanner();
            scannerInitialized = true;
        } else if (html5QrcodeScanner) {
            try {
                html5QrcodeScanner.resume();
            } catch(e) {
                console.warn('Camera resume error:', e);
            }
        }
    }

    window.switchCheckinMode = function(mode) {
        currentCheckinMode = mode;
        try {
            localStorage.setItem('workshopos_checkin_mode', mode);
        } catch(e) {}

        const btnDashboard = document.getElementById('btn-mode-dashboard');
        const btnScanner = document.getElementById('btn-mode-scanner');
        const viewDashboard = document.getElementById('view-dashboard-mode');
        const viewScanner = document.getElementById('view-scanner-mode');

        if (mode === 'scanner') {
            if (btnScanner) {
                btnScanner.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-primary text-white shadow-xs';
            }
            if (btnDashboard) {
                btnDashboard.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary';
            }
            if (viewDashboard) viewDashboard.style.display = 'none';
            if (viewScanner) viewScanner.style.display = 'block';

            stopLiveMonitorPolling();
            ensureScannerInitialized();
        } else {
            // Dashboard mode (default)
            if (btnDashboard) {
                btnDashboard.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-primary text-white shadow-xs';
            }
            if (btnScanner) {
                btnScanner.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary';
            }
            if (viewDashboard) viewDashboard.style.display = 'block';
            if (viewScanner) viewScanner.style.display = 'none';

            // Pause camera scanner to save CPU and battery
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.pause();
                } catch(e) {}
            }

            // Start live polling and fetch now
            startLiveMonitorPolling();
            fetchLiveMonitorData();
        }
    };

    window.toggleFullscreen = function() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.warn('Fullscreen error:', err);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen().catch(err => {
                    console.warn('Exit fullscreen error:', err);
                });
            }
        }
    };

    document.addEventListener('fullscreenchange', function() {
        const fsBtn = document.getElementById('btn-toggle-fullscreen');
        if (!fsBtn) return;
        if (document.fullscreenElement) {
            fsBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
            fsBtn.title = 'ចាកចេញពីពេញអេក្រង់';
        } else {
            fsBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
            fsBtn.title = 'បើកពេញអេក្រង់';
        }
    });

    function startLiveMonitorPolling() {
        stopLiveMonitorPolling();
        liveMonitorTimer = setInterval(fetchLiveMonitorData, 3500);
    }

    function stopLiveMonitorPolling() {
        if (liveMonitorTimer) {
            clearInterval(liveMonitorTimer);
            liveMonitorTimer = null;
        }
    }

    function fetchLiveMonitorData() {
        if (isFetchingLiveMonitor) return;
        isFetchingLiveMonitor = true;

        fetch(APP_URL + '/api/checkin/live-monitor?workshop_id=' + WORKSHOP_ID)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // 1. Update Metrics
                const s = data.stats;
                const checkedInEl = document.getElementById('lm-checked-in');
                const totalEl = document.getElementById('lm-total');
                const rateBadge = document.getElementById('lm-rate-badge');
                const progressBar = document.getElementById('lm-progress-bar');
                const pendingEl = document.getElementById('lm-pending');
                const vipEl = document.getElementById('lm-vip');
                const stationsCountEl = document.getElementById('lm-stations-count');
                const devicesCountEl = document.getElementById('lm-devices-count');
                const topbarStats = document.getElementById('stats-checked-in');
                const topbarBadge = document.getElementById('topbar-helper-badge');
                const updatedText = document.getElementById('lm-last-updated-text');

                if (checkedInEl) checkedInEl.innerText = Number(s.checked_in || 0).toLocaleString();
                if (totalEl) totalEl.innerText = Number(s.total || 0).toLocaleString();
                if (rateBadge) rateBadge.innerText = (s.rate || 0) + '%';
                if (progressBar) {
                    progressBar.style.width = (s.rate || 0) + '%';
                    progressBar.setAttribute('aria-valuenow', s.rate || 0);
                }
                if (pendingEl) pendingEl.innerText = Number(s.pending || 0).toLocaleString();
                if (vipEl) vipEl.innerText = `${s.vip_checked_in || 0} / ${s.vip_total || 0}`;
                if (stationsCountEl) stationsCountEl.innerText = Number(s.active_passes || 0).toLocaleString();
                if (devicesCountEl) devicesCountEl.innerText = Number(s.active_devices || 0).toLocaleString();

                if (topbarStats) {
                    topbarStats.innerText = `${s.checked_in || 0} / ${s.total || 0}`;
                }

                if (topbarBadge) {
                    if (s.active_devices > 0) {
                        topbarBadge.style.display = 'inline-block';
                        topbarBadge.innerText = s.active_devices + ' គ្រឿង';
                    } else if (s.active_passes > 0) {
                        topbarBadge.style.display = 'inline-block';
                        topbarBadge.innerText = s.active_passes + ' តុ';
                    } else {
                        topbarBadge.style.display = 'none';
                    }
                }

                if (updatedText) {
                    const now = new Date();
                    const pad = (n) => String(n).padStart(2, '0');
                    updatedText.innerText = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                }

                // 2. Render Live Stream
                renderLiveStream(data.stream || []);

                // 3. Render Helper Stations
                renderHelperStations(data.stations || []);
            })
            .catch(err => {
                console.warn('Live monitor fetch error:', err);
            })
            .finally(() => {
                isFetchingLiveMonitor = false;
            });
    }

    function renderLiveStream(stream) {
        const container = document.getElementById('lm-stream-container');
        if (!container) return;

        if (!stream || stream.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle text-secondary" style="width: 60px; height: 60px;">
                            <i class="bi bi-qr-code-scan fs-3 text-primary"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">មិនទាន់មានការស្កេនចូលនៅឡើយទេ</h6>
                    <p class="small text-muted mb-0">នៅពេលជំនួយការ ឬអ្នករៀបចំស្កេន សិក្ខាកាមនឹងបង្ហាញនៅទីនេះភ្លាមៗ។</p>
                </div>
            `;
            return;
        }

        let html = '';
        const newIds = new Set();

        stream.forEach(item => {
            const isNew = !isFirstStreamLoad && !knownStreamIds.has(item.registration_id);
            newIds.add(item.registration_id);

            let avatarHtml = '';
            if (item.photo_url) {
                avatarHtml = `<img src="${escapeHtml(item.photo_url)}" alt="${escapeHtml(item.name)}" class="rounded-circle object-fit-cover shadow-2xs flex-shrink-0" style="width: 44px; height: 44px;">`;
            } else {
                const initials = item.name ? item.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : '?';
                avatarHtml = `<div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" style="width: 44px; height: 44px; font-size: 0.95rem;">${escapeHtml(initials)}</div>`;
            }

            const vipBadge = item.is_vip 
                ? `<span class="badge bg-warning text-dark border border-warning-subtle rounded-pill ms-1 px-2 py-0.5" style="font-size: 0.68rem;"><i class="bi bi-star-fill text-warning-emphasis me-0.5"></i> VIP</span>` 
                : '';

            const ticketBadge = item.ticket_name && item.ticket_name !== 'ទូទៅ'
                ? `<span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size: 0.68rem;">${escapeHtml(item.ticket_name)}</span>`
                : '';

            const sourceBadge = `<span class="badge bg-light text-secondary border rounded-pill small py-1 px-2 d-inline-flex align-items-center gap-1" title="${escapeHtml(item.source)}">
                <i class="bi bi-person-badge text-primary" style="font-size: 0.75rem;"></i>
                <span class="text-truncate" style="max-width: 140px;">${escapeHtml(item.source)}</span>
            </span>`;

            html += `
                <div class="list-group-item p-3 d-flex align-items-center justify-content-between gap-3 stream-item ${isNew ? 'stream-item-new' : ''}">
                    <div class="d-flex align-items-center gap-3 text-truncate">
                        ${avatarHtml}
                        <div class="text-truncate">
                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <span class="fw-bold text-dark" style="font-size: 0.95rem;">${escapeHtml(item.name)}</span>
                                ${vipBadge}
                                ${ticketBadge}
                            </div>
                            <div class="small text-muted text-truncate mt-0.5" style="font-size: 0.78rem;">
                                <i class="bi bi-building me-1 text-secondary"></i><span>${escapeHtml(item.company || '-')}</span>
                                <span class="mx-1 text-muted">•</span>
                                <i class="bi bi-geo-alt me-0.5 text-secondary"></i><span>${escapeHtml(item.province || 'ទូទៅ')}</span>
                                ${item.position && item.position !== '-' ? `<span class="mx-1 text-muted">•</span><span class="text-secondary">${escapeHtml(item.position)}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <div class="text-end d-flex flex-column align-items-end gap-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                <i class="bi bi-check-circle-fill me-1"></i>${escapeHtml(item.time)}
                            </span>
                            ${sourceBadge}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill shadow-2xs ms-1" title="លុបវត្តមាន" onclick="adminResetAttendance(${item.registration_id}, '${escapeHtml(item.name)}')">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        knownStreamIds = newIds;
        isFirstStreamLoad = false;
    }

    function renderHelperStations(stations) {
        const container = document.getElementById('lm-stations-list-container');
        if (!container) return;

        if (!stations || stations.length === 0) {
            container.innerHTML = `
                <div class="p-3 text-center text-muted small bg-light rounded-3 border">
                    <i class="bi bi-info-circle me-1 text-primary"></i> មិនទាន់មានតុជំនួយការដែលសកម្មនៅឡើយទេ។<br>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold mt-2 shadow-2xs" data-bs-toggle="modal" data-bs-target="#helperPassModal">
                        <i class="bi bi-plus me-1"></i> បង្កើតតុជំនួយការ
                    </button>
                </div>
            `;
            return;
        }

        let html = '';
        stations.forEach(st => {
            let devicesHtml = '';
            if (st.devices && st.devices.length > 0) {
                devicesHtml = '<div class="d-flex flex-column gap-2 mt-2 pt-2 border-top">';
                st.devices.forEach(dev => {
                    devicesHtml += `
                        <div class="p-2 rounded-3 bg-light border-0 d-flex align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <span class="rounded-circle bg-success shadow-sm flex-shrink-0" style="width: 8px; height: 8px;"></span>
                                <span class="fw-bold text-dark text-truncate" style="font-size: 0.84rem;">${escapeHtml(dev.helper_name)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                    ${dev.scanned_count} នាក់
                                </span>
                                <span class="text-muted small" title="សកម្មចុងក្រោយ" style="font-size: 0.72rem;">${escapeHtml(dev.last_active)}</span>
                            </div>
                        </div>
                    `;
                });
                devicesHtml += '</div>';
            } else {
                devicesHtml = `
                    <div class="text-muted small ps-2 py-2 mt-1 fst-italic" style="font-size: 0.75rem;">
                        <i class="bi bi-hourglass-split me-1 text-warning"></i>រង់ចាំជំនួយការបើកទូរស័ព្ទភ្ជាប់...
                    </div>
                `;
            }

            html += `
                <div class="card border rounded-3 p-3 bg-white shadow-2xs mb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <i class="bi bi-qr-code text-primary fs-6"></i>
                            <span class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">${escapeHtml(st.label)}</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                            ${st.active_devices}/${st.max_devices} ឧបករណ៍
                        </span>
                    </div>
                    ${devicesHtml}
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function initLiveMonitorSearch() {
        const input = document.getElementById('lm-manual-search-input');
        const btn = document.getElementById('lm-btn-manual-search');
        const resultsBox = document.getElementById('lm-search-results-box');
        let debounceTimer = null;

        if (!input) return;

        function doSearch() {
            const q = input.value.trim();
            if (!q) {
                if (resultsBox) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                }
                return;
            }

            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            fd.append('workshop_id', WORKSHOP_ID);
            fd.append('query', q);

            fetch(APP_URL + '/api/checkin/search', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (!resultsBox) return;

                if (!res.success || !res.data || res.data.length === 0) {
                    resultsBox.style.display = 'block';
                    resultsBox.innerHTML = '<div class="alert alert-light border small text-muted text-center py-2 mb-0">រកមិនឃើញសិក្ខាកាមត្រូវគ្នានឹង «' + escapeHtml(q) + '» នោះទេ។</div>';
                    return;
                }

                resultsBox.style.display = 'block';
                let html = '<div class="list-group list-group-flush">';
                res.data.forEach(p => {
                    const isChecked = !!p.checked_in_at;
                    const tokenOrCode = p.token || p.registration_code;
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center p-2 rounded-2 mb-1 border-0 bg-light">
                            <div class="text-truncate me-2">
                                <div class="fw-bold text-dark small text-truncate">
                                    ${escapeHtml(p.name)} 
                                    <span class="badge bg-white text-dark border font-monospace ms-1" style="font-size: 0.7rem;">${escapeHtml(p.registration_code)}</span>
                                    ${p.is_vip ? '<span class="badge bg-warning text-dark border rounded-pill ms-1" style="font-size: 0.65rem;">VIP</span>' : ''}
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    ${escapeHtml(p.phone || '')} ${p.company ? '• ' + escapeHtml(p.company) : ''}
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                ${isChecked 
                                    ? `<div class="d-flex align-items-center gap-1.5">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2" style="font-size: 0.72rem;"><i class="bi bi-check2-circle me-1"></i>បានស្កេន</span>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" title="លុបវត្តមាន (Reset)" onclick="adminResetAttendance(${p.registration_id}, '${escapeHtml(p.name)}')"><i class="bi bi-arrow-counterclockwise"></i></button>
                                       </div>`
                                    : `<button class="btn btn-sm btn-success fw-bold px-3 py-1 rounded-pill shadow-2xs" style="font-size: 0.75rem;" onclick="adminQuickCheckin('${escapeHtml(tokenOrCode)}')"><i class="bi bi-check-lg me-1"></i> កត់ត្រាវត្តមាន</button>`
                                }
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                resultsBox.innerHTML = html;
            })
            .catch(err => console.error(err));
        }

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doSearch, 300);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch();
            }
        });

        if (btn) {
            btn.addEventListener('click', doSearch);
        }
    }

    window.adminQuickCheckin = function(tokenOrCode) {
        const fd = new FormData();
        fd.append('_csrf_token', CSRF_TOKEN);
        fd.append('token', tokenOrCode);
        fd.append('workshop_id', WORKSHOP_ID);
        fd.append('scan_type', 'checkin');

        fetch(APP_URL + '/api/checkin/scan', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && !data.already_in) {
                playBeep('success');
                AppDialog.toast((data.participant ? data.participant.name + '៖ ' : '') + (data.message || 'កត់ត្រាវត្តមានជោគជ័យ'), 'success');
            } else if (data.already_in) {
                playBeep('warning');
                AppDialog.toast((data.participant ? data.participant.name + '៖ ' : '') + (data.message || 'បានស្កេនរួចហើយ'), 'info');
            } else {
                playBeep('error');
                AppDialog.error('កំហុស', data.message || 'មិនអាចកត់ត្រាវត្តមានបានទេ');
            }

            // Hide search results and clear input
            const input = document.getElementById('lm-manual-search-input');
            const resultsBox = document.getElementById('lm-search-results-box');
            if (input) input.value = '';
            if (resultsBox) {
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
            }

            // Update live monitor
            fetchLiveMonitorData();
        })
        .catch(() => {
            AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ');
        });
    };

    window.adminResetAttendance = async function(regId, name) {
        if (!regId) return;

        const confirmed = await AppDialog.confirm({
            title: 'កំណត់វត្តមានឡើងវិញ?',
            html: `តើអ្នកពិតជាចង់កំណត់វត្តមានរបស់ «<strong>${escapeHtml(name || '')}</strong>» ឡើងវិញមែនទេ?`,
            icon: 'warning',
            isDanger: true,
            confirmText: '<i class="bi bi-arrow-counterclockwise me-1"></i> កំណត់ឡើងវិញ'
        });

        if (!confirmed) return;

        const fd = new FormData();
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
                AppDialog.toast('បានកំណត់វត្តមានឡើងវិញដោយជោគជ័យ', 'success');
                fetchLiveMonitorData();
            } else {
                AppDialog.error('កំហុស', res.message || 'មិនអាចកំណត់វត្តមានឡើងវិញបានទេ');
            }
        })
        .catch(() => {
            AppDialog.error('បញ្ហាបណ្តាញ', 'បញ្ហាភ្ជាប់បណ្តាញ');
        });
    };

    initLiveMonitorSearch();
    switchCheckinMode(currentCheckinMode);
});

