<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            padding: 20px;
            color: #1e293b;
            overflow-x: hidden;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            height: calc(100vh - 40px);
            display: grid;
            grid-template-columns: 70% 30%;
            grid-template-rows: auto 1fr;
            gap: 20px;
            position: relative;
        }

        /* Header */
        .header {
            background: #ffffff;
            grid-column: 1 / -1;
            border-radius: 20px;
            padding: 25px 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1d4ed8;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header .subtitle {
            font-size: 1.2rem;
            color: #64748b;
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto 15px;
            line-height: 1.5;
        }

        .current-time {
            font-size: 1.1rem;
            color: #475569;
            font-weight: 500;
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 50px;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fee2e2;
            color: #dc2626;
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 10px;
            animation: pulseLive 2s infinite;
        }

        .live-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #dc2626;
            border-radius: 50%;
        }

        @keyframes pulseLive {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Main Content - Left Side */
        .main-display {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 25px;
            justify-content: center;
        }

        .current-call {
            text-align: center;
            padding: 40px;
            background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
            border-radius: 20px;
            color: white;
            box-shadow: 0 10px 30px rgba(29, 78, 216, 0.2);
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 600px;
        }

        .current-call::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.7) 50%, rgba(255,255,255,0.3) 100%);
            background-size: 200% 100%;
            animation: gradientLine 3s infinite linear;
        }

        @keyframes gradientLine {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .current-call-header {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .current-call-title {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            opacity: 0.9;
        }

        .current-call-subtitle {
            font-size: 1rem;
            opacity: 0.8;
            font-weight: 400;
        }

        .current-call-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 20px 0;
        }

        .number-container {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 30px 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .current-number-display {
            font-size: 7rem;
            font-weight: 900;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: numberPulse 2s infinite;
            letter-spacing: -2px;
        }

        @keyframes numberPulse {
            0%, 100% { transform: scale(1); text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); }
            50% { transform: scale(1.05); text-shadow: 0 8px 25px rgba(0, 0, 0, 0.4); }
        }

        .current-call-footer {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 255, 255, 0.2);
        }

        .current-loket-display {
            font-size: 2.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 30px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }

        .call-instruction {
            font-size: 1.1rem;
            opacity: 0.85;
            font-weight: 500;
            animation: fadeInOut 3s infinite;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.85; }
            50% { opacity: 1; }
        }

        .sound-wave {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            margin-left: 15px;
        }

        .sound-wave span {
            display: block;
            width: 4px;
            height: 16px;
            background: currentColor;
            animation: wave 1.2s infinite;
            border-radius: 2px;
        }

        .sound-wave span:nth-child(2) { animation-delay: 0.2s; }
        .sound-wave span:nth-child(3) { animation-delay: 0.4s; }
        .sound-wave span:nth-child(4) { animation-delay: 0.6s; }

        @keyframes wave {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.3); }
        }

        /* Right Side - Loket List */
        .loket-list {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
        }

        .loket-list-header {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1d4ed8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .loket-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px; /* Mengurangi gap antar kartu */
        }

        .loket-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 15px; /* Mengurangi padding */
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 10px; /* Mengurangi gap antar elemen dalam kartu */
            text-align: center;
            min-height: 180px; /* Menetapkan tinggi minimum untuk konsistensi */
        }

        .loket-card.active {
            border-color: #1d4ed8;
            background: rgba(29, 78, 216, 0.05);
            box-shadow: 0 8px 25px rgba(29, 78, 216, 0.15);
            transform: translateY(-2px);
        }

        .loket-name {
            font-size: 1.2rem; /* Mengurangi ukuran font */
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px; /* Mengurangi margin */
        }

        .loket-status {
            font-size: 0.85rem; /* Mengurangi ukuran font */
            padding: 5px 12px; /* Mengurangi padding */
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin: 0 auto 10px; /* Mengurangi margin */
            width: fit-content;
        }

        .loket-status.calling {
            background: #dcfce7;
            color: #15803d;
        }

        .loket-status.waiting {
            background: #fffbeb;
            color: #b45309;
        }

        .loket-status.idle {
            background: #f1f5f9;
            color: #64748b;
        }

        .current-number {
            font-size: 2rem; /* Mengurangi ukuran font */
            font-weight: 800;
            color: #1d4ed8;
            padding: 10px 0; /* Mengurangi padding */
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .current-number.empty {
            color: #cbd5e1;
            font-size: 1.5rem; /* Mengurangi ukuran font */
        }

        .next-title {
            font-size: 0.85rem; /* Mengurangi ukuran font */
            color: #64748b;
            font-weight: 600;
            margin-top: 5px; /* Mengurangi margin */
            margin-bottom: 5px; /* Mengurangi margin */
        }

        .next-numbers {
            display: flex;
            gap: 8px; /* Mengurangi gap */
            flex-wrap: wrap;
            justify-content: center;
        }

        .next-number {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px 12px; /* Mengurangi padding */
            font-weight: 600;
            font-size: 0.85rem; /* Mengurangi ukuran font */
            color: #475569;
        }

        .next-number.next-up {
            border-color: #f59e0b;
            background: #fffbeb;
            color: #b45309;
        }

        /* Connection Status */
        .connection-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .connection-status.connected {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .connection-status.disconnected {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Fullscreen Button */
        .fullscreen-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #1d4ed8;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .fullscreen-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        /* Loading State */
        .loading-state {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .loading-content {
            text-align: center;
            color: #64748b;
        }

        .loading-content .material-icons {
            font-size: 3rem;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .container {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto;
            }
            
            .current-number-display {
                font-size: 6rem;
            }
            
            .current-loket-display {
                font-size: 2rem;
            }
            
            .current-call {
                min-height: 400px;
                padding: 30px;
            }
            
            .number-container {
                padding: 30px 45px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .header .subtitle {
                font-size: 1rem;
            }
            
            .current-number-display {
                font-size: 4.5rem;
            }
            
            .current-loket-display {
                font-size: 1.6rem;
            }
            
            .main-display, .loket-list {
                padding: 20px;
            }
            
            .loket-name {
                font-size: 1.1rem;
            }
            
            .current-number {
                font-size: 1.8rem;
            }
            
            .current-number.empty {
                font-size: 1.3rem;
            }
            
            .number-container {
                padding: 25px 35px;
            }
            
            .current-call {
                min-height: 380px;
                padding: 25px;
            }
        }

        @media (max-width: 480px) {
            .current-number-display {
                font-size: 3.5rem;
            }
            
            .current-call-title {
                font-size: 1.3rem;
            }
            
            .current-loket-display {
                font-size: 1.4rem;
            }
            
            .number-container {
                padding: 15px 25px;
            }
            
            .current-call {
                min-height: 300px;
                padding: 20px;
            }
            
            .loket-card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Connection Status -->
    <div class="connection-status connected" id="connectionStatus">
        <span class="material-icons">wifi</span>
        <span>Terhubung</span>
    </div>

    <!-- Fullscreen Button -->
    <div class="fullscreen-btn" id="fullscreenBtn" title="Layar Penuh">
        <span class="material-icons">fullscreen</span>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SISTEM ANTRIAN DIGITAL</h1>
            <p class="subtitle">Monitor Antrian Real-time - Pelayanan Terpadu</p>
            <div class="current-time" id="currentTime"></div>
            <div class="live-indicator" id="liveIndicator">
                LIVE - <span id="updateStatus">Memperbarui data...</span>
            </div>
        </div>

        <!-- Main Display - Left Side -->
        <div class="main-display">
            <!-- Current Call Display -->
            <div class="current-call">
                <div class="current-call-header">
                    <div class="current-call-title">
                        <span class="material-icons">volume_up</span>
                        SEDANG DIPANGGIL
                    </div>
                    <div class="current-call-subtitle">Silakan menuju ke loket yang ditentukan</div>
                </div>
                
                <div class="current-call-content">
                    <div class="number-container">
                        <div class="current-number-display" id="currentNumberDisplay">
                            A001
                        </div>
                    </div>
                </div>
                
                <div class="current-call-footer">
                    <div class="current-loket-display" id="currentLoketDisplay">
                        <span class="material-icons">desktop_windows</span>
                        <span id="loketName">Loket A</span>
                        <div class="sound-wave">
                            <span></span><span></span><span></span><span></span>
                        </div>
                    </div>
                    <div class="call-instruction">Harap datang dengan membawa dokumen yang diperlukan</div>
                </div>
            </div>
        </div>

        <!-- Loket List - Right Side -->
        <div class="loket-list">
            <div class="loket-list-header">
                <span class="material-icons">view_list</span>
                DAFTAR LOKET PELAYANAN
            </div>
            <div class="loket-grid" id="loketGrid">
                <!-- Loket A -->
                <div class="loket-card active">
                    <div class="loket-name">Loket A</div>
                    <div class="loket-status calling">
                        Sedang Melayani
                    </div>
                    <div class="current-number">
                        A001
                    </div>
                </div>
                
                <!-- Loket B -->
                <div class="loket-card">
                    <div class="loket-name">Loket B</div>
                    <div class="loket-status idle">
                        Tidak Ada Antrian
                    </div>
                    <div class="current-number empty">
                        —
                    </div>
                </div>
                
                <!-- Loket C -->
                <div class="loket-card">
                    <div class="loket-name">Loket C</div>
                    <div class="loket-status idle">
                        Tidak Ada Antrian
                    </div>
                    <div class="current-number empty">
                        —
                    </div>
                </div>
                
                <!-- Loket D -->
                <div class="loket-card">
                    <div class="loket-name">Loket D</div>
                    <div class="loket-status idle">
                        Tidak Ada Antrian
                    </div>
                    <div class="current-number empty">
                        —
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        class QueueDisplaySystem {
            constructor() {
                this.previousData = null;
                this.speechEnabled = true;
                this.connectionStatus = true;
                this.updateInterval = null;
                this.lastUpdateTime = null;
                this.updateTimer = null;
                this.currentCall = null;
                
                this.initializeSystem();
                this.setupEventListeners();
                this.startDataFetching();
                this.updateClock();
                this.setupFullscreen();
            }

            initializeSystem() {
                // Update clock every second
                setInterval(() => this.updateClock(), 1000);
                
                // Check for speech synthesis support
                if (!('speechSynthesis' in window)) {
                    console.warn('Speech synthesis tidak didukung di browser ini');
                    this.speechEnabled = false;
                }

                // Set up CSRF token for requests
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    window.csrfToken = token.getAttribute('content');
                }

                // Initialize update timer
                this.updateUpdateTimer();
            }

            setupFullscreen() {
                const fullscreenBtn = document.getElementById('fullscreenBtn');
                fullscreenBtn.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error(`Error attempting to enable fullscreen: ${err.message}`);
                        });
                        fullscreenBtn.innerHTML = '<span class="material-icons">fullscreen_exit</span>';
                        fullscreenBtn.title = 'Keluar Layar Penuh';
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                            fullscreenBtn.innerHTML = '<span class="material-icons">fullscreen</span>';
                            fullscreenBtn.title = 'Layar Penuh';
                        }
                    }
                });

                // Handle fullscreen change events
                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement) {
                        fullscreenBtn.innerHTML = '<span class="material-icons">fullscreen</span>';
                        fullscreenBtn.title = 'Layar Penuh';
                    }
                });
            }

            setupEventListeners() {
                // Handle visibility changes
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.pauseUpdates();
                    } else {
                        this.resumeUpdates();
                    }
                });

                // Handle online/offline status
                window.addEventListener('online', () => {
                    this.updateConnectionStatus(true);
                    this.resumeUpdates();
                });

                window.addEventListener('offline', () => {
                    this.updateConnectionStatus(false);
                });
            }

            updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                const dateString = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                document.getElementById('currentTime').innerHTML = `
                    <span class="material-icons">schedule</span>
                    ${dateString}, <strong>${timeString}</strong>
                `;
            }

            updateUpdateTimer() {
                if (this.updateTimer) clearInterval(this.updateTimer);
                
                this.updateTimer = setInterval(() => {
                    const now = new Date();
                    const diff = now - this.lastUpdateTime;
                    const seconds = Math.floor(diff / 1000);
                    
                    let statusText;
                    if (seconds < 10) {
                        statusText = 'Baru saja diperbarui';
                    } else if (seconds < 60) {
                        statusText = `Diperbarui ${seconds} detik lalu`;
                    } else {
                        const minutes = Math.floor(seconds / 60);
                        statusText = `Diperbarui ${minutes} menit lalu`;
                    }
                    
                    document.getElementById('updateStatus').textContent = statusText;
                }, 1000);
            }

            updateConnectionStatus(isConnected) {
                this.connectionStatus = isConnected;
                const statusEl = document.getElementById('connectionStatus');
                
                if (isConnected) {
                    statusEl.className = 'connection-status connected';
                    statusEl.innerHTML = '<span class="material-icons">wifi</span><span>Terhubung</span>';
                } else {
                    statusEl.className = 'connection-status disconnected';
                    statusEl.innerHTML = '<span class="material-icons">wifi_off</span><span>Terputus</span>';
                }
            }

            async fetchQueueData() {
                try {
                    const response = await fetch('/display/queue-data', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    
                    if (data.status) {
                        this.lastUpdateTime = new Date();
                        this.updateUpdateTimer();
                        this.updateDisplay(data.data);
                        this.updateConnectionStatus(true);
                    } else {
                        throw new Error(data.message || 'Failed to fetch data');
                    }

                } catch (error) {
                    console.error('Error fetching queue data:', error);
                    this.updateConnectionStatus(false);
                }
            }

            updateDisplay(data) {
                this.updateLoketGrid(data.lokets);
                this.updateCurrentCall(data.lokets);
                this.checkForNewCalls(data.lokets);
            }

            updateLoketGrid(lokets) {
                const gridEl = document.getElementById('loketGrid');
                
                if (!lokets || lokets.length === 0) {
                    gridEl.innerHTML = `
                        <div class="loading-state">
                            <div class="loading-content">
                                <span class="material-icons">inbox</span>
                                <p>Tidak ada data loket</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                // Sort lokets: calling ones first, then others
                const sortedLokets = [...lokets].sort((a, b) => {
                    if (a.current_calling && !b.current_calling) return -1;
                    if (!a.current_calling && b.current_calling) return 1;
                    return a.id - b.id;
                });

                gridEl.innerHTML = sortedLokets.map(loket => {
                    const hasCalling = loket.current_calling;
                    const nextQueues = loket.next_queues || [];
                    
                    return `
                        <div class="loket-card ${hasCalling ? 'active' : ''}">
                            <div class="loket-name">${loket.nama_loket}</div>
                            <div class="loket-status ${hasCalling ? 'calling' : (nextQueues.length > 0 ? 'waiting' : 'idle')}">
                                ${hasCalling ? 'Sedang Melayani' : (nextQueues.length > 0 ? 'Ada Antrian' : 'Tidak Ada Antrian')}
                            </div>
                            
                            <div class="current-number ${hasCalling ? '' : 'empty'}">
                                ${hasCalling || '—'}
                            </div>
                            
                            ${nextQueues.length > 0 ? `
                                <div>
                                    <div class="next-title">Antrian Berikutnya:</div>
                                    <div class="next-numbers">
                                        ${nextQueues.slice(0, 3).map((queue, index) => 
                                            `<div class="next-number ${index === 0 ? 'next-up' : ''}">${queue}</div>`
                                        ).join('')}
                                        ${nextQueues.length > 3 ? `<div class="next-number">+${nextQueues.length - 3}</div>` : ''}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');
            }

            updateCurrentCall(lokets) {
                // Find the first calling queue to display
                const callingLoket = lokets.find(l => l.current_calling);
                
                if (callingLoket) {
                    document.getElementById('currentNumberDisplay').textContent = callingLoket.current_calling;
                    document.getElementById('loketName').textContent = callingLoket.nama_loket;
                    this.currentCall = {
                        number: callingLoket.current_calling,
                        loket: callingLoket.nama_loket
                    };
                } else {
                    document.getElementById('currentNumberDisplay').textContent = '-';
                    document.getElementById('loketName').textContent = '-';
                    this.currentCall = null;
                }
            }

            checkForNewCalls(currentLokets) {
                if (!this.previousData) {
                    this.previousData = currentLokets;
                    return;
                }

                // Check for new calls
                currentLokets.forEach(currentLoket => {
                    const previousLoket = this.previousData.find(l => l.id === currentLoket.id);
                    
                    if (currentLoket.current_calling && 
                        (!previousLoket || previousLoket.current_calling !== currentLoket.current_calling)) {
                        
                        this.playAnnouncement({
                            kode_antrian: currentLoket.current_calling,
                            loket: currentLoket.nama_loket
                        });
                    }
                });

                this.previousData = currentLokets;
            }

            playAnnouncement(calling) {
                if (!this.speechEnabled) return;

                const text = `Silakan antrian ${calling.kode_antrian.split('').join(' ')} menuju ke ${calling.loket}`;
                
                // Stop any ongoing speech
                if (window.speechSynthesis.speaking) {
                    window.speechSynthesis.cancel();
                }

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                utterance.pitch = 1.0;
                utterance.volume = 1.0;

                utterance.onstart = () => {
                    console.log('Announcement started:', text);
                };

                utterance.onend = () => {
                    console.log('Announcement completed');
                };

                utterance.onerror = (event) => {
                    console.error('Speech error:', event);
                };

                // Small delay before speaking
                setTimeout(() => {
                    window.speechSynthesis.speak(utterance);
                }, 500);
            }

            startDataFetching() {
                // Initial fetch
                this.fetchQueueData();
                
                // Set up periodic updates every 3 seconds
                this.updateInterval = setInterval(() => {
                    if (this.connectionStatus && !document.hidden) {
                        this.fetchQueueData();
                    }
                }, 3000);
            }

            pauseUpdates() {
                if (this.updateInterval) {
                    clearInterval(this.updateInterval);
                    this.updateInterval = null;
                }
            }

            resumeUpdates() {
                if (!this.updateInterval) {
                    this.startDataFetching();
                }
            }
        }

        // Initialize the display when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new QueueDisplaySystem();
        });
    </script>
</body>
</html>