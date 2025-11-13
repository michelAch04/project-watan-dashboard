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
            margin: 1.2cm;
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
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
            background: #fff;
            width: 210mm;
            margin: 0 auto;
        }

        /* Container for each request */
        .form-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto 20px auto;
            padding: 12px;
            border: 2px solid #000;
            background: #fff;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .form-container:last-child {
            page-break-after: auto;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 8px 0;
        }

        .header-fields {
            display: table;
            width: 100%;
            font-size: 10pt;
        }

        .header-fields>div {
            display: table-cell;
            width: 50%;
            padding: 2px 0;
        }

        .header-fields .right {
            text-align: right;
        }

        .header-fields .left {
            text-align: left;
        }

        .header-fields strong {
            font-weight: bold;
            margin-left: 5px;
        }

        /* Form sections */
        .form-section {
            border: 1px solid #000;
            padding: 6px;
            margin: 6px 0;
        }

        /* Form rows */
        .form-row {
            display: table;
            width: 100%;
            margin: 3px 0;
            font-size: 9pt;
        }

        .form-row>* {
            display: table-cell;
            vertical-align: middle;
            padding: 2px 4px;
        }

        .form-label {
            font-weight: bold;
            white-space: nowrap;
            padding-left: 8px;
            column-width: auto;
        }

        .form-value {
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 2px 4px;
        }

        .form-value.small {
            width: 60px;
        }

        .form-value.medium {
            width: 140px;
        }

        .form-value.large {
            width: 200px;
        }

        .form-value.full {
            width: 100%;
        }

        /* Checkbox styling */
        .checkbox-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 4px 0;
            align-items: center;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .checkbox {
            width: 14px;
            height: 14px;
            border: 2px solid #000;
            display: inline-block;
            text-align: center;
            line-height: 12px;
            font-size: 10pt;
            font-weight: bold;
            flex-shrink: 0;
        }

        .checkbox.checked::before {
            content: 'X';
        }

        /* Three column layout for assistance details */
        .three-col {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .col {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 9pt;
        }

        .col-title {
            font-weight: bold;
            font-size: 9.5pt;
            margin-bottom: 6px;
            text-decoration: underline;
        }

        .col-field {
            margin: 4px 0;
        }

        .col-field label {
            font-weight: bold;
            font-size: 8.5pt;
            display: block;
            margin-bottom: 2px;
        }

        .col-input {
            border-bottom: 1px solid #000;
            min-height: 16px;
            padding: 1px 2px;
            font-size: 9pt;
        }

        /* Financial section */
        .financial-section {
            border: 1px solid #000;
            padding: 8px;
            margin: 6px 0;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .amount-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .amount-item label {
            font-weight: bold;
        }

        .amount-highlight {
            font-weight: bold;
            font-size: 13pt;
            background: #f0f0f0;
            padding: 3px 10px;
            border: 1px solid #000;
            min-width: 80px;
            text-align: center;
        }

        /* Approval section */
        .approval-section {
            border: 2px solid #000;
            padding: 8px;
            margin: 8px 0;
        }

        .approval-row {
            margin: 5px 0;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            min-width: 200px;
            display: inline-block;
            height: 20px;
        }

        /* Notes section */
        .notes-section {
            margin: 8px 0;
        }

        .notes-label {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .notes-box {
            border: 1px solid #000;
            min-height: 50px;
            padding: 6px;
            font-size: 9pt;
        }

        /* Footer */
        .office-footer {
            text-align: center;
            margin-top: 12px;
            font-weight: bold;
            font-size: 11pt;
        }

        /* Print button */
        .print-button {
            position: fixed;
            top: 15px;
            left: 15px;
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: 2px solid #000;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12pt;
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
            margin-bottom: 20px;
            padding: 15px;
            background: #000;
            color: #fff;
            border-radius: 0;
            page-break-after: always;
        }

        .monthly-title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .monthly-subtitle {
            font-size: 12pt;
            margin-top: 6px;
        }

        /* Print styles */
        @media print {
            body {
                width: 100%;
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
                margin: 0;
                border: 2px solid #000;
            }

            .form-container:last-child {
                page-break-after: auto;
            }
        }

        /* Responsive for mobile */
        @media screen and (max-width: 768px) {
            body {
                width: 100%;
                zoom: 0.6;
            }

            .form-container {
                max-width: 100%;
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