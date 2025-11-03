<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Digital</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/display/display.css') }}">
</head>
<body>
    <div class="fullscreen-btn" id="fullscreenBtn" title="Layar Penuh">
        <span class="material-icons">fullscreen</span>
    </div>

    <div class="container">
        <div class="header">
            <div class="header-info">
                <h1>SISTEM ANTRIAN DIGITAL</h1>
                <p class="subtitle">Pelayanan Terpadu Satu Pintu</p>
            </div>
            <div class="header-time-wrapper">
                <div class="current-time" id="currentTime"></div>
            </div>
        </div>

        <div class="left-section">
            <div class="main-display" id="mainDisplay">
                <div class="placeholder-call">
                    <span class="material-icons">notifications_paused</span>
                    <p>Menunggu Panggilan Berikutnya</p>
                </div>
            </div>

            <div class="loket-list">
                <div class="loket-list-header">DAFTAR LOKET</div>
                <div class="loket-grid" id="loketGrid">
                    </div>
            </div>
        </div>

        <div class="right-section">
            <div class="video-ad-container">
                <div class="video-ad-header">
                    <span class="material-icons">play_circle</span>
                    <span>Informasi & Pengumuman</span>
                </div>
                <div class="video-ad-wrapper" id="videoWrapper">
                    <div class="video-loading" id="videoLoading">
                        <div class="spinner"></div>
                        <span>Memuat video...</span>
                    </div>
                    
                    <div id="youtubePlayer"></div>
                    
                    <div class="video-mute-indicator" id="muteIndicator">
                        <span class="material-icons">volume_off</span>
                        <span>Video di-mute saat panggilan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="running-text-container">
        <p class="running-text" id="runningTextElement">
            ... Memuat informasi ...
        </p>
    </div>

    <script src="https://www.youtube.com/iframe_api"></script>
    
    <script>
        // Suppress console warnings (opsional)
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                const msg = args[0]?.toString() || '';
                if (msg.includes('Violation') || msg.includes('passive')) return;
                originalWarn.apply(console, args);
            };
        })();

        let queueDisplayApp;

        function onYouTubeIframeAPIReady() {
            console.log('[YouTube] API Ready');
            if (queueDisplayApp) {
                queueDisplayApp.onYouTubeApiReady();
            }
        }

        class ModernQueueDisplay {
            constructor() {
                this.previousHash = {};
                this.speechEnabled = true;
                this.updateInterval = null;
                this.currentDisplayedCall = null;
                this.speechQueue = [];
                this.isSpeaking = false;
                
                this.mainDisplay = document.getElementById('mainDisplay');
                
                this.youtubePlayer = null;
                this.playerReady = false;
                this.youTubeApiReady = false;
                
                this.videoIds = []; 
                this.runningText = '';
                
                this.currentVideoIndex = 0;
                this.isVideoMuted = false;
                
                this.initialize();
            }
            
            initialize() {
                this.setupFullscreen();
                setInterval(() => this.updateClock(), 1000);
                
                if (!('speechSynthesis' in window)) {
                    this.speechEnabled = false;
                    console.warn('Speech synthesis tidak didukung.');
                }

                this.fetchData();
                this.updateInterval = setInterval(() => this.fetchData(), 3000);
                
                this.fetchDisplaySettings(); 
            }

            async fetchDisplaySettings() {
                console.log('[Settings] Mengambil pengaturan display...');
                try {
                    const response = await fetch('/display/active-settings');
                    if (!response.ok) throw new Error('Gagal mengambil pengaturan');
                    
                    const settings = await response.json();
                    
                    if (settings.video_ids && settings.video_ids.length > 0) {
                        this.videoIds = settings.video_ids;
                        console.log('[Settings] Video IDs berhasil diproses:', this.videoIds);
                    } else {
                        console.warn('[Settings] Tidak ada video ID dari API, periksa admin.');
                        this.videoIds = ['ScMzIvxBSi4', 'kJQP7kiw5Fk']; // Fallback
                    }

                    this.runningText = settings.running_text || 'Selamat Datang';
                    this.updateRunningTextDOM();
                    
                    this.tryInitPlayer();

                } catch (error) {
                    console.error('[Settings] Error:', error);
                    this.videoIds = ['ScMzIvxBSi4', 'kJQP7kiw5Fk'];
                    this.runningText = 'Gagal memuat informasi. Menghubungi server...';
                    this.updateRunningTextDOM();
                    this.tryInitPlayer();
                }
            }

            updateRunningTextDOM() {
                const el = document.getElementById('runningTextElement');
                if (el) {
                    el.textContent = this.runningText;
                }
            }
            
            onYouTubeApiReady() {
                this.youTubeApiReady = true;
                console.log('[YouTube] API script dimuat.');
                this.tryInitPlayer();
            }

            tryInitPlayer() {
                if (this.youTubeApiReady && this.videoIds.length > 0) {
                    console.log('[YouTube] Dependensi siap, memulai player...');
                    this.initYouTubePlayer();
                } else {
                    console.log('[YouTube] Menunggu dependensi lain (API atau Settings)...');
                }
            }
            
            initYouTubePlayer() {
                if (this.videoIds.length === 0) {
                    console.error('[YouTube] Tidak ada Video ID untuk dimainkan.');
                    document.getElementById('videoLoading').textContent = 'Tidak ada video terkonfigurasi.';
                    return;
                }
                
                console.log('[YouTube] Initializing player...');
                try {
                    // Tampilkan spinner sebelum membuat player
                    document.getElementById('videoLoading').style.display = 'flex'; 

                    this.youtubePlayer = new YT.Player('youtubePlayer', {
                        height: '100%',
                        width: '100%',
                        videoId: this.videoIds[this.currentVideoIndex],
                        playerVars: {
                            'autoplay': 1,
                            'controls': 0,
                            'rel': 0,
                            'showinfo': 0,
                            'modestbranding': 1,
                            'loop': 0,
                            'playsinline': 1,
                            'mute': 0 // Hapus baris 'origin'
                        },
                        events: {
                            'onReady': (event) => this.onPlayerReady(event),
                            'onStateChange': (event) => this.onPlayerStateChange(event),
                            'onError': (event) => this.onPlayerError(event)
                        }
                    });
                } catch (e) {
                    console.error('[YouTube] Gagal membuat player:', e);
                    // Tampilkan pesan error jika inisialisasi gagal total
                    const loadingEl = document.getElementById('videoLoading');
                    if(loadingEl) {
                        loadingEl.innerHTML = `<span>Gagal memuat YouTube Player. Periksa koneksi atau konsol error.</span>`;
                    }
                }
            }

            // [PERBAIKAN 24/10/2025] Fungsi ini diubah
            onPlayerReady(event) {
                console.log('[YouTube] Player Ready');
                // 1. Sembunyikan spinner
                document.getElementById('videoLoading').style.display = 'none';
                this.playerReady = true;

                // 2. Hapus pesan error lama (jika ada)
                const errorMsg = document.querySelector('.video-error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }

                // 3. Putar video dan coba unmute
                event.target.playVideo();
                event.target.unMute();
                
                this.isVideoMuted = event.target.isMuted();
                if(this.isVideoMuted) {
                    console.warn('[YouTube] Autoplay dengan suara diblokir oleh browser. Video akan diputar tanpa suara.');
                }
            }

            onPlayerStateChange(event) {
                if (event.data === YT.PlayerState.ENDED) {
                    console.log('[YouTube] Video selesai, memutar berikutnya...');
                    this.playNextVideo();
                }
                // Jika video mulai buffering atau belum mulai, pastikan spinner terlihat
                else if (event.data === YT.PlayerState.BUFFERING || event.data === YT.PlayerState.UNSTARTED) {
                    if (!this.playerReady) { // Hanya tampilkan spinner jika player belum ready
                       document.getElementById('videoLoading').style.display = 'flex';
                    }
                }
                // Jika video sudah mulai main, sembunyikan spinner
                else if (event.data === YT.PlayerState.PLAYING) {
                    document.getElementById('videoLoading').style.display = 'none';
                     // Hapus juga pesan error jika ada
                    const errorMsg = document.querySelector('.video-error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            }

            // [PERBAIKAN 24/10/2025] Fungsi ini diubah total
            onPlayerError(event) {
                // 1. Pastikan spinner utama tersembunyi
                document.getElementById('videoLoading').style.display = 'none';

                console.error('[YouTube] Player Error:', event.data, 'pada video ID:', this.videoIds[this.currentVideoIndex]);
                
                // 2. Tampilkan pesan error di dalam player (jika belum ada)
                const playerWrapper = document.getElementById('videoWrapper');
                if(playerWrapper && !playerWrapper.querySelector('.video-error-message')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'video-error-message'; // Gunakan class CSS
                    errorDiv.innerHTML = `
                        <span class="material-icons">error_outline</span>
                        <span>Video tidak dapat dimuat (Error: ${event.data}).</span>
                        <span>Mencoba video berikutnya...</span>
                    `;
                    playerWrapper.appendChild(errorDiv);
                }

                // 3. Coba lewati video yang error setelah 3 detik
                if (this.videoIds.length > 1) {
                    console.warn('[YouTube] Mencoba memutar video berikutnya dalam 3 detik...');
                    setTimeout(() => {
                        this.playNextVideo();
                    }, 3000); // Jeda 3 detik
                } else {
                     console.warn('[YouTube] Hanya ada satu video dan error, tidak bisa memutar video lain.');
                     // Opsional: Tampilkan pesan permanen jika hanya 1 video
                     const errorDiv = playerWrapper.querySelector('.video-error-message span:last-child');
                     if(errorDiv) errorDiv.textContent = 'Hanya ada satu video dan gagal dimuat.';
                }
            }

            // [PERBAIKAN 24/10/2025] Fungsi ini diubah
            playNextVideo() {
                // Jangan lakukan apa-apa jika player belum siap
                if (!this.youtubePlayer || typeof this.youtubePlayer.loadVideoById !== 'function') {
                    console.warn('[YouTube] Player belum siap untuk memutar video berikutnya.');
                    return;
                }

                // 1. Hapus pesan error lama (jika ada)
                const errorMsg = document.querySelector('.video-error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
                
                // 2. Tampilkan lagi spinner sebelum memuat
                document.getElementById('videoLoading').style.display = 'flex';
                this.playerReady = false; // Tandai player belum ready lagi saat loading
                
                // 3. Pindah ke video berikutnya
                this.currentVideoIndex = (this.currentVideoIndex + 1) % this.videoIds.length;
                const nextVideoId = this.videoIds[this.currentVideoIndex];
                
                console.log(`[YouTube] Memuat video ${this.currentVideoIndex + 1}/${this.videoIds.length}: ${nextVideoId}`);
                
                // 4. Muat video baru
                this.youtubePlayer.loadVideoById(nextVideoId);
                
                // 5. Status mute akan diatur di onPlayerReady() atau onStateChange()
                // Tidak perlu atur mute di sini
            }

            muteVideo() {
                if (this.playerReady && this.youtubePlayer && typeof this.youtubePlayer.mute === 'function' && !this.isVideoMuted) {
                    this.youtubePlayer.mute();
                    this.isVideoMuted = true;
                    document.getElementById('muteIndicator').classList.add('show');
                    console.log('[YouTube] Video di-mute (via API)');
                }
            }

            unmuteVideo() {
                if (this.playerReady && this.youtubePlayer && typeof this.youtubePlayer.unMute === 'function' && this.isVideoMuted) {
                    
                    setTimeout(() => {
                        if (this.isSpeaking) {
                            console.log('[YouTube] Unmute dibatalkan, panggilan baru dimulai.');
                            return;
                        }
                        
                        this.youtubePlayer.unMute();
                        this.isVideoMuted = false;
                        document.getElementById('muteIndicator').classList.remove('show');
                        console.log('[YouTube] Video di-unmute (via API)');
                    }, 500);
                }
            }

            // --- SISA FUNGSI ANTRIAN (TIDAK BERUBAH) ---
            
            setupFullscreen() {
                const btn = document.getElementById('fullscreenBtn');
                btn.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => console.error(err));
                    } else {
                        document.exitFullscreen();
                    }
                });
                document.addEventListener('fullscreenchange', () => {
                    const icon = document.fullscreenElement ? 'fullscreen_exit' : 'fullscreen';
                    btn.innerHTML = `<span class="material-icons">${icon}</span>`;
                });
            }

            updateClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                document.getElementById('currentTime').innerHTML = `<span class="material-icons">schedule</span> ${date}, ${time}`;
            }

            async fetchData() {
                try {
                    const response = await fetch('/display/queue-data'); 
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    const data = await response.json();
                    if (data.status) {
                        this.renderLoketGrid(data.data.lokets);
                        this.processCalls(data.data.lokets);
                    }
                } catch (error) {
                    console.error('Error fetching queue data:', error);
                }
            }

            renderLoketGrid(lokets) {
                const gridEl = document.getElementById('loketGrid');
                gridEl.innerHTML = lokets.map(loket => {
                    const isCalling = !!loket.current_calling;
                    const hasWaiting = loket.waiting_count && loket.waiting_count > 0; 
                    
                    let statusText = 'Tersedia';
                    let statusClass = 'idle';
                    
                    if(isCalling) {
                        statusText = `Melayani: ${loket.current_calling}`;
                        statusClass = 'calling';
                    } else if (hasWaiting) {
                        statusText = `${loket.waiting_count} Antrean Menunggu`;
                        statusClass = 'waiting';
                    }

                    return `
                        <div class="loket-card ${isCalling ? 'active' : ''}" data-loket-id="${loket.id}">
                            <div class="current-number ${isCalling ? '' : 'empty'}">
                                ${isCalling ? loket.current_calling : '—'}
                            </div>
                            <div class="loket-name">${loket.nama_loket}</div>
                            <div class="loket-status ${statusClass}">${statusText}</div>
                        </div>
                    `;
                }).join('');
            }
            
            processCalls(lokets) {
                let hasAnyCalling = false;
                let latestNewCall = null;

                lokets.forEach(loket => {
                    const loketId = loket.id;
                    const currentCalling = loket.current_calling;
                    const callTimestamp = loket.call_timestamp; 
                    const currentIdentifier = currentCalling ? `${loketId}:${currentCalling}:${callTimestamp}` : null;
                    const previousIdentifier = this.previousHash[loketId];

                    if (currentCalling) {
                        hasAnyCalling = true;
                        
                        if (currentIdentifier !== previousIdentifier) {
                            console.log(`[ANNOUNCEMENT TRIGGERED] Loket ${loketId}: ${currentCalling}`);
                            
                            this.previousHash[loketId] = currentIdentifier;
                            
                            latestNewCall = {
                                number: currentCalling,
                                loketName: loket.nama_loket,
                                loketId: loketId,
                                voiceText: loket.voice_text 
                            };
                            
                            this.queueAnnouncement(latestNewCall.voiceText, currentCalling, loket.nama_loket);
                        }
                    } else {
                        if (previousIdentifier) {
                            this.previousHash[loketId] = null;
                        }
                    }
                });

                if (latestNewCall) {
                    this.currentDisplayedCall = latestNewCall;
                    this.showCall(latestNewCall.number, latestNewCall.loketName);
                } else if (hasAnyCalling) {
                    if (this.currentDisplayedCall) {
                        const stillActive = lokets.find(l => 
                            l.id === this.currentDisplayedCall.loketId && 
                            l.current_calling === this.currentDisplayedCall.number
                        );
                        if (stillActive) return;
                    }
                    
                    const firstCalling = lokets.find(l => l.current_calling);
                    if (firstCalling) {
                        this.currentDisplayedCall = {
                            number: firstCalling.current_calling,
                            loketName: firstCalling.nama_loket,
                            loketId: firstCalling.id,
                            voiceText: firstCalling.voice_text
                        };
                        this.showCall(firstCalling.current_calling, firstCalling.nama_loket);
                    }
                } else {
                    this.currentDisplayedCall = null;
                    this.showPlaceholder();
                }
            }

            queueAnnouncement(voiceText, number, loketName) {
                if (!this.speechEnabled) {
                    console.log('[SPEECH DISABLED] Would announce:', number, loketName);
                    return;
                }
                
                const textToSpeak = voiceText || `Nomor Antrian, ${number.split('').join(' ')}, silakan menuju ke, loket ${loketName}`;
                
                console.log('[QUEUE] Adding to speech queue:', textToSpeak);
                this.speechQueue.push({ text: textToSpeak });
                
                if (!this.isSpeaking) {
                    this.processNextAnnouncement();
                }
            }

            processNextAnnouncement() {
                if (this.speechQueue.length === 0) {
                    this.isSpeaking = false;
                    console.log('[QUEUE] Speech queue empty');
                    this.unmuteVideo();
                    return;
                }

                this.isSpeaking = true;
                const { text } = this.speechQueue.shift();
                console.log('[QUEUE] Processing:', text, `(${this.speechQueue.length} remaining)`);
                
                this.muteVideo();
                
                this.playAnnouncement(text, () => {
                    setTimeout(() => {
                        this.processNextAnnouncement();
                    }, 500);
                });
            }

            showCall(number, loketName) {
                const currentNumber = this.mainDisplay.querySelector('.current-number-display');
                const currentLoket = this.mainDisplay.querySelector('.current-loket-display span:nth-child(2)');
                
                if (currentNumber && currentNumber.textContent === number &&
                    currentLoket && currentLoket.textContent === loketName) {
                    return;
                }
                
                this.mainDisplay.innerHTML = `
                    <div class="current-call">
                        <div class="current-call-title">SEDANG DIPANGGIL</div>
                        <div class="current-call-content">
                            <div class="current-number-display">${number}</div>
                        </div>
                        <div class="current-call-footer">
                            <div class="current-loket-display">
                                <span class="material-icons">desktop_windows</span>
                                <span>${loketName}</span>
                                <div class="sound-wave">
                                    <span></span><span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            showPlaceholder() {
                if (this.mainDisplay.querySelector('.placeholder-call')) {
                    return;
                }
                this.mainDisplay.innerHTML = `
                    <div class="placeholder-call">
                        <span class="material-icons">notifications_paused</span>
                        <p>Menunggu Panggilan Berikutnya</p>
                    </div>
                `;
            }

            playAnnouncement(textToSpeak, onComplete) {
                console.log(`[SPEECH] Speaking: "${textToSpeak}"`);
                
                window.speechSynthesis.cancel();
                
                setTimeout(() => {
                    const utterance = new SpeechSynthesisUtterance(textToSpeak);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.85;
                    utterance.pitch = 1.0;
                    utterance.volume = 1.0;
                    
                    utterance.onstart = () => console.log('[SPEECH] Started');
                    utterance.onend = () => {
                        console.log('[SPEECH] Ended');
                        if (onComplete) onComplete();
                    };
                    utterance.onerror = (e) => {
                        console.error('[SPEECH ERROR]:', e);
                        if (onComplete) onComplete();
                    };
                    
                    window.speechSynthesis.speak(utterance);
                }, 250);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            queueDisplayApp = new ModernQueueDisplay();
        });
    </script>
</body>
</html>