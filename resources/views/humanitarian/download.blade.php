<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(isset($requests))
            طلبات إنسانية - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        @else
            طلب إنساني - {{ $request->request_number }}
        @endif
    </title>
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

        .page-break {
            page-break-after: always;
        }

        .monthly-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #931335 0%, #622032 100%);
            color: white;
            border-radius: 10px;
        }

        .monthly-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .monthly-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        .request-card {
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 3px dashed #ddd;
        }

        .request-card:last-child {
            border-bottom: none;
        }

        @media print {
            .request-card {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">طباعة / Print</button>

    @if(isset($requests))
        <!-- Multiple Requests (Monthly Export) -->
        <div class="monthly-header">
            <div class="monthly-title">تقرير الطلبات الإنسانية الشهري</div>
            <div class="monthly-title" style="font-size: 20px;">Monthly Humanitarian Requests Report</div>
            <div class="monthly-subtitle">
                {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} /
                {{ \Carbon\Carbon::create($year, $month, 1)->locale('ar')->translatedFormat('F Y') }}
            </div>
            <div class="monthly-subtitle" style="margin-top: 10px;">
                عدد الطلبات: {{ $requests->count() }} طلب
            </div>
        </div>

        @foreach($requests as $index => $request)
        <div class="request-card {{ $index > 0 && $index % 2 == 0 ? 'page-break' : '' }}">
            @include('humanitarian.partials.download-single-request', ['request' => $request, 'showHeader' => false])
        </div>
        @endforeach

    @else
        <!-- Single Request -->
        @include('humanitarian.partials.download-single-request', ['request' => $request, 'showHeader' => true])
    @endif

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>