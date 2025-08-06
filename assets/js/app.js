import { Gauge } from 'gaugeJS';

const startButton = document.getElementById('start-test');
const pingElement = document.getElementById('ping');
const downloadElement = document.getElementById('download');
const uploadElement = document.getElementById('upload');
const statusText = document.querySelector('.status-text');
const gaugeSpeed = document.querySelector('.gauge-speed');

const testDuration = 10000; // 10 seconds

// Gauge setup
const opts = {
    angle: -0.2,
    lineWidth: 0.2,
    radiusScale: 1,
    pointer: {
        length: 0.6,
        strokeWidth: 0.035,
        color: '#ccd6f6'
    },
    limitMax: false,
    limitMin: false,
    colorStart: '#64ffda',
    colorStop: '#64ffda',
    strokeColor: '#112240',
    generateGradient: true,
    highDpiSupport: true,
};
const target = document.getElementById('speed-gauge');
const gauge = new Gauge(target).setOptions(opts);
gauge.maxValue = 100;
gauge.set(0);

function setGaugeSpeed(speed) {
    gauge.set(speed);
    gaugeSpeed.textContent = speed.toFixed(2);
}

async function testPing() {
    statusText.textContent = 'Testing Ping...';
    const startTime = new Date().getTime();
    await fetch('/ping', { method: 'HEAD', cache: 'no-store' });
    const endTime = new Date().getTime();
    const pingTime = endTime - startTime;
    pingElement.textContent = pingTime;
    return pingTime;
}

async function testDownload() {
    statusText.textContent = 'Testing Download Speed...';
    let totalBytes = 0;
    const startTime = new Date().getTime();

    const download = async () => {
        while ((new Date().getTime() - startTime) < testDuration) {
            const response = await fetch('/download?nocache=' + new Date().getTime());
            const reader = response.body.getReader();

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                totalBytes += value.length;
                const duration = (new Date().getTime() - startTime) / 1000;
                const speedMbps = (totalBytes * 8) / duration / 1024 / 1024;
                downloadElement.textContent = speedMbps.toFixed(2);
                setGaugeSpeed(speedMbps);
            }
        }
    };

    const downloads = Array(5).fill(null).map(download);
    await Promise.all(downloads);
}

async function testUpload() {
    statusText.textContent = 'Testing Upload Speed...';
    let totalBytes = 0;
    const startTime = new Date().getTime();
    const data = new Blob([new ArrayBuffer(1024 * 1024)], { type: 'application/octet-stream' }); // 1MB

    const upload = async () => {
        while ((new Date().getTime() - startTime) < testDuration) {
            await fetch('/upload', { method: 'POST', body: data });
            totalBytes += data.size;
            const duration = (new Date().getTime() - startTime) / 1000;
            const speedMbps = (totalBytes * 8) / duration / 1024 / 1024;
            uploadElement.textContent = speedMbps.toFixed(2);
            setGaugeSpeed(speedMbps);
        }
    };

    const uploads = Array(5).fill(null).map(upload);
    await Promise.all(uploads);
}

startButton.addEventListener('click', async () => {
    startButton.disabled = true;
    pingElement.textContent = '-';
    downloadElement.textContent = '-';
    uploadElement.textContent = '-';
    setGaugeSpeed(0);

    await testPing();
    await testDownload();
    setGaugeSpeed(0); // Reset gauge after download
    await testUpload();
    setGaugeSpeed(0); // Reset gauge after upload

    statusText.textContent = 'Test Complete!';
    startButton.disabled = false;
});
