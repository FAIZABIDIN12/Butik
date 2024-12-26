<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Withdraw;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Menampilkan total saldo tunai dan non tunai
    public function index()
    {
        // Fetch all payments
        $payments = Payment::all();

        // Example of getting a specific payment ID (adjust based on your logic)
        $firstPayment = $payments->first(); // Get the first payment for example
        $paymentId = $firstPayment ? $firstPayment->id : null; // Set it or null if no payments exist

        // Calculate balances
        $cashBalance = $payments->where('metode_pembayaran', 'tunai')->sum('amount');
        $nonCashBalance = $payments->where('metode_pembayaran', 'non_tunai')->sum('amount');
        $totalSales = $payments->sum('amount');

        // Fetch withdrawal history
        $withdrawals = Withdraw::all();

        // Return the view with all variables
        return view('report.report_penjualan', compact('cashBalance', 'nonCashBalance', 'totalSales', 'withdrawals', 'paymentId'));
    }


    public function withdraw(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'amount' => 'required|numeric|min:1', // Pastikan amount tidak nol atau negatif
            'method' => 'required|in:tunai,non_tunai',
            'payment_id' => 'required|exists:payments,id', // Pastikan payment_id valid
        ]);

        // Ambil data pembayaran terkait
        $payment = Payment::find($request->payment_id);

        // Periksa apakah saldo mencukupi
        if ($payment->amount < $request->amount) {
            return redirect()->back()->withErrors(['amount' => 'Saldo tidak cukup untuk penarikan ini.']);
        }

        // Kurangi saldo sesuai jumlah penarikan
        $payment->amount -= $request->amount;
        $payment->save(); // Simpan perubahan saldo ke database

        // Simpan data penarikan ke tabel withdrawals
        Withdraw::create([
            'payment_id' => $payment->id, // Pastikan ini merujuk ke ID pembayaran yang valid
            'amount' => $request->amount,
            'method' => $request->method,
        ]);

        // Redirect dengan pesan sukses
        return redirect()->back()->with('success', 'Penarikan berhasil dan saldo telah diperbarui.');
    }
}
