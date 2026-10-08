<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        .box { border: 1px solid #ccc; padding: 12px; margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Fee Receipt</h1>
    <p>{{ config('app.name') }}</p>
    <div class="box">
        <p><strong>Student:</strong> {{ $fee->student->full_name ?? $fee->student->admission_number }}</p>
        <p><strong>Fee:</strong> {{ $fee->title }}</p>
        <p><strong>Total amount:</strong> {{ number_format((float) $fee->amount, 2) }}</p>
        <p><strong>Amount paid:</strong> {{ number_format((float) $fee->amount_paid, 2) }}</p>
        <p><strong>Balance:</strong> {{ number_format(max(0, (float) $fee->amount - (float) $fee->amount_paid), 2) }}</p>
        <p><strong>Status:</strong> {{ $fee->status }}</p>
        <p><strong>Due date:</strong> {{ $fee->due_date?->format('Y-m-d') }}</p>
        @if($payment)
            <hr>
            <p><strong>Last payment:</strong> {{ number_format((float) $payment->amount, 2) }} on {{ $payment->payment_date?->format('Y-m-d') }}</p>
            <p><strong>Method:</strong> {{ $payment->payment_method }}</p>
        @endif
    </div>
</body>
</html>
