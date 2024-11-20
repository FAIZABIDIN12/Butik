<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $accounts = Account::all()->keyBy('code'); // Menggunakan keyBy untuk memudahkan pencarian
        return view('categories.index', compact('categories', 'accounts'));
    }

    public function create()
    {
        // Ambil semua akun untuk dropdown
        $accounts = Account::all();
        return view('categories.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:categories,code',
            'type' => 'required|in:in,out,mutation',
            'name' => 'required|string',
            'debit_account_code' => 'nullable|string|exists:accounts,code',
            'credit_account_code' => 'nullable|string|exists:accounts,code',
            'note' => 'nullable|string',
        ]);

        // Simpan kategori dengan data dari request
        Category::create($request->only([
            'code',
            'type',
            'name',
            'debit_account_code',
            'credit_account_code',
            'note'
        ]));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($code)
    {
        // Ambil kategori dan semua akun
        $category = Category::findOrFail($code);
        $accounts = Account::all();
        return view('categories.edit', compact('category', 'accounts'));
    }

    public function update(Request $request, $code)
    {
        $request->validate([
            'type' => 'required|in:in,out,mutation',
            'name' => 'required|string',
            'debit_account_code' => 'nullable|string|exists:accounts,code',
            'credit_account_code' => 'nullable|string|exists:accounts,code',
            'note' => 'nullable|string',
        ]);

        // Update kategori dengan data dari request
        $category = Category::findOrFail($code);
        $category->update($request->only([
            'type',
            'name',
            'debit_account_code',
            'credit_account_code',
            'note'
        ]));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($code)
    {
        $category = Category::findOrFail($code);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function report()
    {
        // Ambil semua kategori dengan relasi akun debit dan kredit
        $categories = Category::with(['debitAccount', 'creditAccount', 'transactions'])->get();

        // Proses data kategori menjadi format yang diinginkan
        $categoryData = $categories->map(function ($category) {
            return [
                'category_code' => $category->code,
                'category_name' => $category->name,
                'debit_account' => $category->debitAccount ? $category->debitAccount->name : 'Tidak ada',
                'debit_total' => $category->transactions->where('category_code', $category->code)->sum('nominal'), // Gunakan 'transactions' yang sudah di-load dengan relasi
                'credit_account' => $category->creditAccount ? $category->creditAccount->name : 'Tidak ada',
                'credit_total' => $category->transactions->where('category_code', $category->code)->sum('nominal'), // Gunakan 'transactions' yang sudah di-load dengan relasi
            ];
        });

        // Kirim data kategori yang sudah diproses ke tampilan
        return view('categories.report', compact('categoryData'));
    }
}
