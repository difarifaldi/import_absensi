<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan dari {{ date('d', $startDate) }} sampai
        {{ date('d', $endDate) . ' ' . $bulan . ' ' . date('Y', $endDate) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        td:nth-child(4) {
            text-align: left;
            vertical-align: top;
        }

        td:nth-child(4) ul {
            padding-left: 15px;
            margin: 2px;
        }

        h2 {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Laporan dari {{ date('d', $startDate) }} sampai
        {{ date('d', $endDate) . ' ' . $bulan . ' ' . date('Y', $endDate) }}</h2>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Sesi</th>
                <th>Nama</th>
                <th>Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filteredData as $row)
                <tr>
                    <td>{{ is_numeric($row[2]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('d/m/Y') : $row[2] }}
                    </td>
                    <td>{{ $row[3] }}</td>
                    <td>{{ $row[1] }}</td>
                    <td>{!! $row[6] !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
