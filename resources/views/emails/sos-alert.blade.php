<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .alert-box { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #dc2626; }
        .map-link { display: inline-block; background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
        .footer { text-align: center; padding: 15px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 SOS EMERGENCY ALERT</h1>
        </div>
        <div class="content">
            <p>Dear {{ $name }},</p>
            <p><strong>An SOS emergency alert has been triggered!</strong></p>
            
            <div class="alert-box">
                <h3>Alert Details:</h3>
                <p><strong>Vehicle:</strong> {{ $alert->device->vehicle_no ?? $alert->device->name }}</p>
                <p><strong>Time:</strong> {{ $alert->triggered_at->format('d M Y, h:i A') }}</p>
                <p><strong>Speed:</strong> {{ $alert->speed }} km/h</p>
                @if($alert->latitude && $alert->longitude)
                    <p><strong>Location:</strong> {{ $alert->latitude }}, {{ $alert->longitude }}</p>
                    <a href="https://maps.google.com/?q={{ $alert->latitude }},{{ $alert->longitude }}" class="map-link" target="_blank">
                        📍 View on Google Maps
                    </a>
                @else
                    <p><strong>Location:</strong> Not available</p>
                @endif
            </div>

            <p><strong>Action Required:</strong> Please check on the driver immediately and take necessary action.</p>
        </div>
        <div class="footer">
            <p>This is an automated emergency alert from your GPS Tracking System.</p>
            <p>Do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
