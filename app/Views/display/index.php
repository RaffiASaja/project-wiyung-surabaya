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
            <h1>PEMERINTAH KOTA SURABAYA</h1>
            <h2>UPUBKB WIYUNG</h2>
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
    </div>

    <!-- 🔊 tombol unlock audio -->
    <button onclick="toggleAudio()" id="btnAudio">
        🔇 OFF
    </button>

    <script>
        let audioEnabled = false;
        let queue = [];
        let isSpeaking = false;
        let lastIds = [];
        let lastMap = {};

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

                            let text = `Nomor kendaraan ${item.nomor_kendaraan}, silakan menuju pos ${item.pos}`;

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

            // POS 1
            data.pos1.forEach((row) => {
                pos1.innerHTML += `
            <div class="card">
                <div class="card-header">POS 1</div>
                <div class="card-body">
                    <div class="label">Nomor Kendaraan:</div>
                    <div class="value">${row.nomor_kendaraan}</div>

                    <div class="label" style="margin-top:10px;">Nama Pemilik:</div>
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
                <div class="card-body">
                    <div class="label">Nomor Kendaraan:</div>
                    <div class="value">${row.nomor_kendaraan}</div>

                    <div class="label" style="margin-top:10px;">Nama Pemilik:</div>
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