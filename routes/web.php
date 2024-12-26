<?php

use App\Http\Controllers\{
    DashboardController,
    KategoriController,
    LaporanController,
    ProdukController,
    MemberController,
    PengeluaranController,
    PembelianController,
    PembelianDetailController,
    PenjualanController,
    PenjualanDetailController,
    SettingController,
    SupplierController,
    UserController,
    AccountController,
    CategoryController,
    TransactionController,
    ReportController,
    PaymentController,
    ExportPenjualanController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::group(['middleware' => 'level:1,2'], function () {
        Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
        Route::post('/kategori/import', [KategoriController::class, 'import'])->name('kategori.import');
        Route::resource('/kategori', KategoriController::class);

        Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
        Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
        Route::post('/produk/cetak-barcode', [ProdukController::class, 'cetakBarcode'])->name('produk.cetak_barcode');
        Route::put('produk/add_stock/{id}', [ProdukController::class, 'addStock'])->name('produk.add_stock');
        Route::put('/produk/{id}/reduce-stock', [ProdukController::class, 'reduceStock'])->name('produk.reduce_stock');
        Route::post('/produk/import', [ProdukController::class, 'import'])->name('produk.import');

        Route::resource('/produk', ProdukController::class);

        Route::get('/member/data', [MemberController::class, 'data'])->name('member.data');
        Route::post('/member/cetak-member', [MemberController::class, 'cetakMember'])->name('member.cetak_member');
        Route::resource('/member', MemberController::class);

        Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
        Route::resource('/supplier', SupplierController::class);

        Route::get('/pengeluaran/data', [PengeluaranController::class, 'data'])->name('pengeluaran.data');
        Route::resource('/pengeluaran', PengeluaranController::class);

        Route::get('/pembelian/data', [PembelianController::class, 'data'])->name('pembelian.data');
        Route::get('/pembelian/{id}/create', [PembelianController::class, 'create'])->name('pembelian.create');
        Route::resource('/pembelian', PembelianController::class)
            ->except('create');

        Route::get('/pembelian_detail/{id}/data', [PembelianDetailController::class, 'data'])->name('pembelian_detail.data');
        Route::get('/pembelian_detail/loadform/{diskon}/{total}', [PembelianDetailController::class, 'loadForm'])->name('pembelian_detail.load_form');
        Route::resource('/pembelian_detail', PembelianDetailController::class)
            ->except('create', 'show', 'edit');

        Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
        Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
    });

    Route::group(['middleware' => 'level:1,2'], function () {
        Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');
        Route::post('/transaksi/simpan', [PenjualanController::class, 'store'])->name('transaksi.simpan');
        Route::get('/transaksi/selesai', [PenjualanController::class, 'selesai'])->name('transaksi.selesai');
        Route::get('/transaksi/nota-kecil', [PenjualanController::class, 'notaKecil'])->name('transaksi.nota_kecil');
        Route::get('/transaksi/nota-besar', [PenjualanController::class, 'notaBesar'])->name('transaksi.nota_besar');
        Route::get('/transaksi/{id}/data', [PenjualanDetailController::class, 'data'])->name('transaksi.data');
        Route::get('/transaksi/loadform/{diskon}/{total}/{diterima}', [PenjualanDetailController::class, 'loadForm'])->name('transaksi.load_form');
        Route::post('/transactions/import', [TransactionController::class, 'import'])->name('transactions.import');
        Route::resource('/transaksi', PenjualanDetailController::class)
            ->except('create', 'show', 'edit');
    });

    Route::group(['middleware' => 'level:1'], function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/data/{awal}/{akhir}', [LaporanController::class, 'data'])->name('laporan.data');
        Route::get('/laporan/pdf/{awal}/{akhir}', [LaporanController::class, 'exportPDF'])->name('laporan.export_pdf');

        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::resource('/user', UserController::class);

        Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::get('/setting/first', [SettingController::class, 'show'])->name('setting.show');
        Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
    });
    Route::group(['middleware' => 'level:1'], function () {
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{code}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{code}', [AccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{code}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    });

    Route::group(['middleware' => 'level:1'], function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{code}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{code}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{code}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/reportjurnal', [CategoryController::class, 'report'])->name('categories.report');
    });

    Route::group(['middleware' => 'level:1,2'], function () {
        // Rute yang bisa diakses oleh level 1 dan level 2
        Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
        Route::post('/transaction/store', [TransactionController::class, 'store'])->name('transaction.store');
    });

    Route::group(['middleware' => 'level:1'], function () {
        // Rute khusus untuk level 1
        Route::delete('/transaction/{id}', [TransactionController::class, 'destroy'])->name('transaction.destroy');
        Route::post('import', [TransactionController::class, 'import'])->name('transaction.import');
        Route::get('data', [TransactionController::class, 'getData'])->name('transaction.data');
        Route::get('labarugi', [TransactionController::class, 'showLabaRugi'])->name('transaction.labarugi');
        Route::get('/transaction/{id}/edit', [TransactionController::class, 'edit'])->name('transaction.edit');
        Route::put('/transaction/{id}', [TransactionController::class, 'update'])->name('transaction.update');
        Route::get('/journal', [TransactionController::class, 'generateJournalReport'])->name('transaction.journal_report');
        Route::get('/journal-report', [TransactionController::class, 'journalReport'])->name('journal.report');
    });

    Route::get('export-penjualan', [ExportPenjualanController::class, 'export'])->name('export.penjualan');
    // Route untuk melihat laporan saldo
    Route::get('/report/penjualan', [PaymentController::class, 'index'])->name('report.penjualan');

    // Route untuk menarik saldo non-tunai
    Route::post('/payment/withdraw', [PaymentController::class, 'withdraw'])->name('payment.withdraw');

    Route::get('/profit-loss-report', [ReportController::class, 'profitLoss'])->name('report.profit_loss');
    Route::get('/balance_sheet', [ReportController::class, 'balanceSheet'])->name('report.balance_sheet');
});

Route::group(['middleware' => 'level:1,2'], function () {
    Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
    Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');
});
