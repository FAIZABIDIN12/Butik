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
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:tunai,non_tunai',
            'payment_id' => 'required|exists:payments,id', // Pastikan payment_id valid
        ]);
    
        // Ambil pembayaran tertentu
        $payment = Payment::find($request->payment_id);
    
        // Logika untuk penarikan berdasarkan metode pembayaran
        if ($request->method == 'tunai') {
            // Pastikan kita mengurangi dari saldo tunai
            if ($payment->amount >= $request->amount) { // Pastikan amount di sini merujuk ke saldo tunai
                $payment->amount -= $request->amount; // Kurangi saldo tunai
                $payment->save(); // Simpan perubahan ke database
                
                // Simpan data penarikan ke tabel withdrawals
                Withdraw::create([
                    'payment_id' => $request->payment_id,
                    'amount' => $request->amount,
                    'method' => $request->method,
                ]);
                
                return redirect()->back()->with('success', 'Penarikan tunai berhasil dan saldo berhasil diperbarui!');
            } else {
                return redirect()->back()->withErrors(['amount' => 'Saldo tidak cukup untuk penarikan ini.']);
            }
        } elseif ($request->method == 'non_tunai') {
            // Pastikan kita mengurangi dari saldo non tunai
            if ($payment->amount >= $request->amount) { // Menggunakan field untuk non tunai
                $payment->amount -= $request->amount; // Kurangi saldo non tunai
                $payment->save(); // Simpan perubahan ke database
    
                // Simpan data penarikan ke tabel withdrawals
                Withdraw::create([
                    'payment_id' => $request->payment_id,
                    'amount' => $request->amount,
                    'method' => $request->method,
                ]);
                
                return redirect()->back()->with('success', 'Penarikan non tunai berhasil dan saldo berhasil diperbarui!');
            } else {
                return redirect()->back()->withErrors(['amount' => 'Saldo non tunai tidak cukup untuk penarikan ini.']);
            }
        }
    }
    
}
