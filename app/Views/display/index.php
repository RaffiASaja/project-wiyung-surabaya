<!doctype html>
<html>

<head>
    <title>Display Antrian</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/display.css') ?>">
</head>

<body>

    <div class="display-container">

        <!-- HEADER -->
        <div class="header">
            <img class="logo" src="<?= base_url('assets/img/logo_kota_sby.svg') ?>" alt="">
            <h1>UPUBKB WIYUNG - DINAS PERHUBUNGAN KOTA SURABAYA</h1>
            <h2>(Unit Pengelolaan Pengujian Kendaraan Bermotor)</h2>
        </div>

        <div class="grid">

            <!-- POS 1 -->
            <div class="pos-column">
                <div class="pos-box" id="pos1-box"></div>
            </div>

            <!-- POS 2 -->
            <div class="pos-column">
                <div class="pos-box blue" id="pos2-box"></div>
            </div>

        </div>

        <div class="footer">
            <img class="logo-footer" src="<?= base_url('assets/img/logo_dishub.png') ?>" alt="">
            <div class="footer-text">
                <strong>Pemerintah Kota Surabaya - Dinas Perhubungan</strong>
                <span>© 2026 UPUBKB Wiyung</span>
            </div>

            <!-- 🔊 tombol unlock audio -->
            <button onclick="toggleAudio()" id="btnAudio">
                🔇 OFF
            </button>
        </div>
    </div>

    <script>
        let audioEnabled = false;
        let queue = [];
        let isSpeaking = false;
        let lastIds = [];
        let lastMap = {};

        function plateToWords(plate) {
            const letterNames = {
                A: 'a',
                B: 'be',
                C: 'ce',
                D: 'de',
                E: 'e',
                F: 'ef',
                G: 'ge',
                H: 'ha',
                I: 'i',
                J: 'je',
                K: 'ka',
                L: 'el',
                M: 'em',
                N: 'en',
                O: 'o',
                P: 'pe',
                Q: 'ki',
                R: 'er',
                S: 'es',
                T: 'te',
                U: 'u',
                V: 'fe',
                W: 'we',
                X: 'eks',
                Y: 'ye',
                Z: 'zet'
            };

            const digitNames = {
                0: 'nol',
                1: 'satu',
                2: 'dua',
                3: 'tiga',
                4: 'empat',
                5: 'lima',
                6: 'enam',
                7: 'tujuh',
                8: 'delapan',
                9: 'sembilan'
            };

            const raw = String(plate ?? '').trim().toUpperCase();
            if (!raw) return '';

            // pisahkan per "kata" (spasi / strip / titik)
            const tokens = raw.split(/[\s\-\.]+/).filter(Boolean);
            const out = [];

            const speakToken = (token) => {
                // token huruf saja -> per huruf
                if (/^[A-Z]+$/.test(token)) {
                    return token.split('').map(ch => letterNames[ch] ?? ch).join(' ');
                }
                // token angka saja -> per digit
                if (/^[0-9]+$/.test(token)) {
                    return token.split('').map(ch => digitNames[ch] ?? ch).join(' ');
                }
                // campuran -> per karakter
                return token.split('').map(ch => {
                    if (/[A-Z]/.test(ch)) return letterNames[ch] ?? ch;
                    if (/[0-9]/.test(ch)) return digitNames[ch] ?? ch;
                    return '';
                }).filter(Boolean).join(' ');
            };

            tokens.forEach((t) => out.push(speakToken(t)));
            return out.filter(Boolean).join(' ');
        }

        function toggleAudio() {
            audioEnabled = !audioEnabled;

            document.getElementById('btnAudio').innerText =
                audioEnabled ? '🔊 ON' : '🔇 OFF';

            // 🔥 unlock browser audio sekali saja
            if (audioEnabled) {
                speechSynthesis.speak(new SpeechSynthesisUtterance(" "));
            }
            if (audioEnabled && queue.length > 0 && !isSpeaking) {
                speakQueue();
            }
        }

        // 🔔 ding + TTS
        function playDingThenSpeak(text) {
            let ding = new Audio("<?= base_url('assets/sounds/ding.mp3') ?>");

            ding.play().then(() => {
                ding.onended = () => {
                    let utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.9;

                    utterance.onend = () => speakQueue();

                    speechSynthesis.speak(utterance);
                };
            }).catch(() => {
                // fallback kalau ding gagal
                let utterance = new SpeechSynthesisUtterance(text);
                utterance.onend = () => speakQueue();
                speechSynthesis.speak(utterance);
            });
        }


        // queue system
        function speakQueue() {
            if (!audioEnabled) {
                isSpeaking = false;
                return;
            }

            if (queue.length === 0) {
                isSpeaking = false;
                return;
            }

            isSpeaking = true;

            let text = queue.shift();
            playDingThenSpeak(text);
        }

        function addToQueue(text) {
            queue.push(text);

            if (!isSpeaking && audioEnabled) {
                speakQueue();
            }
        }

        // ambil data
        function fetchData() {
            fetch("<?= site_url('display/data') ?>")
                .then(res => res.json())
                .then(data => {

                    let all = [...data.pos1, ...data.pos2];

                    all.forEach(item => {

                        let key = item.id;
                        let time = item.waktu_panggil;

                        // 🔥 DETEKSI PERUBAHAN
                        if (!lastMap[key] || lastMap[key] !== time) {

                            const nomor = plateToWords(item.nomor_kendaraan);
                            const nama = String(item.nama ?? '').trim();
                            const pos = String(item.pos ?? '').trim();

                            let text = `Nomor kendaraan ${nomor || item.nomor_kendaraan}`;
                            if (nama) {
                                text += `, atas nama ${nama}`;
                            }
                            text += `, silakan menuju pos ${pos}`;

                            addToQueue(text);

                            // simpan state terbaru
                            lastMap[key] = time;
                        }
                    });

                    updateUI(data);
                });
        }

        // update tampilan
        function updateUI(data) {
            let pos1 = document.getElementById('pos1-box');
            let pos2 = document.getElementById('pos2-box');

            pos1.innerHTML = '';
            pos2.innerHTML = '';

            // reset class
            pos1.className = 'pos-box';
            pos2.className = 'pos-box blue';

            // 🔥 tambahkan class berdasarkan jumlah card
            pos1.classList.add('count-' + data.pos1.length);
            pos2.classList.add('count-' + data.pos2.length);

            // POS 1
            data.pos1.forEach((row) => {
                pos1.innerHTML += `
        <div class="card">
            <div class="card-header">POS 1</div>

            <div class="card-row">
                <div class="label">Nomor Kendaraan:</div>
                <div class="value">${row.nomor_kendaraan}</div>
            </div>

            <div class="card-row">
                <div class="label">Nama Pemilik:</div>
                <div class="value">${row.nama}</div>
            </div>
        </div>
        `;
            });

            // POS 2
            data.pos2.forEach((row) => {
                pos2.innerHTML += `
        <div class="card blue">
            <div class="card-header">POS 2</div>

            <div class="card-row">
                <div class="label">Nomor Kendaraan:</div>
                <div class="value">${row.nomor_kendaraan}</div>
            </div>

            <div class="card-row">
                <div class="label">Nama Pemilik:</div>
                <div class="value">${row.nama}</div>
            </div>
        </div>
        `;
            });
        }

        // polling
        setInterval(fetchData, 5000);
        fetchData();
    </script>

</body>

</html>
