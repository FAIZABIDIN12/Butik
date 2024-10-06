<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaction;
use App\Models\Account;
use PDF;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');
        
        return view('produk.index', compact('kategori'));
    }

    public function data()
    {
        $produk = Produk::leftJoin('kategori', 'kategori.id_kategori', 'produk.id_kategori')
            ->select('produk.*', 'nama_kategori')
            ->orderBy('kode_produk', 'asc')
            ->get();
    
        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk) {
                return '
                    <input type="checkbox" name="id_produk[]" value="'. $produk->id_produk .'">
                ';
            })
            ->addColumn('kode_produk', function ($produk) {
                return '<span class="label label-success">'. $produk->kode_produk .'</span>';
            })
            ->addColumn('harga_beli', function ($produk) {
                return format_uang($produk->harga_beli);
            })
            ->addColumn('harga_jual', function ($produk) {
                return format_uang($produk->harga_jual);
            })
            ->addColumn('stok', function ($produk) {
                return format_uang($produk->stok);
            })
            ->addColumn('aksi', function ($produk) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('produk.update', $produk->id_produk) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i>Edit</button>
                    <button type="button" onclick="addStockForm(`'. route('produk.add_stock', $produk->id_produk) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-plus"></i> Stock</button>
                    <button type="button" onclick="reduceStockForm(`'. route('produk.reduce_stock', $produk->id_produk) .'`, '. $produk->id_produk .')" class="btn btn-xs btn-warning btn-flat"><i class="fa fa-minus"></i> Stock</button>
                </div>
                ';
            })
            
            
            ->rawColumns(['aksi', 'kode_produk', 'select_all'])
            ->make(true);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $produk = Produk::latest()->first() ?? new Produk();
        $request['kode_produk'] = 'P'. tambah_nol_didepan((int)$produk->id_produk +1, 6);

        $produk = Produk::create($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        return response()->json($produk);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);
        $produk->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk = Produk::find($id);
        $produk->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk as $id) {
            $produk = Produk::find($id);
            $produk->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = array();
        foreach ($request->id_produk as $id) {
            $produk = Produk::find($id);
            $dataproduk[] = $produk;
        }

        $no  = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('produk.pdf');
    }
   
    public function addStock(Request $request, $id)
    {
        \Log::info('Request data: ', $request->all());
    
        $product = Produk::findOrFail($id);
        $jumlahStok = $request->input('jumlah');
    
        if ($jumlahStok <= 0) {
            return response()->json(['message' => 'Jumlah stok harus lebih besar dari nol.'], 400);
        }
    
        $product->stok += $jumlahStok;
    
        if (!$product->save()) {
            \Log::error('Gagal menambah stok untuk produk ID: ' . $product->id_produk);
            return response()->json(['message' => 'Gagal menambah stok.'], 500);
        }
    
        // Catat transaksi penambahan stok
        $transaction = Transaction::storeStockTransaction(
            Transaction::CATEGORY_STOCK_ADD,
            $jumlahStok,
            $product->harga_jual,
            "Penambahan stok produk: {$product->kode_produk}"
        );
    
        // Ambil akun yang terlibat dalam transaksi
        $debetAccount = Account::where('code', '103')->first(); // Ganti dengan akun yang sesuai
        $creditAccount = Account::where('code', '401')->first(); // Ganti dengan akun yang sesuai
        $profitLossAccount = Account::where('code', '203')->first(); // Ambil akun laba rugi sebagai revenue
    
        // Cek apakah akun ditemukan
        if (!$debetAccount || !$creditAccount || !$profitLossAccount) {
            return response()->json(['message' => 'Akun debet, kredit, atau laba rugi tidak ditemukan.'], 400);
        }
    
        // Log akun untuk debugging
        \Log::info('Debet Account:', ['account' => $debetAccount]);
        \Log::info('Credit Account:', ['account' => $creditAccount]);
        \Log::info('Profit/Loss (Revenue) Account:', ['account' => $profitLossAccount]);
    
        // Update saldo bulanan dan akun menggunakan metode dari model Transaction
        $amount = $jumlahStok * $product->harga_jual;
        
        Transaction::updateMonthlyBalance($debetAccount, $amount, 'debit');
        Transaction::updateMonthlyBalance($creditAccount, $amount, 'credit');
    
        // Update akun laba rugi sebagai revenue (Kredit karena pendapatan bertambah)
        Transaction::updateMonthlyBalance($profitLossAccount, $amount, 'credit'); // Revenue di-update sebagai kredit
    
        return response()->json([
            'message' => 'Stok berhasil ditambahkan, transaksi dan laba rugi tercatat',
            'product' => $product
        ]);
    }
    
    public function reduceStock(Request $request, $id)
{
    \Log::info('Request data: ', $request->all());

    $product = Produk::findOrFail($id);
    $jumlahStok = $request->input('jumlah');

    if ($jumlahStok <= 0) {
        return response()->json(['message' => 'Jumlah stok harus lebih besar dari nol.'], 400);
    }

    // Cek apakah stok cukup untuk dikurangi
    if ($product->stok < $jumlahStok) {
        return response()->json(['message' => 'Jumlah stok tidak mencukupi.'], 400);
    }

    $product->stok -= $jumlahStok;

    if (!$product->save()) {
        \Log::error('Gagal mengurangi stok untuk produk ID: ' . $product->id_produk);
        return response()->json(['message' => 'Gagal mengurangi stok.'], 500);
    }

    // Catat transaksi pengurangan stok
    $transaction = Transaction::storeStockTransaction(
        Transaction::CATEGORY_STOCK_REDUCE, // Kategori pengurangan stok (kode 023)
        $jumlahStok,
        $product->harga_jual,
        "Pengurangan stok produk: {$product->kode_produk}"
    );

    // Ambil akun yang terlibat dalam transaksi
    $debetAccount = Account::where('code', '501')->first(); // Ganti dengan akun yang sesuai
    $creditAccount = Account::where('code', '103')->first(); // Ganti dengan akun yang sesuai
    $profitLossAccount = Account::where('code', '203')->first(); // Ambil akun laba rugi sebagai revenue

    // Cek apakah akun ditemukan
    if (!$debetAccount || !$creditAccount || !$profitLossAccount) {
        return response()->json(['message' => 'Akun debet, kredit, atau laba rugi tidak ditemukan.'], 400);
    }

    // Log akun untuk debugging
    \Log::info('Debet Account:', ['account' => $debetAccount]);
    \Log::info('Credit Account:', ['account' => $creditAccount]);
    \Log::info('Profit/Loss (Revenue) Account:', ['account' => $profitLossAccount]);

    // Update saldo bulanan dan akun menggunakan metode dari model Transaction
    $amount = $jumlahStok * $product->harga_jual;
    
    Transaction::updateMonthlyBalance($debetAccount, $amount, 'debit');
    Transaction::updateMonthlyBalance($creditAccount, $amount, 'credit');

    // Update akun laba rugi sebagai revenue (Kredit karena pendapatan bertambah)
    Transaction::updateMonthlyBalance($profitLossAccount, $amount, 'debit'); // Revenue di-update sebagai kredit

    return response()->json([
        'message' => 'Stok berhasil dikurangi, transaksi dan laba rugi tercatat',
        'product' => $product
    ]);
}

}



