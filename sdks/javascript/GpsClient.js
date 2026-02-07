const axios = require('axios');
const crypto = require('crypto');

class GpsClient {
    constructor(baseUrl, apiKey, apiSecret) {
        this.client = axios.create({
            baseURL: `${baseUrl.replace(/\/$/, '')}/api/v2`,
            headers: {
                'X-API-Key': apiKey,
                'X-API-Secret': apiSecret,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        this.apiSecret = apiSecret;
    }

    /**
     * Get all devices.
     */
    async getDevices(params = {}) {
        const response = await this.client.get('/devices', { params });
        return response.data;
    }

    /**
     * Get tracking history for a device.
     */
    async getHistory(deviceId, params = {}) {
        const response = await this.client.get(`/gps-data/${deviceId}/history`, { params });
        return response.data;
    }

    /**
     * Post new GPS data.
     */
    async postGpsData(data) {
        const response = await this.client.post('/gps-data', data);
        return response.data;
    }

    /**
     * Verify Webhook Signature.
     */
    verifyWebhook(payload, signature) {
        const hmac = crypto.createHmac('sha256', this.apiSecret);
        const expectedSignature = hmac.update(JSON.stringify(payload)).digest('hex');
        return crypto.timingSafeEqual(Buffer.from(expectedSignature), Buffer.from(signature));
    }
}

module.exports = GpsClient;
