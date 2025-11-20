<div class="form-container">
    <!-- Header: طلب مساعدة with request number and date -->
    <div class="header">
        <h1>طلب مساعدة</h1>
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
            <span class="form-value large">{{ $request->humanitarianRequest->requester_full_name ?? '' }}</span>
            <span class="form-label">البلدة :</span>
            <span class="form-value medium">{{ $request->humanitarianRequest->voter->city->name ?? '' }}</span>
            <span class="form-label">رقم السجل:</span>
            <span class="form-value small">{{ $request->humanitarianRequest->voter->ro_number ?? '' }}</span>
        </div>

        <!-- Row 2: Birth Date, Spouse Name -->
        <div class="form-row">
            <span class="form-label">تاريخ الولادة :</span>
            <span class="form-value medium">{{ $request->humanitarianRequest->voter->birth_date ?? '' }}</span>
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
            <span class="form-value full">{{ $request->humanitarianRequest->voter->city->name ?? '' }}</span>
        </div>

        <!-- Row 5: Reference -->
        <div class="form-row">
            <span class="form-label">الهاتف :</span>
            <span class="form-value medium">{{ $request->humanitarianRequest->voter->phone ?? '' }}</span>
            <span class="form-label">المرجع</span>
            <span class="form-value large">{{ $request->referenceMember->name ?? '' }}</span>
        </div>
    </div>

    <!-- Payment Types Section -->
    <div class="form-section">
        <div class="checkbox-row">
            <div class="checkbox-item">
                <span class="checkbox {{ (isset($request->humanitarianRequest->subtype) && (str_contains(mb_strtolower($request->humanitarianRequest->subtype), 'اجتماعية') || str_contains(strtolower($request->humanitarianRequest->subtype), 'social'))) ? 'checked' : '' }}"></span>
                <span>اجتماعية</span>
            </div>
            <div class="checkbox-item">
                <span class="checkbox {{ (isset($request->humanitarianRequest->subtype) && (str_contains(mb_strtolower($request->humanitarianRequest->subtype), 'استشفائية') || str_contains(strtolower($request->humanitarianRequest->subtype), 'hospital'))) ? 'checked' : '' }}"></span>
                <span>استشفائية</span>
            </div>
            <div class="checkbox-item">
                <span class="checkbox {{ (isset($request->humanitarianRequest->subtype) && (str_contains(mb_strtolower($request->humanitarianRequest->subtype), 'طبية') || str_contains(strtolower($request->humanitarianRequest->subtype), 'medical'))) ? 'checked' : '' }}"></span>
                <span>طبية</span>
            </div>
            <div class="checkbox-item">
                <span class="checkbox {{ (isset($request->humanitarianRequest->subtype) && (str_contains(mb_strtolower($request->humanitarianRequest->subtype), 'تربوية') || str_contains(strtolower($request->humanitarianRequest->subtype), 'education'))) ? 'checked' : '' }}"></span>
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
            <span class="form-value full">{{ $request->humanitarianRequest->subtype ?? '' }}</span>
        </div>
        <div class="amount-row">
            <div class="amount-item">
                <label>المبلغ</label>
                <span class="amount-highlight">${{ isset($request->humanitarianRequest->amount) ? number_format($request->humanitarianRequest->amount, 0) : '' }}</span>
            </div>
            <div class="amount-item">
                <label>المجموع:</label>
                <span class="amount-highlight">${{ isset($request->humanitarianRequest->amount) ? number_format($request->humanitarianRequest->amount, 0) : '' }}</span>
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
            <span class="form-value" style="min-width: 300px;"><!-- {{ $request->humanitarianRequest->requester_full_name ?? ''  }} --> </span>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="notes-section">
        <div class="notes-label">ملاحظات :</div>
        <div class="notes-box">{{ $request->humanitarianRequest->notes ?? '' }}</div>
    </div>

    <!-- Office Footer -->
    <div class="approval-section" style="display: flex; gap: 0;">
        <div class="notes-section" style="flex: 1; border-left: 1px solid #000; padding: 4px;">
            <div class="notes-label" style="text-align: center;">مكتب الشؤون الإجتماعية</div>
            <div class="notes-box" style="min-height: 30px;"></div>
        </div>
        <div class="notes-section" style="flex: 1; border-left: 1px solid #000; padding: 4px;">
            <div class="notes-label" style="text-align: center;">مسؤول القطاع</div>
            <div class="notes-box" style="min-height: 30px;"></div>
        </div>
        <div class="notes-section" style="flex: 1; padding: 4px;">
            <div class="notes-label" style="text-align: center;">الموافقة</div>
            <div class="notes-box" style="min-height: 30px;"></div>
        </div>
    </div>
</div>