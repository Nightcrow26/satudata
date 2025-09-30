<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Walidata - {{ $walidata->indikator->uraian_indikator ?? 'Indikator' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #2563eb;
        }
        
        .header h2 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .metadata {
            margin-bottom: 20px;
        }
        
        .metadata table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .metadata td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .metadata td:first-child {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 200px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .data-table th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Indikator Walidata</h1>
        <h2>{{ $walidata->indikator->uraian_indikator ?? 'Indikator' }}</h2>
        <p><strong>SKPD:</strong> {{ $walidata->skpd->nama ?? 'N/A' }}</p>
        <p><strong>Aspek:</strong> {{ $walidata->aspek->nama ?? 'N/A' }}</p>
    </div>

    @if(!empty($metadata))
    <div class="metadata">
        <h3 style="margin-bottom: 10px; color: #2563eb;">Informasi Metadata</h3>
        <table>
            @foreach($metadata as $meta)
                <tr>
                    <td>{{ $meta['label'] ?? '' }}</td>
                    <td>{{ $meta['value'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    @if(!empty($tableData) && !empty($columns))
    <div class="data-section">
        <h3 style="margin-bottom: 10px; color: #2563eb;">Data Indikator</h3>
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($tableData as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td>{{ $row[$column] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: #666;">
        <p>Tidak ada data yang tersedia untuk indikator ini.</p>
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Sumber: Sistem Satu Data</p>
    </div>
</body>
</html>