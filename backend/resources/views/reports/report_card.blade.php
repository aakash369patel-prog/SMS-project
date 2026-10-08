<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Report Card</h1>
    <p class="muted">{{ config('app.name') }}</p>
    <p><strong>Student:</strong> {{ $student->full_name ?? trim($student->first_name.' '.$student->last_name) }} ({{ $student->admission_number }})</p>
    <p><strong>Class:</strong> {{ $student->schoolClass?->name }} {{ $student->schoolClass?->section }}</p>
    <table>
        <thead>
            <tr>
                <th>Exam</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>Max</th>
                <th>%</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['exam'] }}</td>
                    <td>{{ $row['subject'] }}</td>
                    <td>{{ $row['marks'] }}</td>
                    <td>{{ $row['max'] }}</td>
                    <td>{{ $row['percentage'] }}</td>
                    <td>{{ $row['letter'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
