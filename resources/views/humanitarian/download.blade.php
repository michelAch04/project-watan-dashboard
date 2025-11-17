<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8, user-scalable=yes">
    <!-- Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Optional: Theme color for browser UI -->
    <meta name="theme-color" content="#4F46E5">

    <!-- Apple touch icon (if you have one in your public folder) -->
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- Enable standalone mode on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">


    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>
        @if(isset($requests))
        طلبات إنسانية - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        @else
        طلب مساعدة - {{ $request->request_number ?? '' }}
        @endif
    </title>
    <style>
        @page {
            margin: 0.8cm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 9pt;
            line-height: 1.1;
            color: #000;
            background: #fff;
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 0;
        }

        /* Container for each request */
        .form-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto 10px auto;
            padding: 8px;
            border: 1.5px solid #000;
            background: #fff;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .form-container:last-child {
            page-break-after: auto;
            margin-bottom: 0;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #000;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            line-height: 1;
        }

        .header-fields {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 9pt;
            line-height: 1.2;
        }

        .header-fields>div {
            flex: 1;
        }

        .header-fields .right {
            text-align: right;
        }

        .header-fields .left {
            text-align: left;
        }

        .header-fields strong {
            font-weight: bold;
            margin-left: 4px;
        }

        /* Form sections */
        .form-section {
            border: 1px solid #000;
            padding: 4px;
            margin: 4px 0;
        }

        /* Form rows */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            width: 100%;
            margin: 2px 0;
            font-size: 8.5pt;
            line-height: 1.2;
        }

        .form-row>* {
            vertical-align: middle;
            padding: 1px 3px;
        }

        .form-label {
            font-weight: bold;
            white-space: nowrap;
            padding-left: 4px;
        }

        .form-value {
            border-bottom: 1px solid #000;
            min-height: 16px;
            padding: 1px 3px;
            line-height: 1.3;
        }

        .form-value.small {
            width: 50px;
            flex-shrink: 0;
        }

        .form-value.medium {
            width: 120px;
            flex-shrink: 0;
        }

        .form-value.large {
            width: 180px;
            flex-shrink: 0;
        }

        .form-value.full {
            width: 100%;
            flex: 1;
        }

        /* Checkbox styling */
        .checkbox-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 2px 0;
            align-items: center;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
            font-size: 8.5pt;
        }

        .checkbox {
            width: 12px;
            height: 12px;
            border: 1.5px solid #000;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-size: 9pt;
            font-weight: bold;
            flex-shrink: 0;
        }

        .checkbox.checked::before {
            content: 'X';
        }

        /* Three column layout for assistance details */
        .three-col {
            display: flex;
            width: 100%;
            margin: 4px 0;
            gap: 0;
        }

        .col {
            flex: 1;
            border: 1px solid #000;
            padding: 4px;
            font-size: 8pt;
        }

        .col + .col {
            border-right: none;
        }

        .col-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 3px;
            text-decoration: underline;
            line-height: 1.2;
        }

        .col-field {
            margin: 2px 0;
        }

        .col-field label {
            font-weight: bold;
            font-size: 7.5pt;
            display: block;
            margin-bottom: 1px;
            line-height: 1.2;
        }

        .col-input {
            border-bottom: 1px solid #000;
            min-height: 14px;
            padding: 0px 2px;
            font-size: 8pt;
            line-height: 1.3;
        }

        /* Financial section */
        .financial-section {
            border: 1px solid #000;
            padding: 4px;
            margin: 4px 0;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
        }

        .amount-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 9pt;
        }

        .amount-item label {
            font-weight: bold;
        }

        .amount-highlight {
            font-weight: bold;
            font-size: 11pt;
            background: #f0f0f0;
            padding: 2px 8px;
            border: 1px solid #000;
            min-width: 70px;
            text-align: center;
            line-height: 1.3;
        }

        /* Approval section */
        .approval-section {
            border: 1.5px solid #000;
            padding: 4px;
            margin: 4px 0;
        }

        .approval-row {
            margin: 3px 0;
            font-size: 8.5pt;
            line-height: 1.2;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            min-width: 180px;
            display: inline-block;
            height: 18px;
        }

        /* Notes section */
        .notes-section {
            margin: 4px 0;
        }

        .notes-label {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 8.5pt;
            line-height: 1.2;
        }

        .notes-box {
            border: 1px solid #000;
            min-height: 40px;
            padding: 4px;
            font-size: 8pt;
            line-height: 1.3;
        }

        /* Footer */
        .office-footer {
            text-align: center;
            margin-top: 6px;
            font-weight: bold;
            font-size: 9pt;
        }

        /* Print button */
        .print-button {
            position: fixed;
            top: 10px;
            left: 10px;
            padding: 8px 16px;
            background: #000;
            color: #fff;
            border: 2px solid #000;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11pt;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .print-button:hover {
            background: #333;
        }

        /* Monthly report header */
        .monthly-header {
            text-align: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #000;
            color: #fff;
            border-radius: 0;
        }

        .monthly-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .monthly-subtitle {
            font-size: 11pt;
            margin-top: 4px;
            line-height: 1.2;
        }

        /* Print styles */
        @media print {
            @page {
                margin: 0.8cm;
                size: A4 portrait;
            }

            body {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .print-button {
                display: none !important;
            }

            .monthly-header {
                display: none !important;
            }

            .form-container {
                page-break-after: always;
                page-break-inside: avoid;
                margin: 0 !important;
                padding: 8px !important;
                border: 1.5px solid #000 !important;
            }

            .form-container:last-child {
                page-break-after: auto;
            }

            /* Ensure no extra margins or paddings in print */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Responsive for mobile */
        @media screen and (max-width: 768px) {
            body {
                width: 100vw;
                max-width: 100vw;
                padding: 0 5px;
            }

            .form-container {
                max-width: 100%;
                width: 100%;
                margin: 0 auto 10px auto;
                font-size: 8pt;
            }

            .monthly-header {
                margin-bottom: 10px;
            }
        }

        /* Tablet responsive */
        @media screen and (min-width: 769px) and (max-width: 1024px) {
            body {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Print Button -->
    <button class="print-button no-print" onclick="window.print()">
        طباعة / Print
    </button>

    @if(isset($requests) && $requests->count() > 0)
    <!-- Monthly Export Header (shown on screen, hidden in print) -->
    <div class="monthly-header no-print">
        <div class="monthly-title">تقرير الطلبات الإنسانية الشهري</div>
        <div class="monthly-subtitle">
            {{ \Carbon\Carbon::create($year, $month, 1)->locale('ar')->translatedFormat('F Y') }}
        </div>
        <div class="monthly-subtitle">
            عدد الطلبات: {{ $requests->count() }} طلب
        </div>
    </div>

    @foreach($requests as $request)
    @include('layouts.partials.form-template', ['request' => $request])
    @endforeach
    @else
    @include('layouts.partials.form-template', ['request' => $request])
    @endif

</body>

</html>