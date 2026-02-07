<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .info-box {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
        }
        .value {
            color: #111827;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .attachment-notice {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Scheduled Report Ready</h1>
    </div>
    
    <div class="content">
        <p>Hello,</p>
        
        <p>Your scheduled report has been generated and is ready for review.</p>
        
        <div class="info-box">
            <div class="info-row">
                <span class="label">Report Name:</span>
                <span class="value">{{ $reportName }}</span>
            </div>
            <div class="info-row">
                <span class="label">Report Type:</span>
                <span class="value">{{ $reportType }}</span>
            </div>
            <div class="info-row">
                <span class="label">Records Found:</span>
                <span class="value">{{ number_format($recordCount) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Generated:</span>
                <span class="value">{{ $generatedAt }}</span>
            </div>
            <div class="info-row">
                <span class="label">Format:</span>
                <span class="value">{{ $format }}</span>
            </div>
        </div>
        
        <div class="attachment-notice">
            <strong>📎 Attachment Included</strong>
            <p style="margin: 5px 0 0 0;">The report file is attached to this email. You can download it directly from your email client.</p>
        </div>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact support.</p>
        
        <p>Best regards,<br>
        <strong>GPS Fleet Management Team</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated message from your GPS Fleet Management System.</p>
        <p>&copy; {{ date('Y') }} GPS Fleet Management. All rights reserved.</p>
    </div>
</body>
</html>
