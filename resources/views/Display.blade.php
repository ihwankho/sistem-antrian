<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian - Semua Loket</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
            overflow-x: hidden;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .header .subtitle {
            font-size: 1.1rem;
            color: #718096;
            font-weight: 400;
        }

        .current-time {
            font-size: 1rem;
            color: #4a5568;
            font-weight: 500;
            margin-top: 8px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow: hidden;
        }

        /* Loket Grid */
        .loket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            flex: 1;
            overflow-y: auto;
            padding: 5px;
        }

        .loket-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .loket-card.has-calling {
            border-color: #48bb78;
            transform: scale(1.02);
            box-shadow: 0 20px 40px rgba(72, 187, 120, 0.2);
        }

        .loket-card.has-calling::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #48bb78, #38a169);
        }

        .loket-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .current-number {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 15px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .current-number.calling {
            color: #48bb78;
            animation: pulse 2s infinite;
        }

        .current-number.empty {
            color: #cbd5e0;
            font-size: 2rem;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        .queue-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .queue-status.calling {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .queue-status.waiting {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
        }

        .queue-status.empty {
            background: #f7fafc;
            color: #718096;
            border: 2px solid #e2e8f0;
        }

        .next-queues {
            background: #f8fafc;
            border-radius: 15px;
            padding: 15px;
            margin-top: 10px;
        }

        .next-title {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .next-numbers {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .next-number {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #4a5568;
            min-width: 50px;
            text-align: center;
        }

        .next-number.next-up {
            border-color: #ed8936;
            background: #fff5f0;
            color: #dd6b20;
        }

        /* Sound Wave Animation */
        .sound-wave {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            margin-left: 8px;
        }

        .sound-wave span {
            display: block;
            width: 3px;
            height: 12px;
            background: currentColor;
            animation: wave 1.5s infinite;
            border-radius: 2px;
        }

        .sound-wave span:nth-child(2) { animation-delay: 0.1s; }
        .sound-wave span:nth-child(3) { animation-delay: 0.2s; }
        .sound-wave span:nth-child(4) { animation-delay: 0.3s; }

        @keyframes wave {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.3); }
        }

        /* Footer Stats */
        .footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .footer-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: #f8fafc;
            border-radius: 50px;
            font-weight: 600;
            min-width: 120px;
            justify-content: center;
        }

        .stat-number {
            font-size: 1.3rem;
            color: #667eea;
        }

        .stat-label {
            color: #718096;
            font-size: 0.9rem;
        }

        /* Loading */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-state {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: #718096;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .loket-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
            
            .current-number {
                font-size: 3.5rem;
                height: 70px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
                gap: 15px;
            }
            
            .loket-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
            
            .current-number {
                font-size: 3rem;
                height: 60px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .footer-stats {
                gap: 15px;
            }
            
            .stat-item {
                padding: 10px 15px;
                min-width: 100px;
            }
        }

        @media (max-width: 480px) {
            .loket-grid {
                grid-template-columns: 1fr;
            }
            
            .current-number {
                font-size: 2.5rem;
                height: 50px;
            }
        }

        /* Connection Status */
        .connection-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .connection-status.connected {
            background: #48bb78;
            color: white;
        }

        .connection-status.disconnected {
            background: #f56565;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Connection Status -->
    <div class="connection-status connected" id="connectionStatus">
        <span class="material-icons" style="font-size: 14px; vertical-align: middle; margin-right: 4px;">wifi</span>
        Terhubung
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SISTEM ANTRIAN DIGITAL</h1>
            <p class="subtitle">Monitor Antrian Real-time - Semua Loket</p>
            <div class="current-time" id="currentTime"></div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="loket-grid" id="loketGrid">
                <div class="loading-state">
                    <div>
                        <div class="loading"></div>
                        <p style="margin-top: 15px;">Memuat data antrian...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Stats -->
        <div class="footer">
            <div class="footer-stats">
                <div class="stat-item">
                    <div class="stat-number" id="totalQueue">0</div>
                    <div class="stat-label">Total Hari Ini</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="waitingQueue">0</div>
                    <div class="stat-label">Menunggu</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="callingQueue">0</div>
                    <div class="stat-label">Dipanggil</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="completedQueue">0</div>
                    <div class="stat-label">Selesai</div>
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
                
                this.initializeSystem();
                this.setupEventListeners();
                this.startDataFetching();
                this.updateClock();
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

                // Keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'r' || e.key === 'R') {
                        this.fetchQueueData();
                    }
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
                
                document.getElementById('currentTime').textContent = `${dateString}, ${timeString}`;
            }

            updateConnectionStatus(isConnected) {
                this.connectionStatus = isConnected;
                const statusEl = document.getElementById('connectionStatus');
                
                if (isConnected) {
                    statusEl.className = 'connection-status connected';
                    statusEl.innerHTML = '<span class="material-icons" style="font-size: 14px; vertical-align: middle; margin-right: 4px;">wifi</span>Terhubung';
                } else {
                    statusEl.className = 'connection-status disconnected';
                    statusEl.innerHTML = '<span class="material-icons" style="font-size: 14px; vertical-align: middle; margin-right: 4px;">wifi_off</span>Terputus';
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
                        this.updateDisplay(data.data);
                        this.updateConnectionStatus(true);
                    } else {
                        throw new Error(data.message || 'Failed to fetch data');
                    }

                } catch (error) {
                    console.error('Error fetching queue data:', error);
                    this.updateConnectionStatus(false);
                    this.showError('Gagal memuat data antrian');
                }
            }

            updateDisplay(data) {
                this.updateLoketGrid(data.lokets);
                this.updateStats(data.stats);
                this.checkForNewCalls(data.lokets);
            }

            updateLoketGrid(lokets) {
                const gridEl = document.getElementById('loketGrid');
                
                if (!lokets || lokets.length === 0) {
                    gridEl.innerHTML = `
                        <div class="loading-state">
                            <div style="text-align: center;">
                                <span class="material-icons" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e0;">inbox</span>
                                <p>Tidak ada data loket</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                gridEl.innerHTML = lokets.map(loket => {
                    const hasCalling = loket.current_calling;
                    const nextQueues = loket.next_queues || [];
                    
                    return `
                        <div class="loket-card ${hasCalling ? 'has-calling' : ''}">
                            <div class="loket-title">${loket.nama_loket}</div>
                            
                            <div class="current-number ${hasCalling ? 'calling' : 'empty'}">
                                ${hasCalling || '-'}
                            </div>
                            
                            <div class="queue-status ${hasCalling ? 'calling' : (nextQueues.length > 0 ? 'waiting' : 'empty')}">
                                ${hasCalling 
                                    ? `<span class="material-icons">volume_up</span>
                                       Sedang Dipanggil
                                       <div class="sound-wave">
                                           <span></span><span></span><span></span><span></span>
                                       </div>`
                                    : nextQueues.length > 0
                                        ? `<span class="material-icons">schedule</span> ${nextQueues.length} Menunggu`
                                        : `<span class="material-icons">check_circle</span> Tidak Ada Antrian`
                                }
                            </div>
                            
                            ${nextQueues.length > 0 ? `
                                <div class="next-queues">
                                    <div class="next-title">Antrian Selanjutnya</div>
                                    <div class="next-numbers">
                                        ${nextQueues.slice(0, 5).map((queue, index) => 
                                            `<div class="next-number ${index === 0 ? 'next-up' : ''}">${queue}</div>`
                                        ).join('')}
                                        ${nextQueues.length > 5 ? `<div class="next-number">+${nextQueues.length - 5}</div>` : ''}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');
            }

            updateStats(stats) {
                if (!stats) return;
                
                document.getElementById('totalQueue').textContent = stats.total || 0;
                document.getElementById('waitingQueue').textContent = stats.waiting || 0;
                document.getElementById('callingQueue').textContent = stats.calling || 0;
                document.getElementById('completedQueue').textContent = stats.completed || 0;
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

            showError(message) {
                console.error(message);
                // Could add toast notification here
            }
        }

        // Initialize the display when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new QueueDisplaySystem();
        });
    </script>
</body>
</html>