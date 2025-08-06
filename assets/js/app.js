import { Gauge } from 'gaugeJS';

const startButton = document.getElementById('start-test');
const pingElement = document.getElementById('ping');
const downloadSpeedElement = document.getElementById('download-speed');
const uploadSpeedElement = document.getElementById('upload-speed');
const statusText = document.querySelector('.status-text');
const downloadWrapper = document.getElementById('download-wrapper');
const uploadWrapper = document.getElementById('upload-wrapper');

const testDuration = 10000; // 10 seconds

// Gauge setup
const commonOpts = {
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
    strokeColor: '#112240',
    generateGradient: true,
    highDpiSupport: true,
};

const downloadGauge = new Gauge(document.getElementById('download-gauge')).setOptions({ ...commonOpts, colorStart: '#64ffda', colorStop: '#64ffda' });
const uploadGauge = new Gauge(document.getElementById('upload-gauge')).setOptions({ ...commonOpts, colorStart: '#ff64da', colorStop: '#ff64da' });

downloadGauge.maxValue = 100;
downloadGauge.set(0);
uploadGauge.maxValue = 100;
uploadGauge.set(0);

function setSpeed(gauge, element, speed) {
    gauge.set(speed);
    element.textContent = speed.toFixed(0);
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
    downloadWrapper.classList.add('active');
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
                setSpeed(downloadGauge, downloadSpeedElement, speedMbps);
            }
        }
    };

    const downloads = Array(5).fill(null).map(download);
    await Promise.all(downloads);
}

async function testUpload() {
    statusText.textContent = 'Testing Upload Speed...';
    uploadWrapper.classList.add('active');
    let totalBytes = 0;
    const startTime = new Date().getTime();
    const data = new Blob([new ArrayBuffer(1024 * 1024)], { type: 'application/octet-stream' }); // 1MB

    const upload = async () => {
        while ((new Date().getTime() - startTime) < testDuration) {
            await fetch('/upload', { method: 'POST', body: data });
            totalBytes += data.size;
            const duration = (new Date().getTime() - startTime) / 1000;
            const speedMbps = (totalBytes * 8) / duration / 1024 / 1024;
            setSpeed(uploadGauge, uploadSpeedElement, speedMbps);
        }
    };

    const uploads = Array(5).fill(null).map(upload);
    await Promise.all(uploads);
}

startButton.addEventListener('click', async () => {
    startButton.disabled = true;
    startButton.textContent = 'Testing...';
    pingElement.textContent = '-';
    setSpeed(downloadGauge, downloadSpeedElement, 0);
    setSpeed(uploadGauge, uploadSpeedElement, 0);
    downloadWrapper.classList.remove('active');
    uploadWrapper.classList.remove('active');

    await testPing();
    await testDownload();
    await testUpload();

    statusText.textContent = 'Test Complete!';
    startButton.textContent = 'Test Again';
    startButton.disabled = false;
});