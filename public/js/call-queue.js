let voiceEnabled = false;

// Fungsi ini akan aktifkan suara saat user pertama kali klik
function enableQueueVoice() {
    if (voiceEnabled) return;
    voiceEnabled = true;

    const utter = new SpeechSynthesisUtterance("Suara aktif");
    // speechSynthesis.speak(utter);

    console.log("🔊 Suara diaktifkan setelah interaksi user");
}

// Dengarkan interaksi pertama
document.addEventListener('click', function onceClick() {
    enableQueueVoice();
    document.removeEventListener('click', onceClick);
});

// Fungsi suara antrian
async function playQueueSound(message) {
    if (!voiceEnabled) {
        console.warn("❌ Suara belum diaktifkan");
        return;
    }

    const voices = await getVoices();
    const voiceToUse = voices.find(v => v.lang.includes("id")) || voices[0];

    const speech = new SpeechSynthesisUtterance(message);
    speech.voice = voiceToUse;
    speech.rate = 0.8;

    // speechSynthesis.cancel();
    // speechSynthesis.speak(speech);

     // Step 1: Mainkan suara pembuka
    const bellStart = new Audio('/asset/awal.mp3');
    bellStart.play().then(() => {
        bellStart.onended = () => {
            // Step 2: Mainkan TTS
            speechSynthesis.cancel();
            speechSynthesis.speak(speech);

            // Step 3: Saat TTS selesai, mainkan bell akhir
            speech.onend = () => {
                const bellEnd = new Audio('/asset/akhir.mp3');
                bellEnd.play().catch(error => {
                    console.error("Gagal memutar bell akhir:", error);
                });
            };
        };
    }).catch(error => {
        console.error("Gagal memutar bell awal:", error);
    });
}

// Ambil list suara
function getVoices() {
    return new Promise(resolve => {
        const interval = setInterval(() => {
            const voices = window.speechSynthesis.getVoices();
            if (voices.length) {
                clearInterval(interval);
                resolve(voices);
            }
        }, 10);
    });
}

// Listener Livewire
document.addEventListener("livewire:initialized", () => {
    Livewire.on("queue-called", playQueueSound);
});