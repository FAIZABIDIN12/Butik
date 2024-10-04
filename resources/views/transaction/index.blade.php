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
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addTransactionModal">Tambah Transaksi</button>
                    </div>
                </div>

                <div class="box-body table-responsive">
                    <table class="table table-striped table-bordered" id="transactions-table">
                        <thead>
                            <tr>
                                <th>Date</th>
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
                                    <td>{{ $transaction->transaction_at }}</td>
                                    <td>{{ $transaction->category ? $transaction->category->code : 'N/A' }}</td>
                                    <td>{{ $transaction->description }}</td>
                                    <td>{{ $transaction->category ? $transaction->category->name : 'N/A' }}</td>
                                    <td>{{ number_format($transaction->debit, 2) }}</td>
                                    <td>{{ number_format($transaction->credit, 2) }}</td>
                                    <td>{{ number_format($transaction->saldo, 2) }}</td>
                                    <td>
                                        <button class="btn btn-warning edit" data-id="{{ $transaction->id }}">Edit</button>
                                        <!-- <button class="btn btn-danger delete" data-id="{{ $transaction->id }}">Delete</button> -->
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
    console.log(`/transaction/${transactionId}/edit`); // Log the URL

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
    console.log($(this).serialize()); // Log the form data

    $.ajax({
        url: `/transaction/${transactionId}`,
        method: 'PUT',
        data: $(this).serialize(),
        success: function() {
            location.reload(); // Reload the page to see the changes
        },
        error: function(jqXHR) {
            console.error(jqXHR.responseText); // Log the error response
            alert('Error updating transaction.'); // Display a generic error message
        }
    });
});

  // Handle delete button click
$('.delete').on('click', function() {
    var transactionId = $(this).data('id');
    if (confirm('Are you sure you want to delete this transaction?')) {
        $.ajax({
            url: `/transaction/${transactionId}`, // Ensure the URL is correct
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}' // Include CSRF token for security
            },
            success: function(response) {
                location.reload(); // Reload the page to see the changes
            },
            error: function(xhr, status, error) {
                alert('Error deleting transaction: ' + xhr.responseText); // Provide more information
            }
        });
    }
});

});
</script>
@endpush
