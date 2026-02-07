import requests
import hashlib
import hmac
import json
import time

class GpsClient:
    def __init__(self, base_url, api_key, api_secret):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.api_secret = api_secret
        self.session = requests.Session()
        self.session.headers.update({
            'X-API-Key': self.api_key,
            'X-API-Secret': self.api_secret,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        })

    def get_devices(self, params=None):
        """Get all devices."""
        return self._request('GET', '/devices', params=params)

    def get_history(self, device_id, params=None):
        """Get tracking history for a device."""
        return self._request('GET', f'/gps-data/{device_id}/history', params=params)

    def post_gps_data(self, data):
        """Post new GPS data."""
        return self._request('POST', '/gps-data', json=data)

    def _request(self, method, endpoint, **kwargs):
        url = f"{self.base_url}/api/v2{endpoint}"
        response = self.session.request(method, url, **kwargs)
        
        if response.status_code >= 400:
            try:
                error_msg = response.json().get('error', {}).get('message', 'API Request Failed')
            except:
                error_msg = response.text
            raise Exception(f"Error {response.status_code}: {error_msg}")
            
        return response.json()

    def verify_webhook(self, payload, signature):
        """Verify Webhook Signature."""
        message = json.dumps(payload, separators=(',', ':'))
        expected_signature = hmac.new(
            self.api_secret.encode('utf-8'),
            message.encode('utf-8'),
            hashlib.sha256
        ).hexdigest()
        
        return hmac.compare_digest(expected_signature, signature)
