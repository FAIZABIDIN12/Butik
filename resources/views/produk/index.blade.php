@extends('layouts.master')

@section('title')
    Daftar Produk
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Produk</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
            <div class="btn-group">
    <button onclick="addForm('{{ route('produk.store') }}')" 
            class="btn btn-primary btn-lg btn-flat" 
            style=" margin-right: 10px;">
        <i class="fa fa-plus-circle"></i> Tambah
    </button>
    <button onclick="deleteSelected('{{ route('produk.delete_selected') }}')" 
            class="btn btn-danger btn-lg btn-flat" 
            style="background-color: #dc3545; border-color: #dc3545; color: white; margin-right: 10px;">
        <i class="fa fa-trash"></i> Hapus
    </button>
    <button onclick="cetakBarcode('{{ route('produk.cetak_barcode') }}')" 
            class="btn btn-info btn-lg btn-flat" 
            style="background-color: #17a2b8; border-color: #17a2b8; color: white;">
        <i class="fa fa-barcode"></i> Cetak Barcode
    </button>
    <button onclick="showImportModal()" 
            class="btn btn-warning btn-lg btn-flat" 
            style="background-color: #ffc107; border-color: #ffc107; color: white; margin-left: 10px;">
        <i class="fa fa-upload"></i> Import
    </button>
</div>

            </div>
            <div class="box-body table-responsive">
                <form action="" method="post" class="form-produk">
                    @csrf
                    <table class="table table-striped table-bordered">
                        <thead>
                            <th width="5%">
                                <input type="checkbox" name="select_all" id="select_all">
                            </th>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Merk</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Diskon</th>
                            <th>Stok</th>
                            <th>Rak</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Produk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="importForm" action="{{ route('produk.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Pilih file Excel untuk diimpor:</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@includeIf('produk.reduce_stock_modal')
@includeIf('produk.add_stock_modal')
@includeIf('produk.form')
@endsection

@push('scripts')
<script>
    let table;

    $(function () {
        table = $('.table').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('produk.data') }}',
            },
            columns: [
                {data: 'select_all', searchable: false, sortable: false},
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'nama_produk'},
                {data: 'nama_kategori'},
                {data: 'merk'},
                {data: 'harga_beli'},
                {data: 'harga_jual'},
                {data: 'diskon'},
                {data: 'stok'},
                {data: 'rak'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (! e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menyimpan data');
                        return;
                    });
            }
        });

        $('[name=select_all]').on('click', function () {
            $(':checkbox').prop('checked', this.checked);
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_produk]').focus();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_produk]').focus();

        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama_produk]').val(response.nama_produk);
                $('#modal-form [name=id_kategori]').val(response.id_kategori);
                $('#modal-form [name=merk]').val(response.merk);
                $('#modal-form [name=harga_beli]').val(response.harga_beli);
                $('#modal-form [name=harga_jual]').val(response.harga_jual);
                $('#modal-form [name=diskon]').val(response.diskon);
                $('#modal-form [name=stok]').val(response.stok);
                $('#modal-form [name=rak]').val(response.rak);
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data');
                return;
            });
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function deleteSelected(url) {
        if ($('input:checked').length > 1) {
            if (confirm('Yakin ingin menghapus data terpilih?')) {
                $.post(url, $('.form-produk').serialize())
                    .done((response) => {
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menghapus data');
                        return;
                    });
            }
        } else {
            alert('Pilih data yang akan dihapus');
            return;
        }
    }

    function cetakBarcode(url) {
        if ($('input:checked').length < 1) {
            alert('Pilih data yang akan dicetak');
            return;
        } else if ($('input:checked').length < 3) {
            alert('Pilih minimal 3 data untuk dicetak');
            return;
        } else {
            $('.form-produk')
                .attr('target', '_blank')
                .attr('action', url)
                .submit();
        }
    }

    function addStockForm(url, produkId) {
        $('#modal-add-stock').modal('show'); // Tampilkan modal
        $('#modal-add-stock form').attr('action', url); // Set action ke URL yang diberikan
        $('#modal-add-stock [name=jumlah]').val(''); // Reset input jumlah
        $('#modal-add-stock #produk_id').val(produkId); // Isi produk_id
    }

    $('#modal-add-stock form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'put',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: (response) => {    
                $('#modal-add-stock').modal('hide');
                table.ajax.reload();
            },
            error: (errors) => {
                alert('Tidak dapat menambah stock');
                console.log(errors); // Untuk debugging
            }
        });
    });

    function reduceStockForm(url, produkId) {
        $('#modal-reduce-stock').modal('show'); // Tampilkan modal
        $('#modal-reduce-stock form').attr('action', url); // Set action ke URL yang diberikan
        $('#modal-reduce-stock [name=jumlah]').val(''); // Reset input jumlah
        $('#modal-reduce-stock #produk_id').val(produkId); // Isi produk_id
    }

    $('#modal-reduce-stock form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'put', // Ubah ke PUT jika menggunakan RESTful
            url: $(this).attr('action'), // Ambil action dari form
            data: $(this).serialize(), // Ambil data dari form
            success: (response) => {
                $('#modal-reduce-stock').modal('hide'); // Sembunyikan modal
                table.ajax.reload(); // Reload tabel untuk menampilkan stok terbaru
                alert('Stok berhasil dikurangi'); // Tampilkan pesan sukses
            },
            error: (errors) => {
                alert('Tidak dapat mengurangi stok');
                console.log(errors); // Untuk debugging
            }
        });
    });
    function showImportModal() {
    $('#importModal').modal('show');
}
</script>
@endpush
