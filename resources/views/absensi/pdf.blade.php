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
        .selain-tanggal {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        td:nth-child(5) {
            text-align: left;
            vertical-align: top;
        }

        td:nth-child(5) ul {
            padding-left: 15px;
            margin: 2px;
        }

        h2 {
            text-align: center;
            font-weight: bold;
        }

        .tanggal-utama {
            padding: 5px;
            border-left: 1px solid black;
            border-right: 1px solid black;
            border-top: 1px solid black;
        }

        .tanggal-selanjutnya {
            padding: 5px;
            border-left: 1px solid black;
            border-right: 1px solid black;
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
                <th>Penjualan Dalam Rupiah</th>
                <th>Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $dateCounts = [];
                foreach ($filteredData as $row) {
                    $tanggal = is_numeric($row[2])
                        ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('d/m/Y')
                        : $row[2];
                    $dateCounts[$tanggal] = ($dateCounts[$tanggal] ?? 0) + 1;
                }

                $dateRemainder = []; // counter sementara
                $lastDate = null;
            @endphp


            @foreach ($filteredData as $row)
                @php
                    $tanggal = is_numeric($row[2])
                        ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('d/m/Y')
                        : $row[2];

                    $showTanggal = $tanggal !== $lastDate;

                    $dateRemainder[$tanggal] = ($dateRemainder[$tanggal] ?? 0) + 1;
                    $isLastForDate = $dateRemainder[$tanggal] === $dateCounts[$tanggal];
                @endphp

                <tr>
                    <td class="{{ $showTanggal ? 'tanggal-utama' : 'tanggal-selanjutnya' }}"
                        style="{{ $isLastForDate ? 'border-bottom: 1px solid black;' : '' }}">
                        {{ $showTanggal ? $tanggal : '' }}
                    </td>


                    <td class="selain-tanggal">{{ $row[3] }}</td>
                    <td class="selain-tanggal">{{ $row[1] }}</td>
                    <td class="selain-tanggal">{{ $row[7] }}</td>
                    <td class="selain-tanggal">{!! $row[6] !!}</td>
                </tr>
                @php $lastDate = $tanggal; @endphp
            @endforeach


        </tbody>
    </table>

    <br><br> <!-- Add spacing between the tables -->

    <!-- Table for total sales per person -->
    <h3>Total Penjualan Per Orang</h3>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Mengelompokkan data berdasarkan Nama dan menghitung total penjualan
                $totalPenjualanPerOrang = [];

                foreach ($filteredData as $row) {
                    // Gunakan trim untuk menghapus spasi yang tidak diperlukan
                    $nama = strtolower(trim($row[1])); // Nama ada di kolom 1, dengan trim dan lowercase
                    $penjualan = $row[7]; // Penjualan ada di kolom 7

                    // Menghilangkan 'Rp' dan titik (untuk format Rupiah) lalu mengonversi ke angka
                    $penjualan = floatval(str_replace(['.', 'Rp', ' '], '', $penjualan));

                    // Pastikan tidak ada duplikasi untuk nama yang sama
                    if (!isset($totalPenjualanPerOrang[$nama])) {
                        $totalPenjualanPerOrang[$nama] = 0;
                    }

                    // Menambahkan penjualan untuk orang yang sama
                    $totalPenjualanPerOrang[$nama] += $penjualan;
                }
            @endphp

            @foreach ($totalPenjualanPerOrang as $nama => $totalPenjualan)
                <tr>
                    <td>{{ ucfirst($nama) }}</td> <!-- Menampilkan nama dengan huruf pertama kapital -->
                    <td>{{ 'Rp ' . number_format($totalPenjualan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
