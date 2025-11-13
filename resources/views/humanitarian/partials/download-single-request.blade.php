@if($showHeader ?? true)
<div class="header">
    <div class="logo">مشروع الوطن - Project Watan</div>
    <div class="title">طلب مساعدة إنسانية</div>
    <div class="title" style="font-size: 16px; color: #622032;">Humanitarian Aid Request</div>
    <div class="request-number">رقم الطلب: {{ $request->request_number }}</div>
    <div class="request-number">تاريخ: {{ $request->request_date->format('Y/m/d') }}</div>
</div>
@else
<div style="margin-bottom: 20px; padding: 15px; background: #f8f0e2; border-radius: 8px;">
    <div style="font-size: 18px; font-weight: bold; color: #622032; margin-bottom: 5px;">
        رقم الطلب: {{ $request->request_number }}
    </div>
    <div style="font-size: 14px; color: #666;">
        تاريخ: {{ $request->request_date->format('Y/m/d') }}
    </div>
</div>
@endif

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

    @if($request->budget)
    <div class="field">
        <span class="field-label">الميزانية:</span>
        <span class="field-value">{{ $request->budget->description }}</span>
    </div>
    @endif

    @if($request->ready_date)
    <div class="field">
        <span class="field-label">تاريخ الجاهزية:</span>
        <span class="field-value">{{ $request->ready_date->format('Y/m/d') }}</span>
    </div>
    @endif
</div>

@if($showHeader ?? true)
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
@endif
