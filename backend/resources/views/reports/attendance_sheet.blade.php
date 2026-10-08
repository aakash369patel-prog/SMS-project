<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Sheet</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Attendance Sheet</h1>
    <p><strong>Class:</strong> {{ $classLabel }}</p>
    <p><strong>Period:</strong> {{ $from }} to {{ $to }}</p>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Excused</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['present'] }}</td>
                    <td>{{ $row['absent'] }}</td>
                    <td>{{ $row['late'] }}</td>
                    <td>{{ $row['excused'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
