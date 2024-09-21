@extends('layouts.master')

@section('title')
    Daftar Akun
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Akun</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"></h3>
                <!-- Tombol Tambah Data -->
                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addAccountModal">
                    Create New Account
                </button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-accounts">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Initial Balance</th>
                            <th>Current Balance</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $index => $account)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $account->code }}</td>
                            <td>{{ $account->name }}</td>
                            <td>
                                @if($account->position == 'asset')
                                    Aktiva
                                @elseif($account->position == 'liability')
                                    Pasiva
                                @elseif($account->position == 'revenue')
                                    Laba Rugi
                                @elseif($account->position == 'expense')
                                    Beban Biaya
                                @endif
                            </td>

                            <td>{{ $account->initial_balance }}</td>
                            <td>{{ $account->current_balance }}</td>
                            <td>
                                <a href="#" class="btn btn-warning btn-sm" data-toggle="modal" 
                                   data-target="#editAccountModal" 
                                   data-code="{{ $account->code }}" 
                                   data-name="{{ $account->name }}" 
                                   data-position="{{ $account->position }}" 
                                   data-initial_balance="{{ $account->initial_balance }}" 
                                   data-current_balance="{{ $account->current_balance }}">
                                    Edit
                                </a>
                                <button class="btn btn-danger btn-sm" data-toggle="modal" 
                                        data-target="#deleteAccountModal" 
                                        data-code="{{ $account->code }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah Akun -->
<div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAccountModalLabel">Tambah Akun Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="code">Kode</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="position">Posisi</label>
                        <select class="form-control" id="position" name="position" required>
                            <option value="asset">Aktiva</option>
                            <option value="liability">Pasiva</option>
                            <option value="revenue">Laba Rugi </option>
                            <option value="expense">Beban Biaya </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="initial_balance">Saldo Awal</label>
                        <input type="number" class="form-control" id="initial_balance" name="initial_balance" required>
                    </div>
                    <div class="form-group">
                        <label for="current_balance">Saldo Saat Ini</label>
                        <input type="number" class="form-control" id="current_balance" name="current_balance" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Edit Akun -->
<div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccountModalLabel">Edit Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editAccountForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_code">Kode</label>
                        <input type="text" class="form-control" id="edit_code" name="code" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Nama</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <select class="form-control" id="edit_position" name="position" required>
                    <option value="asset" {{ old('position') == 'asset' ? 'selected' : '' }}>Aktiva</option>
                        <option value="liability" {{ old('position') == 'liability' ? 'selected' : '' }}>Pasiva</option>
                        <option value="revenue" {{ old('position') == 'revenue' ? 'selected' : '' }}>Laba Rugi</option>
                        <option value="expense" {{ old('position') == 'expense' ? 'selected' : '' }}>Beban Biaya</option>
                    </select>

                    <div class="form-group">
                        <label for="edit_initial_balance">Saldo Awal</label>
                        <input type="number" class="form-control" id="edit_initial_balance" name="initial_balance" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_current_balance">Saldo Saat Ini</label>
                        <input type="number" class="form-control" id="edit_current_balance" name="current_balance" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Hapus Akun -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Hapus Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="deleteAccountForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus akun ini?</p>
                    <input type="hidden" id="delete_code" name="code">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

@includeIf('accounts.detail') <!-- Pastikan view detail ada -->
@endsection

@push('scripts')
@push('scripts')
<script>
    $(function () {
        // Untuk mengatur data pada modal edit
        $('#editAccountModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var code = button.data('code');
            var name = button.data('name');
            var position = button.data('position');
            var initial_balance = button.data('initial_balance');
            var current_balance = button.data('current_balance');

            var modal = $(this);
            modal.find('#edit_code').val(code);
            modal.find('#edit_name').val(name);
            modal.find('#edit_position').val(position);
            modal.find('#edit_initial_balance').val(initial_balance);
            modal.find('#edit_current_balance').val(current_balance);
            modal.find('#editAccountForm').attr('action', '/accounts/' + code);
        });

        // Untuk mengatur modal hapus
        $('#deleteAccountModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var code = button.data('code');
            var modal = $(this);
            modal.find('#delete_code').val(code);
            modal.find('#deleteAccountForm').attr('action', '/accounts/' + code);
        });
    });
</script>


@endpush