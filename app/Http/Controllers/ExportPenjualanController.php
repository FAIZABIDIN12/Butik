<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PenjualanExport;
use Illuminate\Http\Request;

class ExportPenjualanController extends Controller
{
    public function export(Request $request)
    {
        // Ambil parameter tanggal mulai dan tanggal akhir dari request
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Pastikan tanggal mulai dan tanggal akhir ada
        if (!$start_date || !$end_date) {
            return redirect()->back()->with('error', 'Tanggal mulai dan tanggal akhir harus dipilih.');
        }

        // Filter data berdasarkan tanggal yang dipilih
        $penjualan = Penjualan::with('details')->whereBetween('created_at', [
            $start_date . ' 00:00:00', // Mulai tanggal
            $end_date . ' 23:59:59' // Akhir tanggal
        ])->get();


        // Format nama file dengan tanggal mulai dan akhir
        $formatted_start_date = \Carbon\Carbon::parse($start_date)->format('Y-m-d');
        $formatted_end_date = \Carbon\Carbon::parse($end_date)->format('Y-m-d');
        $file_name = "report_penjualan_{$formatted_start_date}-{$formatted_end_date}.xlsx";

        // Ekspor data yang sudah difilter ke Excel dengan nama file yang telah diformat
        return Excel::download(new PenjualanExport($penjualan), $file_name);
    }
}
