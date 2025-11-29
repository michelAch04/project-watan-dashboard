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
        طلبات دعم الفريق - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        @else
        طلب دعم فريق - {{ $request->request_number ?? '' }}
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

        .col+.col {
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

    <!-- Back/Close Button -->
    <button class="print-button no-print" onclick="window.history.back()" style="top: 10px; right: 10px; left: auto;">
        رجوع / Back
    </button>

    @if(isset($requests) && $requests->count() > 0)
    <!-- Monthly Export Header (shown on screen, hidden in print) -->
    <div class="monthly-header no-print">
        <div class="monthly-title">تقرير طلبات دعم الفريق الشهري</div>
        <div class="monthly-subtitle">
            {{ \Carbon\Carbon::create($year, $month, 1)->locale('ar')->translatedFormat('F Y') }}
        </div>
        <div class="monthly-subtitle">
            عدد الطلبات: {{ $requests->count() }} طلب
        </div>
    </div>

    @foreach($requests as $request)
    <div class="form-container">
        <!-- Header: طلب دعم فريق with request number and date -->
        <div class="header">
            <h1>طلب دعم فريق</h1>
            <div class="header-fields">
                <div class="right">
                    <strong>رقم الطلب</strong>
                    <span>{{ $request->request_number ?? '' }}</span>
                </div>
                <div class="left">
                    <strong>تاريخ الطلب</strong>
                    <span>{{ $request->created_at ? $request->created_at->format('d/m/Y') : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="form-section">
            <!-- Row 1: Name, City, Registration Number -->
            <div class="form-row">
                <span class="form-label">الاسم الثلاثي :</span>
                <span class="form-value large">{{ $request->teamSupportRequest->requester_full_name ?? '' }}</span>
                <span class="form-label">البلدة :</span>
                <span class="form-value medium">{{ $request->teamSupportRequest->pwMember->voter->city->name ?? '' }}</span>
                <span class="form-label">رقم السجل:</span>
                <span class="form-value small">{{ $request->teamSupportRequest->pwMember->voter->register_number ?? '' }}</span>
            </div>

            <!-- Row 2: Mother Name, Spouse Name -->
            <div class="form-row">
                <span class="form-label">إسم الأم الثلاثي :</span>
                <span class="form-value medium">{{ $request->teamSupportRequest->pwMember->mother_full_name ?? '' }}</span>
                <span class="form-label">أسم الزوج/ة :</span>
                <span class="form-value medium"></span>
            </div>

            <!-- Row 3: Profession, Marital Status, Children Count -->
            <div class="form-row">
                <span class="form-label">المهنة:</span>
                <span class="form-value medium"></span>
                <span class="form-label">الوضع العائلي:</span>
                <div class="checkbox-row" style="display: inline-flex;">
                    <div class="checkbox-item">
                        <span class="checkbox"></span>
                        <span>متأهل</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span>
                        <span>أعزب</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span>
                        <span>أرمل</span>
                    </div>
                </div>
                <span class="form-label">عدد الأولاد</span>
                <span style="font-size: 8pt;">في المدرسة</span>
                <span class="form-value small"></span>
                <span style="font-size: 8pt;">في الجامعة</span>
                <span class="form-value small"></span>
            </div>

            <!-- Row 4: Residential Address -->
            <div class="form-row">
                <span class="form-label">عنوان السكن:</span>
                <span class="form-value full">{{ $request->teamSupportRequest->pwMember->city->name ?? '' }}</span>
            </div>

            <!-- Row 5: Reference -->
            <div class="form-row">
                <span class="form-label">الهاتف :</span>
                <span class="form-value medium">{{ $request->teamSupportRequest->pwMember->phone ?? '' }}</span>
                <span class="form-label">المرجع</span>
                <span class="form-value large">{{ $request->referenceMember ? trim($request->referenceMember->first_name . ' ' . $request->referenceMember->father_name . ' ' . $request->referenceMember->last_name) : '' }}</span>
            </div>
        </div>

        <!-- Payment Types Section -->
        <div class="form-section">
            <div class="checkbox-row">
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'إجتماعية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'social'))) ? 'checked' : '' }}"></span>
                    <span>اجتماعية</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'استشفائية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'hospital'))) ? 'checked' : '' }}"></span>
                    <span>استشفائية</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'طبية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'medical'))) ? 'checked' : '' }}"></span>
                    <span>طبية</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'تربوية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'education'))) ? 'checked' : '' }}"></span>
                    <span>تربوية</span>
                </div>
            </div>
        </div>

        <!-- Three Column Section: Educational, Medical, Material -->
        <div class="three-col">
            <!-- Educational Column -->
            <div class="col">
                <div class="col-title">تربوية</div>
                <div class="col-field">
                    <label>المؤسسة التربوية :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>القسط السنوي :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>الحسومات :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>المتبقي:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field form-row">
                    <span style="font-size: 8pt; font-weight: 600;">المتأخرات:</span>
                    <span class="form-value small" style="width: 70px;"></span>
                    <span style="font-size: 8pt; font-weight: 600;">المدفوعات:</span>
                    <span class="form-value small" style="width: 70px;"></span>
                </div>
                <div class="col-field">
                    <label>ملاحظات :</label>
                </div>
            </div>

            <!-- Medical Column -->
            <div class="col">
                <div class="col-title">طبية</div>
                <div class="col-field">
                    <label>تكلفة العلاج:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>مدة العلاج :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>مستلزمات:</label>
                    <div class="col-input"></div>
                </div>
            </div>

            <!-- Material Column -->
            <div class="col">
                <div class="col-title">استشفائية</div>
                <div class="col-field">
                    <label>المجموع:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>الحسم:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>المتبقي:</label>
                    <div class="col-input"></div>
                </div>
            </div>
        </div>

        <!-- Payment Method Section -->
        <div class="form-section">
            <div class="checkbox-row">
                <div class="checkbox-item">
                    <span class="checkbox"></span>
                    <span>لمرة واحدة</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox"></span>
                    <span>شهرياً</span>
                </div>
                <div class="checkbox-item">
                    <span class="form-label">دفعات في</span>
                    <span class="form-value small"></span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox"></span>
                    <span>شيك</span>
                </div>
            </div>
        </div>

        <!-- Financial Section -->
        <div class="financial-section">
            <div class="form-row">
                <span class="form-label">إقتراح المساعدة</span>
                <span class="form-value full">{{ $request->teamSupportRequest->subtype ?? '' }}</span>
            </div>
            <div class="amount-row">
                <div class="amount-item">
                    <label>المبلغ</label>
                    <span class="amount-highlight">${{ isset($request->teamSupportRequest->amount) ? number_format($request->teamSupportRequest->amount, 0) : '' }}</span>
                </div>
                <div class="amount-item">
                    <label>المجموع:</label>
                    <span class="amount-highlight">${{ isset($request->teamSupportRequest->amount) ? number_format($request->teamSupportRequest->amount, 0) : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Approval and Disbursement Section -->
        <div class="approval-section">
            <div class="approval-row">
                <span class="form-label">الموافقة</span>
                <span class="signature-line"></span>
            </div>
            <div class="approval-row" style="margin-top: 8px;">
                <span class="form-label">تصرف في</span>
                <span class="form-value" style="min-width: 300px;"><!-- {{ isset($request->ready_date) && $request->ready_date ? \Carbon\Carbon::parse($request->ready_date)->format('d/m/Y') : '-----------------------------------' }} --></span>
            </div>
            <div class="approval-row">
                <span class="form-label">من</span>
                <span class="form-value medium"><!-- {{ isset($request->ready_date) && $request->ready_date && isset($request->budget) ? \Carbon\Carbon::parse($request->ready_date)->format('d/m/Y') : '---------------' }} --></span>
                <span class="form-label">الى</span>
                <span class="form-value medium">----------------------</span>
            </div>
            <div class="approval-row">
                <span class="form-label">بأسم</span>
                <span class="form-value" style="min-width: 300px;"><!-- {{ $request->teamSupportRequest->requester_full_name ?? ''  }} --> </span>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="notes-section">
            <div class="notes-label">ملاحظات :</div>
            <div class="notes-box">{{ $request->teamSupportRequest->notes ?? '' }}</div>
        </div>

        <!-- Office Footer -->
        <div class="approval-section" style="display: flex; gap: 0;">
            <div class="notes-section" style="flex: 1; border-left: 1px solid #000; padding: 4px;">
                <div class="notes-label" style="text-align: center;">مكتب الشؤون الإجتماعية</div>
                <div class="notes-box" style="min-height: 60px;"></div>
            </div>
            <div class="notes-section" style="flex: 1; border-left: 1px solid #000; padding: 4px;">
                <div class="notes-label" style="text-align: center;">مسؤول القطاع</div>
                <div class="notes-box" style="min-height: 60px;"></div>
            </div>
            <div class="notes-section" style="flex: 1; padding: 4px;">
                <div class="notes-label" style="text-align: center;">الموافقة</div>
                <div class="notes-box" style="min-height: 60px;"></div>
            </div>
        </div>
    </div>
    @endforeach
    @else
    <div class="form-container">
        <!-- Header: طلب دعم فريق with request number and date -->
        <div class="header">
            <h1>طلب دعم فريق</h1>
            <div class="header-fields">
                <div class="right">
                    <strong>رقم الطلب</strong>
                    <span>{{ $request->request_number ?? '' }}</span>
                </div>
                <div class="left">
                    <strong>تاريخ الطلب</strong>
                    <span>{{ $request->created_at ? $request->created_at->format('d/m/Y') : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="form-section">
            <!-- Row 1: Name, City, Registration Number -->
            <div class="form-row">
                <span class="form-label">الاسم الثلاثي :</span>
                <span class="form-value large">{{ $request->teamSupportRequest->requester_full_name ?? '' }}</span>
                <span class="form-label">البلدة :</span>
                <span class="form-value medium">{{ $request->teamSupportRequest->pwMember->city->name ?? '' }}</span>
                <span class="form-label">رقم السجل:</span>
                <span class="form-value small">{{ $request->teamSupportRequest->pwMember->register_number ?? '' }}</span>
            </div>

            <!-- Row 2: Mother Name, Spouse Name -->
            <div class="form-row">
                <span class="form-label">إسم الأم الثلاثي :</span>
                <span class="form-value medium">{{ $request->teamSupportRequest->pwMember->mother_full_name ?? '' }}</span>
                <span class="form-label">أسم الزوج/ة :</span>
                <span class="form-value medium"></span>
            </div>

            <!-- Row 3: Profession, Marital Status, Children Count -->
            <div class="form-row">
                <span class="form-label">المهنة:</span>
                <span class="form-value medium"></span>
                <span class="form-label">الوضع العائلي:</span>
                <div class="checkbox-row" style="display: inline-flex;">
                    <div class="checkbox-item">
                        <span class="checkbox"></span>
                        <span>متأهل</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span>
                        <span>أعزب</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span>
                        <span>أرمل</span>
                    </div>
                </div>
                <span class="form-label">عدد الأولاد</span>
                <span style="font-size: 8pt;">في المدرسة</span>
                <span class="form-value small"></span>
                <span style="font-size: 8pt;">في الجامعة</span>
                <span class="form-value small"></span>
            </div>

            <!-- Row 4: Residential Address -->
            <div class="form-row">
                <span class="form-label">عنوان السكن:</span>
                <span class="form-value full">{{ $request->teamSupportRequest->pwMember->city->name ?? '' }}</span>
            </div>

            <!-- Row 5: Reference -->
            <div class="form-row">
                <span class="form-label">الهاتف :</span>
                <span class="form-value medium">{{ $request->teamSupportRequest->pwMember->phone ?? '' }}</span>
                <span class="form-label">المرجع</span>
                <span class="form-value large">{{ $request->referenceMember ? trim($request->referenceMember->first_name . ' ' . $request->referenceMember->father_name . ' ' . $request->referenceMember->last_name) : '' }}</span>
            </div>
        </div>

        <!-- Payment Types Section -->
        <div class="form-section">
            <div class="checkbox-row">
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'إجتماعية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'social'))) ? 'checked' : '' }}"></span>
                    <span>اجتماعية</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'استشفائية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'hospital'))) ? 'checked' : '' }}"></span>
                    <span>استشفائية</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'طبية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'medical'))) ? 'checked' : '' }}"></span>
                    <span>طبية</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox {{ (isset($request->teamSupportRequest->subtype) && (str_contains(mb_strtolower($request->teamSupportRequest->subtype), 'تربوية') || str_contains(strtolower($request->teamSupportRequest->subtype), 'education'))) ? 'checked' : '' }}"></span>
                    <span>تربوية</span>
                </div>
            </div>
        </div>

        <!-- Three Column Section: Educational, Medical, Material -->
        <div class="three-col">
            <!-- Educational Column -->
            <div class="col">
                <div class="col-title">تربوية</div>
                <div class="col-field">
                    <label>المؤسسة التربوية :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>القسط السنوي :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>الحسومات :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>المتبقي:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field form-row">
                    <span style="font-size: 8pt; font-weight: 600;">المتأخرات:</span>
                    <span class="form-value small" style="width: 70px;"></span>
                    <span style="font-size: 8pt; font-weight: 600;">المدفوعات:</span>
                    <span class="form-value small" style="width: 70px;"></span>
                </div>
                <div class="col-field">
                    <label>ملاحظات :</label>
                </div>
            </div>

            <!-- Medical Column -->
            <div class="col">
                <div class="col-title">طبية</div>
                <div class="col-field">
                    <label>تكلفة العلاج:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>مدة العلاج :</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>مستلزمات:</label>
                    <div class="col-input"></div>
                </div>
            </div>

            <!-- Material Column -->
            <div class="col">
                <div class="col-title">استشفائية</div>
                <div class="col-field">
                    <label>المجموع:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>الحسم:</label>
                    <div class="col-input"></div>
                </div>
                <div class="col-field">
                    <label>المتبقي:</label>
                    <div class="col-input"></div>
                </div>
            </div>
        </div>

        <!-- Payment Method Section -->
        <div class="form-section">
            <div class="checkbox-row">
                <div class="checkbox-item">
                    <span class="checkbox"></span>
                    <span>لمرة واحدة</span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox"></span>
                    <span>شهرياً</span>
                </div>
                <div class="checkbox-item">
                    <span class="form-label">دفعات في</span>
                    <span class="form-value small"></span>
                </div>
                <div class="checkbox-item">
                    <span class="checkbox"></span>
                    <span>شيك</span>
                </div>
            </div>
        </div>

        <!-- Financial Section -->
        <div class="financial-section">
            <div class="form-row">
                <span class="form-label">إقتراح المساعدة</span>
                <span class="form-value full">{{ $request->teamSupportRequest->subtype ?? '' }}</span>
            </div>
            <div class="amount-row">
                <div class="amount-item">
                    <label>المبلغ</label>
                    <span class="amount-highlight">${{ isset($request->teamSupportRequest->amount) ? number_format($request->teamSupportRequest->amount, 0) : '' }}</span>
                </div>
                <div class="amount-item">
                    <label>المجموع:</label>
                    <span class="amount-highlight">${{ isset($request->teamSupportRequest->amount) ? number_format($request->teamSupportRequest->amount, 0) : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Approval and Disbursement Section -->
        <div class="approval-section">
            <div class="approval-row">
                <span class="form-label">الموافقة</span>
                <span class="signature-line"></span>
            </div>
            <div class="approval-row" style="margin-top: 8px;">
                <span class="form-label">تصرف في</span>
                <span class="form-value" style="min-width: 300px;"><!-- {{ isset($request->ready_date) && $request->ready_date ? \Carbon\Carbon::parse($request->ready_date)->format('d/m/Y') : '-----------------------------------' }} --></span>
            </div>
            <div class="approval-row">
                <span class="form-label">من</span>
                <span class="form-value medium"><!-- {{ isset($request->ready_date) && $request->ready_date && isset($request->budget) ? \Carbon\Carbon::parse($request->ready_date)->format('d/m/Y') : '---------------' }} --></span>
                <span class="form-label">الى</span>
                <span class="form-value medium">----------------------</span>
            </div>
            <div class="approval-row">
                <span class="form-label">بأسم</span>
                <span class="form-value" style="min-width: 300px;"><!-- {{ $request->teamSupportRequest->requester_full_name ?? ''  }} --> </span>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="notes-section">
            <div class="notes-label">ملاحظات :</div>
            <div class="notes-box">{{ $request->teamSupportRequest->notes ?? '' }}</div>
        </div>

        <!-- Office Footer -->
        <div class="approval-section" style="display: flex; gap: 0;">
            <div class="notes-section" style="flex: 1; border-left: 1px solid #000; padding: 4px;">
                <div class="notes-label" style="text-align: center;">مكتب الشؤون الإجتماعية</div>
                <div class="notes-box" style="min-height: 60px;"></div>
            </div>
            <div class="notes-section" style="flex: 1; border-left: 1px solid #000; padding: 4px;">
                <div class="notes-label" style="text-align: center;">مسؤول القطاع</div>
                <div class="notes-box" style="min-height: 60px;"></div>
            </div>
            <div class="notes-section" style="flex: 1; padding: 4px;">
                <div class="notes-label" style="text-align: center;">الموافقة</div>
                <div class="notes-box" style="min-height: 60px;"></div>
            </div>
        </div>
    </div>
    @endif

</body>
</html>