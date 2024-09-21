@extends('layouts.master')

@section('title', 'Cashflow')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"></h3>
                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addCashflowModal">
                    Tambah Transaksi
                </button>
            </div>
            <div class="box-body">
            <table class="table table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Uraian</th>
            <th>Jenis Transaksi</th>
            <th>Uang Masuk</th>
            <th>Uang Keluar</th>
            <th>Saldo Saat Ini</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cashflows as $cashflow)
        <tr>
            <td>{{ $cashflow->date }}</td>
            <td>{{ $cashflow->description }}</td>
            <td>
                @if($cashflow->transaction_type == 'in')
                    {{ $cashflow->category->name ?? 'N/A' }} (Uang Masuk)
                @elseif($cashflow->transaction_type == 'out')
                    {{ $cashflow->category->name ?? 'N/A' }} (Uang Keluar)
                @endif
            </td>
            <td>{{ $cashflow->transaction_type == 'in' ? 'Rp. ' . number_format($cashflow->amount, 0, ',', '.') : '' }}</td>
            <td>{{ $cashflow->transaction_type == 'out' ? 'Rp. ' . number_format($cashflow->amount, 0, ',', '.') : '' }}</td>
            <td>{{ 'Rp. ' . number_format($cashflow->current_balance, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Cashflow -->
<div class="modal fade" id="addCashflowModal" tabindex="-1" role="dialog" aria-labelledby="addCashflowModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCashflowModalLabel">Tambah Transaksi Cashflow</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cashflows.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">Tanggal</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Uraian</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="form-group">
                        <label for="category_code">Kategori</label>
                        <select class="form-control" id="category_code" name="category_code" required onchange="updateTransactionType()">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->code }}" data-type="{{ $category->type }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Transaksi</label><br>
                        <div style="display: flex; align-items: center;">
                            <div class="form-check" style="margin-right: 20px;">
                                <input class="form-check-input" type="radio" name="transaction_type" id="transaction_in" value="in" required>
                                <label class="form-check-label" for="transaction_in">
                                    Uang Masuk
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="transaction_type" id="transaction_out" value="out" required>
                                <label class="form-check-label" for="transaction_out">
                                    Uang Keluar
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="amount">Jumlah Uang</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateTransactionType() {
    const categorySelect = document.getElementById('category_code');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const transactionType = selectedOption.getAttribute('data-type');

    // Set the corresponding radio button based on the category type
    if (transactionType === 'in') {
        document.getElementById('transaction_in').checked = true;
    } else if (transactionType === 'out') {
        document.getElementById('transaction_out').checked = true;
    }
}
</script>
@endpush
