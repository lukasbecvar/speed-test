// main app functionality
import { Gauge } from 'gaugeJS'

document.addEventListener('DOMContentLoaded', function () {

    // main page elements
    const pingElement = document.getElementById('ping')
    const startButton = document.getElementById('start-test')
    const statusText = document.querySelector('.status-text')
    const uploadWrapper = document.getElementById('upload-wrapper')
    const uploadSpeedElement = document.getElementById('upload-speed')
    const downloadWrapper = document.getElementById('download-wrapper')
    const downloadSpeedElement = document.getElementById('download-speed')

    // modal elements
    const resultsModal = document.getElementById('results-modal')
    const closeModalButton = document.getElementById('close-modal')
    const maxUploadResult = document.getElementById('max-upload-result')
    const pingResultModal = document.getElementById('ping-result-modal')
    const maxDownloadResult = document.getElementById('max-download-result')

    const testDuration = 10000 // 10 seconds

    let maxDownloadSpeed = 0
    let maxUploadSpeed = 0

    // gauge setup
    const commonOpts = {
        angle: -0.2,
        lineWidth: 0.1,
        radiusScale: 0.9,
        pointer: {
            length: 0.6,
            strokeWidth: 0.035,
            color: '#ccd6f6'
        },
        limitMin: false,
        strokeColor: '#29416e',
        highDpiSupport: true,
        staticLabels: {
            color: "#8892b0",
            font: "12px sans-serif",
            labels: [0, 10, 20, 50, 100],
            fractionDigits: 0
        },
        staticZones: [
            { strokeStyle: "#30B32D", min: 0, max: 20 },
            { strokeStyle: "#FFDD00", min: 20, max: 50 },
            { strokeStyle: "#F03E3E", min: 50, max: 100 }
        ],
        renderTicks: {
            divisions: 5,
            divWidth: 1.1,
            subWidth: 0.6,
            subLength: 0.5,
            divLength: 0.7,
            subDivisions: 3,
            divColor: '#333333',
            subColor: '#666666'
        }
    }

    const downloadGauge = new Gauge(document.getElementById('download-gauge')).setOptions(JSON.parse(JSON.stringify(commonOpts)))
    const uploadGauge = new Gauge(document.getElementById('upload-gauge')).setOptions(JSON.parse(JSON.stringify(commonOpts)))

    downloadGauge.maxValue = 100
    downloadGauge.set(0)
    uploadGauge.maxValue = 100
    uploadGauge.set(0)

    function updateGaugeScale(gauge, speed) {
        if (speed > gauge.maxValue) {
            let newMax
            if (speed < 100) newMax = 100
            else if (speed < 250) newMax = 250
            else if (speed < 500) newMax = 500
            else if (speed < 1000) newMax = 1000
            else newMax = 5000

            if (newMax > gauge.maxValue) {
                const newLabels = [0, newMax * 0.2, newMax * 0.5, newMax * 0.8, newMax].map(l => Math.round(l))
                gauge.options.staticLabels.labels = newLabels
                gauge.options.staticZones = [
                    { strokeStyle: "#30B32D", min: 0, max: newMax * 0.4 },
                    { strokeStyle: "#FFDD00", min: newMax * 0.4, max: newMax * 0.8 },
                    { strokeStyle: "#F03E3E", min: newMax * 0.8, max: newMax }
                ]
                gauge.maxValue = newMax
            }
        }
    }

    function setSpeed(gauge, element, speed) {
        gauge.set(speed)
        element.textContent = speed.toFixed(0)
    }

    async function testPing() {
        statusText.textContent = 'Testing Ping...'
        const startTime = new Date().getTime()
        await fetch('/ping', { method: 'HEAD', cache: 'no-store' })
        const endTime = new Date().getTime()
        const pingTime = endTime - startTime
        pingElement.textContent = pingTime
        return pingTime
    }

    async function testDownload() {
        statusText.textContent = 'Testing Download Speed...'
        downloadWrapper.classList.add('active')
        let totalBytes = 0
        const startTime = new Date().getTime()
        maxDownloadSpeed = 0

        const download = async () => {
            while ((new Date().getTime() - startTime) < testDuration) {
                const response = await fetch('/download?nocache=' + new Date().getTime())
                const reader = response.body.getReader()

                while (true) {
                    const { done, value } = await reader.read()
                    if (done) break
                    totalBytes += value.length
                    const duration = (new Date().getTime() - startTime) / 1000
                    const speedMbps = (totalBytes * 8) / duration / 1024 / 1024
                    updateGaugeScale(downloadGauge, speedMbps)
                    if (speedMbps > maxDownloadSpeed) {
                        maxDownloadSpeed = speedMbps
                    }
                    setSpeed(downloadGauge, downloadSpeedElement, maxDownloadSpeed)
                }
            }
        }

        const downloads = Array(5).fill(null).map(download)
        await Promise.all(downloads)
    }

    async function testUpload() {
        statusText.textContent = 'Testing Upload Speed...'
        uploadWrapper.classList.add('active')
        let totalBytes = 0
        const startTime = new Date().getTime()
        const data = new Blob([new ArrayBuffer(1024 * 1024)], { type: 'application/octet-stream' }) // 1MB
        maxUploadSpeed = 0

        const upload = async () => {
            while ((new Date().getTime() - startTime) < testDuration) {
                await fetch('/upload', { method: 'POST', body: data })
                totalBytes += data.size
                const duration = (new Date().getTime() - startTime) / 1000
                const speedMbps = (totalBytes * 8) / duration / 1024 / 1024
                updateGaugeScale(uploadGauge, speedMbps)
                if (speedMbps > maxUploadSpeed) {
                    maxUploadSpeed = speedMbps
                }
                setSpeed(uploadGauge, uploadSpeedElement, maxUploadSpeed)
            }
        }

        const uploads = Array(5).fill(null).map(upload)
        await Promise.all(uploads)
    }

    function showResultsModal(pingTime) {
        maxDownloadResult.textContent = maxDownloadSpeed.toFixed(2)
        maxUploadResult.textContent = maxUploadSpeed.toFixed(2)
        pingResultModal.textContent = pingTime
        resultsModal.style.display = 'flex'
    }

    closeModalButton.addEventListener('click', () => {
        resultsModal.style.display = 'none'
    })

    startButton.addEventListener('click', async () => {
        startButton.disabled = true
        startButton.textContent = 'Testing...'
        pingElement.textContent = '-'
        setSpeed(downloadGauge, downloadSpeedElement, 0)
        setSpeed(uploadGauge, uploadSpeedElement, 0)
        downloadWrapper.classList.remove('active')
        uploadWrapper.classList.remove('active')

        try {
            const pingTime = await testPing()
            await testDownload()
            await testUpload()
            statusText.textContent = 'Test Complete!'
            showResultsModal(pingTime)
        } catch (error) {
            console.error('Speed test failed:', error)
            statusText.textContent = 'Error: Connection to server lost.'
            setSpeed(downloadGauge, downloadSpeedElement, 0)
            setSpeed(uploadGauge, uploadSpeedElement, 0)
        } finally {
            startButton.textContent = 'Test Again'
            startButton.disabled = false
        }
    })
})
