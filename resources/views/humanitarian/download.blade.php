<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب إنساني - {{ $request->request_number }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            color: #000;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #931335;
            padding-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #931335;
            margin-bottom: 10px;
        }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .request-number {
            font-size: 16px;
            color: #622032;
            margin: 10px 0;
        }
        
        .section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #931335;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f0e2;
        }
        
        .field {
            margin: 12px 0;
            display: flex;
            padding: 8px 0;
        }
        
        .field-label {
            font-weight: bold;
            min-width: 150px;
            color: #622032;
        }
        
        .field-value {
            flex: 1;
            color: #000;
        }
        
        .amount-box {
            text-align: center;
            padding: 20px;
            background: #fef9de;
            border: 2px solid #931335;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .amount-label {
            font-size: 14px;
            color: #622032;
            margin-bottom: 10px;
        }
        
        .amount-value {
            font-size: 32px;
            font-weight: bold;
            color: #931335;
        }
        
        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #ddd;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 10px;
            text-align: center;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #931335;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #622032;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
        }
        
        .notes-box {
            background: #fcf7f8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-right: 4px solid #931335;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">طباعة / Print</button>

    <div class="header">
        <div class="logo">مشروع الوطن - Project Watan</div>
        <div class="title">طلب مساعدة إنسانية</div>
        <div class="title" style="font-size: 16px; color: #622032;">Humanitarian Aid Request</div>
        <div class="request-number">رقم الطلب: {{ $request->request_number }}</div>
        <div class="request-number">تاريخ: {{ $request->request_date->format('Y/m/d') }}</div>
    </div>

    <!-- Requester Information -->
    <div class="section">
        <div class="section-title">معلومات الطالب / Requester Information</div>
        
        <div class="field">
            <span class="field-label">الإسم الكامل:</span>
            <span class="field-value">{{ $request->requester_full_name }}</span>
        </div>
        
        <div class="field">
            <span class="field-label">البلدة:</span>
            <span class="field-value">{{ $request->requesterCity->name_ar }}</span>
        </div>
        
        @if($request->requester_ro_number)
        <div class="field">
            <span class="field-label">رقم السجل:</span>
            <span class="field-value">{{ $request->requester_ro_number }}</span>
        </div>
        @endif
        
        @if($request->requester_phone)
        <div class="field">
            <span class="field-label">رقم الهاتف:</span>
            <span class="field-value">{{ $request->requester_phone }}</span>
        </div>
        @endif
    </div>

    <!-- Request Details -->
    <div class="section">
        <div class="section-title">تفاصيل الطلب / Request Details</div>
        
        <div class="field">
            <span class="field-label">نوع المساعدة:</span>
            <span class="field-value">{{ $request->subtype }}</span>
        </div>
        
        @if($request->referenceMember)
        <div class="field">
            <span class="field-label">المرجع:</span>
            <span class="field-value">{{ $request->referenceMember->name }} ({{ $request->referenceMember->phone }})</span>
        </div>
        @endif
        
        @if($request->notes)
        <div class="field">
            <span class="field-label">ملاحظات:</span>
            <span class="field-value">
                <div class="notes-box">{{ $request->notes }}</div>
            </span>
        </div>
        @endif
    </div>

    <!-- Amount -->
    <div class="amount-box">
        <div class="amount-label">المبلغ المطلوب / Requested Amount</div>
        <div class="amount-value">${{ number_format($request->amount, 2) }} USD</div>
    </div>

    <!-- Workflow Information -->
    <div class="section">
        <div class="section-title">معلومات المعاملة / Transaction Information</div>
        
        <div class="field">
            <span class="field-label">مقدم الطلب:</span>
            <span class="field-value">{{ $request->sender->name }}</span>
        </div>
        
        <div class="field">
            <span class="field-label">الحالة:</span>
            <span class="field-value">{{ $request->requestStatus->name_ar }}</span>
        </div>
        
        <div class="field">
            <span class="field-label">تاريخ الموافقة النهائية:</span>
            <span class="field-value">{{ $request->updated_at->format('Y/m/d') }}</span>
        </div>
    </div>

    <!-- Footer with Signatures -->
    <div class="footer">
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>توقيع المستفيد</strong><br>
                    Beneficiary Signature
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line">
                    <strong>توقيع المسؤول</strong><br>
                    Official Signature
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px; color: #622032; font-size: 12px;">
            <p>مشروع الوطن - خدمة المجتمع والإنسانية</p>
            <p>Project Watan - Serving Community & Humanity</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>