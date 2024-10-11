@extends('layouts.master')

@section('title', 'Daftar Kategori')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
        <div class="box-header">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary btn-lg btn-flat" data-toggle="modal" data-target="#addCategoryModal">
            <i class="fa fa-plus-circle"></i> Tambah Kategori
        </button>
    </div>
</div>

            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tipe</th>
                            <th>Nama</th>
                            <th>Rek Debit</th>
                            <th>Rek Kredit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->code }}</td>
                            <td>{{ $category->type }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $accounts[$category->debit_account_code]->name ?? 'Tidak Ditemukan' }}</td>
                            <td>{{ $accounts[$category->credit_account_code]->name ?? 'Tidak Ditemukan' }}</td>
                            <td>
                                <button class="btn btn-warning" data-toggle="modal" data-target="#editCategoryModal" 
                                        data-code="{{ $category->code }}" 
                                        data-type="{{ $category->type }}" 
                                        data-name="{{ $category->name }}" 
                                        data-debit="{{ $category->debit_account_code }}" 
                                        data-credit="{{ $category->credit_account_code }}">
                                    Edit
                                </button>
                                <form action="{{ route('categories.destroy', $category->code) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="code">Kode</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Tipe</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="in">In</option>
                            <option value="out">Out</option>
                            <option value="mutation">Mutation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="debit_account_code">Rek Debit</label>
                        <select class="form-control" id="debit_account_code" name="debit_account_code">
                            <option value="">Pilih Rek Debit</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->code }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="credit_account_code">Rek Kredit</label>
                        <select class="form-control" id="credit_account_code" name="credit_account_code">
                            <option value="">Pilih Rek Kredit</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->code }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_code">Kode</label>
                        <input type="text" class="form-control" id="edit_code" name="code" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_type">Tipe</label>
                        <select class="form-control" id="edit_type" name="type" required>
                            <option value="in">In</option>
                            <option value="out">Out</option>
                            <option value="mutation">Mutation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Nama</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_debit_account_code">Rek Debit</label>
                        <select class="form-control" id="edit_debit_account_code" name="debit_account_code">
                            <option value="">Pilih Rek Debit</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->code }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_credit_account_code">Rek Kredit</label>
                        <select class="form-control" id="edit_credit_account_code" name="credit_account_code">
                            <option value="">Pilih Rek Kredit</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->code }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $('#editCategoryModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var code = button.data('code');
        var type = button.data('type');
        var name = button.data('name');
        var debit = button.data('debit');
        var credit = button.data('credit');

        var modal = $(this);
        modal.find('#edit_code').val(code);
        modal.find('#edit_type').val(type);
        modal.find('#edit_name').val(name);
        modal.find('#edit_debit_account_code').val(debit);
        modal.find('#edit_credit_account_code').val(credit);
        modal.find('form').attr('action', '/categories/' + code);
    });
</script>
@endpush
