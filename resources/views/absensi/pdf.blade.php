<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan dari tanggal
        {{ ltrim(date('d', $startDate), '0') }}
        @if ($bulanStart)
            {{ ' ' . $bulanStart }}
        @endif
        sampai {{ ltrim(date('d', $endDate), '0') }} {{ $bulan }} {{ date('Y', $endDate) }}
    </title>
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

        /* Khusus untuk tabel total penjualan */
        .tabel-total-penjualan {
            width: 100%;
            border-collapse: collapse;
        }

        .tabel-total-penjualan th,
        .tabel-total-penjualan td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        /* Biar baris selang-seling */
        .tabel-total-penjualan tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .tabel-total-penjualan thead th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <h2>
        Laporan dari tanggal
        {{ ltrim(date('d', $startDate), '0') }}
        @if ($bulanStart)
            {{ ' ' . $bulanStart }}
        @endif
        sampai {{ ltrim(date('d', $endDate), '0') }} {{ $bulan }} {{ date('Y', $endDate) }}
    </h2>



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

                    $penjualan = floatval(str_replace(['.', 'Rp', 'RP', ' '], '', $row[6]));
                @endphp

                <tr>
                    <td class="{{ $showTanggal ? 'tanggal-utama' : 'tanggal-selanjutnya' }}"
                        style="{{ $isLastForDate ? 'border-bottom: 1px solid black;' : '' }}">
                        {{ $showTanggal ? $tanggal : '' }}
                    </td>

                    <td class="selain-tanggal">{{ $row[3] }}</td>
                    <td class="selain-tanggal">{{ $row[1] }}</td>
                    {{-- Str::startsWith($row[7], 'Rp') --}}
                    <td class="selain-tanggal"> {{ 'Rp. ' . number_format($penjualan, 0, ',', '.') }} </td>
                    <td class="selain-tanggal">{!! $row[7] !!}</td>
                </tr>
                @php $lastDate = $tanggal; @endphp
            @endforeach


        </tbody>
    </table>

    <br><br> <!-- Add spacing between the tables -->

    <!-- Table for total sales per person -->
    @if ($showPendapatan)
        <h3>Total Penjualan Per Host Live</h3>
        <table class="tabel-total-penjualan">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Total Penjualan</th>
                    <th>Total Sesi</th>
                    <th>Rata-rata Penjualan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Mengelompokkan data berdasarkan Nama dan menghitung total penjualan
                    $totalPenjualanPerOrang = [];
                    $grandTotalPenjualan = 0;
                    $grandTotalSesi = 0;

                    foreach ($filteredData as $row) {
                        // Gunakan trim untuk menghapus spasi yang tidak diperlukan
                        $nama = strtolower(trim($row[1])); // Nama ada di kolom 1, dengan trim dan lowercase
                        $penjualan = $row[6]; // Penjualan ada di kolom 6

                        // Menghilangkan 'Rp' dan titik (untuk format Rupiah) lalu mengonversi ke angka
                        $penjualan = floatval(str_replace(['.', 'Rp', 'RP', ' '], '', $penjualan));

                        // Pastikan tidak ada duplikasi untuk nama yang sama
                        if (!isset($totalPenjualanPerOrang[$nama])) {
                            $totalPenjualanPerOrang[$nama] = [
                                'jumlah_sesi' => 0,
                                'total_penjualan' => 0,
                            ];
                        }

                        // Menambahkan penjualan untuk orang yang sama
                        $totalPenjualanPerOrang[$nama]['total_penjualan'] += $penjualan;
                        $totalPenjualanPerOrang[$nama]['jumlah_sesi'] += 1;
                        $grandTotalPenjualan += $penjualan;
                        $grandTotalSesi += 1;
                    }
                @endphp

                @php
                    // Sorting descending by total_penjualan
                    uasort($totalPenjualanPerOrang, function ($a, $b) {
                        return $b['total_penjualan'] <=> $a['total_penjualan'];
                    });
                @endphp

                @foreach ($totalPenjualanPerOrang as $nama => $data)
                    <tr>
                        <td>{{ ucfirst($nama) }}</td> <!-- Menampilkan nama dengan huruf pertama kapital -->
                        <td>{{ 'Rp ' . number_format($data['total_penjualan'], 0, ',', '.') }}</td>
                        <td>{{ $data['jumlah_sesi'] . ' Sesi' }}</td>
                        <td>{{ 'Rp ' . number_format($data['total_penjualan'] / $data['jumlah_sesi'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach

                <!-- Tambahkan baris total semua penjualan -->
                <tr style="font-weight: bold; background-color: #dff0d8;">
                    <td>Total </td>
                    <td>{{ 'Rp ' . number_format($grandTotalPenjualan, 0, ',', '.') }}</td>
                    <td>{{ $grandTotalSesi . ' Sesi' }}</td>
                    <td>{{ 'Rp ' . number_format($grandTotalPenjualan / $grandTotalSesi, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</body>

</html>
