from gps_client import GpsClient
import json

"""
Example: Historical Route Processor

This script retrieves path data for a specific device and calculates total distance.
"""

def main():
    config = {
        'base_url': 'http://your-platform.com/api/v2',
        'api_key': 'your_api_key',
        'api_secret': 'your_api_secret'
    }

    client = GpsClient(config)
    DEVICE_ID = 1 # Replace with your actual device ID

    try:
        print(f"--- Processing Route for Device #{DEVICE_ID} ---")

        # 1. Fetch history for the last 24 hours
        params = {
            'limit': 100,
            'sort': '-created_at' # Newest first
        }
        
        points = client.get_device_history(DEVICE_ID, params)

        if not points:
            print("No data found for the given period.")
            return

        print(f"Found {len(points)} GPS points.")

        # 2. Simple coordinate listing
        for i, pt in enumerate(points[:5]): # Show first 5
            print(f"[{i+1}] Lat: {pt['latitude']}, Lng: {pt['longitude']} @ {pt['created_at']}")

        if len(points) > 5:
            print("...")

    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    main()
