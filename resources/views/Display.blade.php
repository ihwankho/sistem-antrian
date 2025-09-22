<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary-green: #0A4531;
            --accent-gold: #DA9928;
            --bg-light: #F8F9FA;
            --bg-card: #FFFFFF;
            --text-dark: #212529;
            --text-light: #FFFFFF;
            --text-gray: #6C757D;
            --border-color: #E9ECEF;
            --font-heading: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
            --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--bg-light);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
            overflow: hidden;
            /* Tambahkan padding bawah untuk ruang running text */
            padding-bottom: 60px;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            height: calc(100vh - 40px - 40px); /* Disesuaikan dengan padding body */
            display: grid;
            grid-template-columns: 1fr 380px;
            grid-template-rows: auto 1fr;
            gap: 20px;
        }

        /* Header */
        .header {
            background: var(--bg-card);
            grid-column: 1 / -1;
            border-radius: 16px;
            padding: 20px 30px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-info h1 {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-green);
            margin: 0;
        }
        
        .header-info .subtitle {
            font-size: 1rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        .header-time-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .current-time {
            font-size: 1.1rem;
            color: var(--text-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .current-time .material-icons {
            color: var(--primary-green);
        }

        /* Main Display - Left Side */
        .main-display {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .current-call {
            width: 100%;
            height: 100%;
            text-align: center;
            background: var(--primary-green);
            border-radius: 16px;
            color: var(--text-light);
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
            animation: fadeIn 1s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .current-call-title {
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 600;
            opacity: 0.9;
            border-bottom: 2px solid rgba(255,255,255,0.2);
            padding-bottom: 20px;
        }

        .current-call-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .current-number-display {
            font-family: var(--font-heading);
            font-size: clamp(6rem, 15vw, 12rem);
            font-weight: 800;
            line-height: 1;
            color: var(--accent-gold);
            text-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            animation: numberPulse 1.5s infinite;
        }

        @keyframes numberPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }

        .current-call-footer {
            border-top: 2px solid rgba(255,255,255,0.2);
            padding-top: 20px;
        }

        .current-loket-display {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .sound-wave {
            display: inline-flex;
            align-items: flex-end;
            gap: 4px;
            height: 24px;
        }

        .sound-wave span {
            display: block;
            width: 5px;
            background: var(--accent-gold);
            animation: wave 1.2s infinite ease-in-out;
            border-radius: 2px;
        }

        .sound-wave span:nth-child(1) { height: 10px; animation-delay: 0.1s; }
        .sound-wave span:nth-child(2) { height: 18px; animation-delay: 0.2s; }
        .sound-wave span:nth-child(3) { height: 24px; animation-delay: 0.3s; }
        .sound-wave span:nth-child(4) { height: 15px; animation-delay: 0.4s; }

        @keyframes wave {
            0%, 100% { transform: scaleY(0.2); }
            50% { transform: scaleY(1); }
        }

        /* Right Side - Loket List */
        .loket-list {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
        }

        .loket-list-header {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-green);
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .loket-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .loket-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: 1fr 2fr;
            grid-template-rows: auto auto;
            align-items: center;
            gap: 5px 15px;
        }

        .loket-card.active {
            border-left: 5px solid var(--accent-gold);
            background: #fffbeb;
        }

        .loket-name {
            grid-column: 2 / 3;
            grid-row: 1 / 2;
            font-family: var(--font-heading);
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .current-number {
            grid-column: 1 / 2;
            grid-row: 1 / 3;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-green);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .current-number.empty { color: var(--text-gray); font-size: 1.8rem; }
        
        .loket-card.active .current-number { color: var(--accent-gold); }

        .loket-status {
            grid-column: 2 / 3;
            grid-row: 2 / 3;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-gray);
        }

        .loket-status.calling { color: #15803d; }
        .loket-status.waiting { color: #b45309; }

        /* Fullscreen Button */
        .fullscreen-btn {
            position: fixed;
            bottom: 60px; /* Disesuaikan untuk running text */
            right: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--primary-green);
            width: 50px; height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }

        .fullscreen-btn:hover { background: var(--bg-light); transform: scale(1.1); }
        
        /* Placeholder State */
        .placeholder-call {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 20px;
            height: 100%;
        }
        .placeholder-call .material-icons {
            font-size: 5rem;
            color: var(--text-gray);
            opacity: 0.3;
        }
        .placeholder-call p {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        /* RUNNING TEXT BILLBOARD */
        .running-text-container {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: var(--primary-green);
            color: var(--text-light);
            padding: 10px 0;
            overflow: hidden;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
            z-index: 1001;
        }
        .running-text {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%; /* Mulai dari luar layar kanan */
            animation: marquee 30s linear infinite;
            font-size: 1.1rem;
            font-weight: 500;
        }
        @keyframes marquee {
            0%   { transform: translateX(0%); }
            100% { transform: translateX(-100%); }
        }


        /* Responsive */
        @media (max-width: 1200px) {
            .container {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto 1fr;
                height: auto;
            }
            .main-display {
                min-height: 450px;
            }
        }
        @media (max-width: 768px) {
            body { padding: 10px; padding-bottom: 50px; }
            .container { gap: 10px; height: calc(100vh - 20px - 38px); }
            .header { flex-direction: column; gap: 10px; padding: 15px; }
            .header-info h1 { font-size: 1.5rem; }
            .current-time { font-size: 1rem; }
            .current-call { padding: 25px; }
            .current-call-title { font-size: 1.5rem; }
            .current-number-display { font-size: clamp(5rem, 20vw, 8rem); }
            .current-loket-display { font-size: 2rem; }
            .loket-list { padding: 20px; }
            .loket-card { padding: 15px; }
            .running-text-container { padding: 8px 0; }
            .running-text { font-size: 1rem; }
            .fullscreen-btn { bottom: 50px; }
        }
    </style>
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

    <div class="running-text-container">
        <p class="running-text">
            SELAMAT DATANG DI PENGADILAN NEGERI BANYUWANGI - JAGA SELALU PROTOKOL KESEHATAN - DILARANG MEMBERI TIPS ATAU BINGKISAN DALAM BENTUK APAPUN KEPADA PETUGAS KAMI - TERIMA KASIH
        </p>
    </div>

    <script>
        class ModernQueueDisplay {
            constructor() {
                this.previousCalls = {};
                this.speechEnabled = true;
                this.updateInterval = null;
                this.lastDisplayedCall = null; // Menyimpan data panggilan terakhir
                
                this.mainDisplay = document.getElementById('mainDisplay');
                
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
            }

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
                    const hasWaiting = loket.next_queues && loket.next_queues.length > 0;
                    
                    let statusText = 'Tersedia';
                    let statusClass = 'idle';
                    
                    if(isCalling) {
                        statusText = `Melayani: ${loket.current_calling}`;
                        statusClass = 'calling';
                    } else if (hasWaiting) {
                        statusText = `${loket.next_queues.length} Antrean Menunggu`;
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
                const currentCalls = {};
                let isAnyLoketCalling = false;

                lokets.forEach(loket => {
                    if(loket.current_calling) {
                        currentCalls[loket.id] = loket.current_calling;
                        isAnyLoketCalling = true;
                    }
                });
                
                // Cek panggilan baru untuk announcement
                for (const loketId in currentCalls) {
                    if (this.previousCalls[loketId] !== currentCalls[loketId]) {
                        const loket = lokets.find(l => l.id == loketId);
                        this.lastDisplayedCall = { number: loket.current_calling, loketName: loket.nama_loket };
                        this.playAnnouncement(loket.current_calling, loket.nama_loket);
                    }
                }
                
                this.previousCalls = currentCalls;
                
                // Update tampilan utama
                if (isAnyLoketCalling) {
                    // Jika ada panggilan, tampilkan yang terakhir disimpan
                    if (this.lastDisplayedCall) {
                        this.showCall(this.lastDisplayedCall.number, this.lastDisplayedCall.loketName);
                    }
                } else {
                    // Jika tidak ada panggilan sama sekali, tampilkan placeholder
                    this.lastDisplayedCall = null; // Reset
                    this.showPlaceholder();
                }
            }

            showCall(number, loketName) {
                // Cek apakah panggilan yang sama sudah ditampilkan
                const currentDisplayedNumber = this.mainDisplay.querySelector('.current-number-display');
                if (currentDisplayedNumber && currentDisplayedNumber.textContent === number) {
                    return; // Sudah ditampilkan, tidak perlu render ulang
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
                // Cek apakah placeholder sudah ditampilkan
                if (this.mainDisplay.querySelector('.placeholder-call')) {
                    return; // Sudah ditampilkan, tidak perlu render ulang
                }

                this.mainDisplay.innerHTML = `
                    <div class="placeholder-call">
                        <span class="material-icons">notifications_paused</span>
                        <p>Menunggu Panggilan Berikutnya</p>
                    </div>
                `;
            }

            playAnnouncement(number, loketName) {
                if (!this.speechEnabled) return;
                
                const textToSpeak = `Nomor Antrian, ${number.split('').join(' ')}, silakan menuju ke, ${loketName}`;
                
                window.speechSynthesis.cancel();
                
                const utterance = new SpeechSynthesisUtterance(textToSpeak);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                
                utterance.onerror = (e) => console.error('Speech synthesis error:', e);
                window.speechSynthesis.speak(utterance);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new ModernQueueDisplay();
        });
    </script>
</body>
</html>