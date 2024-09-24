@extends('layouts.master')

@section('title')
    Daftar Transaksi     
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Transaksi</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12 mb-1">
        <div class="box">
            <div class="box-body table-responsive">
                <h1 class="mb-4">Daftar Transaksi</h1>
                <div class="row align-items-center mb-4">
    <div class="col-md-8">
        <form id="filter-form" method="GET" action="{{ route('transaction.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDateFormatted }}">
                </div>
                <div class="col-md-4">
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDateFormatted }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addTransactionModal">Tambah Transaksi</button>
    </div>
</div>
<div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="transactions-table">
                <thead>
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>Deskripsi</th>
        <th>Kategori</th>
        <th>Debit</th>
        <th>Kredit</th>
        <th>Saldo</th>
        <th>Aksi</th>
    </tr>
</thead>
<tbody>
    @foreach ($transactions as $transaction)
        <tr>
            <td>{{ $transaction->id }}</td>
            <td>{{ $transaction->category ? $transaction->category->code : 'N/A' }}</td>
            <td>{{ $transaction->description }}</td>
            <td>{{ $transaction->category ? $transaction->category->name : 'N/A' }}</td>
            <!-- <td>{{ $transaction->type }}</td> Display transaction type -->
            <td>{{ number_format($transaction->debit, 2) }}</td>
            <td>{{ number_format($transaction->credit, 2) }}</td>
            <td>{{ number_format($transaction->saldo, 2) }}</td>
            <td>
                <button class="btn btn-warning edit" data-id="{{ $transaction->id }}">Edit</button>
                <button class="btn btn-danger delete" data-id="{{ $transaction->id }}">Delete</button>
            </td>
        </tr>
    @endforeach
</tbody>
</table>
</div>
<div>
    <h3>Total Saldo: {{ number_format($saldo, 2) }}</h3>
</div>



                <!-- Modal Tambah Transaksi -->
                <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addTransactionModalLabel">Tambah Transaksi</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="addTransactionForm" action="{{ route('transaction.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="description">Deskripsi</label>
                                        <input type="text" class="form-control" name="description" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="category_code">Kategori</label>
                                        <select name="category_code" class="form-control" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->code }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="nominal">Nominal</label>
                                        <input type="text" class="form-control" name="nominal" required>
                                    </div>
                                    <input type="hidden" name="account_code" value="{{ request('account') }}">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        $('#transactions-table').DataTable();
    });
</script>
@endsection
@endsection
