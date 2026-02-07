const GpsClient = require('../GpsClient');

/**
 * Example: Real-time Alert Listener (Webhook Simulation/Handling)
 * 
 * This script shows how you might process a webhook payload in Node.js.
 */

const config = {
    baseUrl: 'http://your-platform.com/api/v2',
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    webhookSecret: 'your_webhook_secret' // Found in Portal -> Webhook Details
};

const client = new GpsClient(config);

// Mock Webhook Receiver
function handleWebhook(headers, payload) {
    const signature = headers['x-gps-signature'];

    // 1. Verify integrity
    if (!client.verifyWebhook(payload, signature)) {
        console.error('❌ Invalid signature! Potential tampering.');
        return;
    }

    const data = JSON.parse(payload);
    console.log(`\n🔔 Webhook Received: ${data.event}`);

    // 2. Handle specific events
    switch (data.event) {
        case 'alert.created':
            console.log(`⚠️  VIOLATION: ${data.data.type} detected for ${data.data.device_name}`);
            console.log(`   Severity: ${data.data.severity.toUpperCase()}`);
            break;

        case 'device.online':
            console.log(`🟢 Device Connected: ${data.data.name}`);
            break;

        case 'geofence.entered':
            console.log(`🚧 Geofence Alert: ${data.data.device_name} entered ${data.data.geofence_name}`);
            break;
    }
}

// Example usage
const mockPayload = JSON.stringify({
    event: 'alert.created',
    data: {
        type: 'speeding',
        severity: 'high',
        device_name: 'Truck-01',
        speed: 110,
        limit: 90
    }
});

// Assuming your framework (Express/Fastify) provides headers
const mockHeaders = {
    'x-gps-signature': client.signPayload(mockPayload) // For simulation
};

handleWebhook(mockHeaders, mockPayload);
