<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\PDF;
use App\Exports\FilteredExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    public function index()
    {
        return view('absensi.index');
    }

    // Fungsi untuk normalisasi kolom penjualan
    private function normalizePenjualan($text)
    {
        // Hapus semua tanda kurung beserta spasi di sekitarnya
        $text = preg_replace('/\s*\(\s*|\s*\)\s*/', '', $text);

        // Hapus karakter newline dan ubah ke bentuk seragam
        $text = trim(str_replace(["\n", "\r"], ' ', $text));

        // Jika format menggunakan "- ", ubah ke array berdasarkan pemisah "- "
        if (strpos($text, '- ') !== false) {
            $items = array_map('trim', explode('- ', $text));
        }
        // Jika format menggunakan koma ",", ubah ke array berdasarkan koma
        elseif (strpos($text, ',') !== false) {
            $items = array_map('trim', explode(',', $text));
        } else {
            // Jika tidak sesuai format, kembalikan teks asli
            return $text;
        }

        // Hapus elemen kosong & kembalikan dalam format HTML <ul><li>
        $items = array_filter($items);
        return '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
    }





    private function bulanIndonesia($bulanInggris)
    {
        $bulan = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];

        return $bulan[$bulanInggris] ?? $bulanInggris;
    }


    public function generatePDF(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $path = $request->file('file')->store('temp');
        $filePath = Storage::path($path); // Mendapatkan path absolut

        // Ambil data dari Excel
        $data = Excel::toArray([], $filePath)[0];

        // Buang baris pertama (header)
        array_shift($data);

        // Konversi format tanggal ke timestamp
        $startDate = strtotime($request->start_date);
        $endDate = strtotime($request->end_date);

        // Ambil nama bulan dalam bahasa Indonesia
        $bulan = $this->bulanIndonesia(date('F', $endDate));
        $bulanStart = date('F', $startDate) != date('F', $endDate)
            ? $this->bulanIndonesia(date('F', $startDate))
            : null;


        $filteredData = array_filter($data, function ($row) use ($startDate, $endDate) {
            if (!isset($row[2]) || empty($row[2])) return false; // Pastikan ada nilai di kolom ke-3

            try {
                // Periksa apakah nilai di kolom ke-3 adalah angka (serial number Excel)
                if (is_numeric($row[2])) {
                    $rowDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]);
                } else {
                    // Jika bukan angka, coba parse sebagai format d/m/Y
                    $rowDate = \DateTime::createFromFormat('d/m/Y', $row[2]);
                }

                if (!$rowDate) return false; // Jika gagal parsing, lewati

                $timestamp = $rowDate->getTimestamp();

                return $timestamp >= $startDate && $timestamp <= $endDate;
            } catch (\Exception $e) {
                return false; // Jika error saat parsing, skip baris ini
            }
        });

        // Konversi format tanggal ke timestamp & sorting
        usort($filteredData, function ($a, $b) {
            $dateA = is_numeric($a[2]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($a[2])->getTimestamp() : strtotime($a[2]);
            $dateB = is_numeric($b[2]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($b[2])->getTimestamp() : strtotime($b[2]);

            if ($dateA === $dateB) {
                $sesiOrder = [
                    'Sesi 1' => 1,
                    'Sesi 2' => 2,
                    'Sesi 3' => 3,
                    'Sesi 4' => 4,
                ];

                // Ambil hanya bagian awal (contoh: "Sesi 3" dari "Sesi 3 (14.00-18.00)")
                $sesiNameA = Str::before($a[3], ' (');
                $sesiNameB = Str::before($b[3], ' (');

                $sesiA = $sesiOrder[$sesiNameA] ?? 99;
                $sesiB = $sesiOrder[$sesiNameB] ?? 99;

                return $sesiA <=> $sesiB;
            }

            return $dateA <=> $dateB;
        });

        // Terapkan normalisasi ke kolom penjualan sebelum dikirim ke tampilan
        foreach ($filteredData as &$row) {
            $row[7] = $this->normalizePenjualan($row[7]); // Pastikan ini sesuai dengan indeks kolom penjualan
        }
        unset($row); // Hindari referensi variabel tak terduga

        // Generate PDF
        $showPendapatan = $request->has('pendapatan') && $request->pendapatan == 1;

        $pdf = PDF::loadView('absensi.pdf', compact('filteredData', 'startDate', 'endDate', 'bulan', 'bulanStart', 'showPendapatan'));


        $filename = ($showPendapatan
            ? 'Pendapatan Host Live '
            : 'Penjualan ')
            . ltrim(date('d', $startDate), '0')
            . ($bulanStart ? ' ' . $bulanStart : '')
            . ' Sampai '
            . ltrim(date('d', $endDate), '0')
            . ' '
            . $bulan
            . ' '
            . date('Y', $endDate)
            . '.pdf';

        return $pdf->download($filename);
    }
}
