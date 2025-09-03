<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dataset->nama }} - Data Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px;   /* tambahkan padding dalam body */
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .header h2 {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        
        .info-section h3 {
            font-size: 11px;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .metadata-table td {
            padding: 3px 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        
        .metadata-table td:first-child {
            background-color: #ecf0f1;
            font-weight: bold;
            width: 30%;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .data-table th {
            background-color: #34495e;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #2c3e50;
        }
        
        .data-table td {
            padding: 4px;
            border: 1px solid #bdc3c7;
            font-size: 8px;
            text-align: center;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .data-table tbody tr:hover {
            background-color: #e8f4fd;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #7f8c8d;
            padding: 5px;
            border-top: 1px solid #bdc3c7;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Responsive column width */
        .data-table th,
        .data-table td {
            max-width: 100px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .container {
            padding: 10px 20px;   /* ruang ekstra kiri kanan */
        }

        
        @page {
            margin: 20mm;
            size: A4 landscape;
        }
        
        /* Print optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $dataset->nama }}</h1>
        <h2>{{ $dataset->skpd->nama ?? 'Tidak Diketahui' }}</h2>
        <p style="font-size: 9px; margin-top: 5px;">
            Dicetak pada: {{ date('d F Y H:i:s') }} | 
            Total Data: {{ count($tableData) }} record(s)
        </p>
    </div>

    {{-- Metadata Section --}}
    @if(!empty($metadata))
    <div class="info-section">
        <h3>Informasi Dataset</h3>
        <table class="metadata-table">
            @foreach($metadata as $meta)
                <tr>
                    <td>{{ $meta['label'] }}</td>
                    <td>{{ $meta['value'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Dataset Info --}}
    <div class="info-section">
        <h3>Detail Dataset</h3>
        <table class="metadata-table">
            <tr>
                <td>Nama Dataset</td>
                <td>{{ $dataset->nama }}</td>
            </tr>
            <tr>
                <td>Aspek</td>
                <td>{{ $dataset->aspek->nama ?? 'Tidak Diketahui' }}</td>
            </tr>
            <tr>
                <td>SKPD</td>
                <td>{{ $dataset->skpd->nama ?? 'Tidak Diketahui' }}</td>
            </tr>
            <tr>
                <td>Tanggal Dibuat</td>
                <td>{{ $dataset->created_at->format('d F Y') }}</td>
            </tr>
            <tr>
                <td>Jumlah Kolom</td>
                <td>{{ count($columns) }}</td>
            </tr>
            <tr>
                <td>Jumlah Baris Data</td>
                <td>{{ count($tableData) }}</td>
            </tr>
        </table>
    </div>

    {{-- Data Table --}}
    @if(!empty($tableData))
    <h3 style="margin-bottom: 10px; color: #2c3e50;">Data Tabel</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($tableData as $row)
                <tr>
                    <td style="background-color: #ecf0f1; font-weight: bold;">{{ $no++ }}</td>
                    @foreach($columns as $column)
                        <td>{{ $row[$column] ?? '-' }}</td>
                    @endforeach
                </tr>
                
                {{-- Page break every 20 rows untuk mencegah tabel terpotong --}}
                @if($no % 20 == 1 && !$loop->last)
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                @foreach($columns as $column)
                                    <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                @endif
            @endforeach
        </tbody>
    </table>
    @else
        <div class="info-section">
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">
                Tidak ada data yang tersedia untuk ditampilkan.
            </p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>
            Generated by System | Dataset: {{ $dataset->nama }} | 
            Page <span class="pagenum"></span>
        </p>
    </div>

    {{-- JavaScript untuk nomor halaman --}}
    <script type="text/php">
        if (isset($pdf)) {
            $x = 520;
            $y = 10;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $size = 8;
            $color = array(0.5, 0.5, 0.5);
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>
</body>
</html>