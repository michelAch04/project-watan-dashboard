<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب مساعدة - مرافق عامة</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* Header Section */
        .header {
            text-align: center;
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 20pt;
            font-weight: bold;
            margin: 4px 0;
        }

        .header h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 2px 0;
        }

        .header .subtitle {
            font-size: 12pt;
            margin: 2px 0;
        }

        .header-row {
            display: table;
            width: 100%;
            border-top: 2px solid #000;
            margin-top: 8px;
            padding-top: 6px;
        }

        .header-cell {
            display: table-cell;
            padding: 4px 8px;
            vertical-align: middle;
        }

        .header-cell.left {
            width: 35%;
            text-align: right;
        }

        .header-cell.center {
            width: 30%;
            text-align: center;
            border-right: 2px solid #000;
            border-left: 2px solid #000;
        }

        .header-cell.right {
            width: 35%;
            text-align: left;
        }

        .input-box {
            display: inline-block;
            min-width: 120px;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
        }

        /* Form Body */
        .form-section {
            border: 2px solid #000;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .form-row {
            display: flex;
            border-bottom: 2px solid #000;
        }

        .form-row:last-child {
            border-bottom: none;
        }

        .form-cell {
            padding: 8px;
            border-left: 2px solid #000;
        }

        .form-cell:last-child {
            border-left: none;
        }

        .form-label {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .form-value {
            min-height: 20px;
        }

        .two-col-custom {
            font-size: 25px;
        }

        .two-col .form-cell:first-child {
            width: 50%;
        }

        .two-col .form-cell:last-child {
            width: 50%;
        }

        .three-col .form-cell {
            flex: 1;
        }

        /* Description Box */
        .description-box {
            min-height: 120px;
            padding: 10px;
        }

        /* Payment Section */
        .payment-section {
            padding: 10px;
        }

        .checkbox-group {
            margin: 8px 0;
        }

        .checkbox-item {
            display: inline-block;
            margin-left: 20px;
        }

        .checkbox {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.5px solid #000;
            margin-left: 6px;
            vertical-align: middle;
        }

        .checkbox.checked::after {
            content: '✓';
            display: block;
            text-align: center;
            line-height: 14px;
            font-weight: bold;
        }

        .amount-box {
            min-width: 150px;
            min-height: 60px;
            border: 2px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
        }

        /* Footer Section */
        .footer-row {
            display: flex;
            text-align: center;
            border-top: 2px solid #000;
        }

        .footer-cell {
            flex: 1;
            padding: 20px 10px;
            border-left: 2px solid #000;
            min-height: 60px;
        }

        .footer-cell:last-child {
            border-left: none;
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

        @media print {
            .print-button {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .page {
                margin: 0;
                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    <!-- Print Button -->
    <button class="print-button" onclick="window.print()">
        طباعة / Print
    </button>

    @if(isset($request))
    <!-- Single Request -->
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>طلب مساعدة</h1>
            <h2>مرافق عامة</h2>
            <p class="subtitle">مكتب الشؤون الاجتماعية والمساعدات</p>

            <div class="header-row">
                <div class="header-cell left">
                    رقم الطلب: <span class="input-box">{{ $request->request_number }}</span>
                </div>
                <div class="header-cell right">
                    تاريخ الطلب:<span class="input-box">{{ $request->request_date->format('d-m-Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="form-section">
            <!-- City Row -->
            <div class="form-row two-col">
                <div class="form-cell">
                    <div class="form-label">البلدة:</div>
                    <div class="form-value">{{ $request->publicRequest->city->name_ar ?? $request->publicRequest->city->name }}</div>
                </div>
                <div class="form-cell" style="min-height: 80px;">
                    <div class="form-label">اقتراح منسق البلدة</div>
                </div>
            </div>

            <!-- Requester Info Row -->
            <div class="form-row three-col">
                <div class="form-cell">
                    <div class="form-label">المرجع:</div>
                    <div class="form-value">{{ $request->referenceMember ? trim($request->referenceMember->first_name . ' ' . $request->referenceMember->father_name . ' ' . $request->referenceMember->last_name) : '' }}</div>
                </div>
                <div class="form-cell">
                    <div class="form-label">هاتف:</div>
                    <div class="form-value">{{ $request->publicRequest->requester_phone }}</div>
                </div>
                <div class="form-cell">
                    <div class="form-label">مقدم الطلب:</div>
                    <div class="form-value">{{ $request->publicRequest->requester_full_name }}</div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <div class="form-label">التفاصيل عن الطلب المقدم</div>
                    <div class="description-box">{{ $request->publicRequest->description }}</div>
                </div>
            </div>

            <!-- Budget & Date Row -->
            <div class="form-row two-col-custom two-col" style="min-height: 50px;">
                <div class="form-cell">
                    <span class="form-label">تاريخ التنفيذ:</span>
                    <div class="amount-box" style="min-width: 100%;">{{ $request->ready_date ? $request->ready_date->format('d-m-Y') : '' }}</div>                
                </div>
                <div class="form-cell">
                    <span class="form-label">الميزانية:</span>
                    <div class="amount-box" style="min-width: 100%;">{{ $request->publicRequest->budget ? $request->publicRequest->budget->description : '' }}</div>
                </div>
            </div>

            <!-- Implementation Details -->
            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <span class="form-label">المشرف على التنفيذ:</span>
                    <span class="form-value"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <span class="form-label">شروط التنفيذ:</span>
                    <span class="form-value">{{ $request->publicRequest->notes ?? '' }}</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <span class="form-label">طريقة الدفع:</span>
                    <span class="form-value"></span>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <div class="form-label">اقتراح المساعدة</div>
                    <div class="payment-section">
                        <table width="100%">
                            <tr>
                                <td style="width: 70%; vertical-align: top;">
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <span>لمرة واحدة</span>
                                            <span class="checkbox"></span>
                                            <span>تصرف في</span>
                                        </div>
                                    </div>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <span>على دفعات</span>
                                            <span class="checkbox"></span>
                                            <span>تصرف في</span>
                                        </div>
                                    </div>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <span>شيك</span>
                                            <span class="checkbox"></span>
                                            <span>بإسم</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 30%; text-align: center; vertical-align: middle;">
                                    <div class="form-label">المبلغ</div>
                                    <div class="amount-box">
                                        ${{ number_format($request->publicRequest->amount, 2) }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Signatures -->
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

    @if(isset($requests))
    <!-- Multiple Requests -->
    @foreach($requests as $req)
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>طلب مساعدة</h1>
            <h2>مرافق عامة</h2>

            <div class="header-row">
                <div class="header-cell left">
                    رقم الطلب: <span class="input-box">{{ $req->request_number }}</span>
                </div>
                <div class="header-cell right">
                    تاريخ الطلب:<span class="input-box">{{ $req->request_date->format('d-m-Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="form-section">
            <!-- City Row -->
            <div class="form-row two-col">
                <div class="form-cell">
                    <span class="form-label">البلدة:</span>
                    <span class="form-value">{{ $req->publicRequest->city->name_ar ?? $req->publicRequest->city->name }}</span>
                </div>
                <div class="form-cell" style="min-height: 80px;">
                    <div class="form-label">اقتراح منسق البلدة</div>
                </div>
            </div>

            <!-- Requester Info Row -->
            <div class="form-row three-col">
                <div class="form-cell">
                    <span class="form-label">مقدم الطلب:</span>
                    <span class="form-value">{{ $req->publicRequest->requester_full_name }}</span>
                </div>
                <div class="form-cell">
                    <span class="form-label">هاتف:</span>
                    <span class="form-value">{{ substr($req->sender->mobile, 3) }}</span>
                </div>
                <div class="form-cell">
                    <span class="form-label">المرجع:</span>
                    <span class="form-value">{{ $req->referenceMember ? trim($req->referenceMember->first_name . ' ' . $req->referenceMember->father_name . ' ' . $req->referenceMember->last_name) : '' }}</span>
                </div>
            </div>

            <!-- Description Section -->
            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <div class="form-label" style="text-align: center;">التفاصيل عن الطلب المقدم</div>
                    <div class="description-box">{{ $req->publicRequest->description }}</div>
                </div>
            </div>

            <!-- Budget & Date Row -->
            <div class="form-row two-col-custom two-col" style="min-height: 50px;">
                <div class="form-cell">
                    <span class="form-label">تاريخ التنفيذ:</span>
                    <div class="amount-box" style="min-width: 100%;">{{ $req->ready_date ? $req->ready_date->format('d-m-Y') : '' }}</div>                
                </div>
                <div class="form-cell">
                    <span class="form-label">الميزانية:</span>
                    <div class="amount-box" style="min-width: 100%;">{{ $req->publicRequest->budget ? $req->publicRequest->budget->description : '' }}</div>
                </div>
            </div>

            <!-- Implementation Details -->
            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <span class="form-label">المشرف على التنفيذ:</span>
                    <span class="form-value"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <span class="form-label">شروط التنفيذ:</span>
                    <span class="form-value">{{ $req->publicRequest->notes ?? '' }}</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <span class="form-label">طريقة الدفع:</span>
                    <span class="form-value"></span>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="form-row">
                <div class="form-cell" style="width: 100%;">
                    <div class="form-label">اقتراح المساعدة</div>
                    <div class="payment-section">
                        <table width="100%">
                            <tr>
                                <td style="width: 70%; vertical-align: top;">
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <span>لمرة واحدة</span>
                                            <span class="checkbox"></span>
                                            <span>تصرف في</span>
                                        </div>
                                    </div>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <span>على دفعات</span>
                                            <span class="checkbox"></span>
                                            <span>تصرف في</span>
                                        </div>
                                    </div>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <span>شيك</span>
                                            <span class="checkbox"></span>
                                            <span>بإسم</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 30%; text-align: center; vertical-align: middle;">
                                    <div class="form-label">المبلغ</div>
                                    <div class="amount-box">
                                        ${{ number_format($req->publicRequest->amount, 2) }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Signatures -->
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
    @endif
</body>

</html>