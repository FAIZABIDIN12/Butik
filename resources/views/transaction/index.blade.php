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
            <div class="row">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <!-- Form Filter -->
        <form id="filter-form" method="GET" action="{{ route('transaction.index') }}" class="d-flex flex-grow-1">
            <div class="row w-100">
                <!-- Start Date -->
                <div class="col-md-3 mb-1">
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-lg" value="{{ $startDateFormatted }}" style="height: 50px;">
                </div>
                <!-- End Date -->
                <div class="col-md-3 mb-1">
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-lg" value="{{ $endDateFormatted }}" style="height: 50px;">
                </div>
                <!-- Filter Button -->
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100" style="height: 50px;">
                        <i class="fa fa-filter"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#addTransactionModal" style="height: 50px;">
                        <i class="fa fa-plus-circle"></i> Transaksi
                    </button>
                    <button type="button" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#importTransactionModal" style="height: 50px;">
                        <i class="fa fa-upload"></i> Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
                <div style="margin-top: 20px;"></div>
                <table class="table table-striped table-bordered table-hover" id="transactions-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode</th>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_at }}</td>
                                <td>{{ $transaction->category ? $transaction->category->code : 'N/A' }}</td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ $transaction->category ? $transaction->category->name : 'N/A' }}</td>
                                <td>{{ number_format($transaction->debit, 2) }}</td>
                                <td>{{ number_format($transaction->credit, 2) }}</td>
                                <td>{{ number_format($transaction->saldo, 2) }}</td>
                                <td >
                                    <button class="btn btn-warning btn-sm edit" data-id="{{ $transaction->id }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete" data-id="{{ $transaction->id }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    <h3>Total Saldo: <span class="text-success">{{ number_format($saldo, 2) }}</span></h3>
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
<!-- Import Transaction Modal -->
<div class="modal fade" id="importTransactionModal" tabindex="-1" role="dialog" aria-labelledby="importTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importTransactionModalLabel">Import Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('transactions.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="file">Upload File Excel</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Import</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

                <!-- Modal Edit Transaksi -->
                <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaksi</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="editTransactionForm" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="edit_description">Deskripsi</label>
                                        <input type="text" class="form-control" name="description" id="edit_description" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_category_code">Kategori</label>
                                        <select name="category_code" class="form-control" id="edit_category_code" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->code }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_nominal">Nominal</label>
                                        <input type="text" class="form-control" name="nominal" id="edit_nominal" required>
                                    </div>
                                    <input type="hidden" name="transaction_id" id="transaction_id">
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#transactions-table').DataTable();

    // Handle edit button click
    $('.edit').on('click', function() {
        var transactionId = $(this).data('id');

        // Fetch transaction data
        $.ajax({
            url: `/transaction/${transactionId}/edit`,
            method: 'GET',
            success: function(data) {
                $('#transaction_id').val(data.transaction.id);
                $('#edit_description').val(data.transaction.description);
                $('#edit_category_code').val(data.transaction.category_code);
                $('#edit_nominal').val(data.transaction.nominal);
                $('#editTransactionModal').modal('show');
            },
            error: function() {
                alert('Error fetching transaction data.');
            }
        });
    });

    $('#editTransactionForm').on('submit', function(e) {
        e.preventDefault();
        var transactionId = $('#transaction_id').val();

        $.ajax({
            url: `/transaction/${transactionId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function() {
                location.reload(); // Reload the page to see the changes
            },
            error: function(jqXHR) {
                alert('Error updating transaction.'); // Display a generic error message
            }
        });
    });

    // Handle delete button click
    $('.delete').on('click', function() {
        var transactionId = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
            $.ajax({
                url: `/transaction/${transactionId}`,
                method: 'DELETE',
                success: function() {
                    location.reload(); // Reload the page to see the changes
                },
                error: function() {
                    alert('Error deleting transaction.');
                }
            });
        }
    });
});
</script>
@endpush
