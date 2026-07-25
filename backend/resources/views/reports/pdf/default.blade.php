<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'VESTRA Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #0d3b66;
            color: #ffffff;
            padding: 24px;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        .meta {
            padding: 0 24px 16px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 24px;
            width: calc(100% - 48px);
        }
        th, td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VESTRA Detergents</h1>
        <p>{{ $title ?? 'Business Report' }}</p>
    </div>

    <div class="meta">
        <strong>Generated:</strong> {{ now()->format('F j, Y H:i') }}
        @if(isset($period))
            <br><strong>Period:</strong> {{ $period }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column['label'] ?? $column['name'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($columns as $column)
                        <td>
                            @php
                                $key = $column['name'] ?? $column['key'] ?? null;
                                $value = $key ? data_get($row, $key) : null;
                            @endphp
                            @if(is_array($value))
                                {{ json_encode($value) }}
                            @else
                                {{ $value ?? '' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        VESTRA Commerce Platform &copy; {{ now()->year }} — Confidential
    </div>
</body>
</html>
