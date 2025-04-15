<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\PDF;
use App\Exports\FilteredExport;
use Illuminate\Support\Facades\Storage;

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
            'format' => 'required|in:pdf,word'
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

            return $dateA <=> $dateB; // Urutkan dari yang terkecil ke terbesar (ascending)
        });

        // Terapkan normalisasi ke kolom penjualan sebelum dikirim ke tampilan
        foreach ($filteredData as &$row) {
            $row[6] = $this->normalizePenjualan($row[6]); // Pastikan ini sesuai dengan indeks kolom penjualan
        }
        unset($row); // Hindari referensi variabel tak terduga

        // Generate PDF
        $pdf = PDF::loadView('absensi.pdf', compact('filteredData', 'startDate', 'endDate', 'bulan'));
        return $pdf->download('Penjualan ' . date('d', $startDate) . ' Sampai ' . date('d', $endDate) . ' ' . $bulan . ' ' . date('Y', $endDate) . '.pdf');
    }
}
