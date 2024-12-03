@extends('layouts.master')

@section('title')
    Daftar Penjualan
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Penjualan</li>
@endsection

@section('content')
<form action="{{ route('export.penjualan') }}" method="GET">
    <div class="row mb-4">
        <!-- Start Date Filter -->
        <div class="col-md-4 mb-3">
            <input type="date" name="start_date" id="start_date" class="form-control form-control-lg" required style="height: 50px;">
        </div>
        
        <!-- End Date Filter -->
        <div class="col-md-4 mb-3">
            <input type="date" name="end_date" id="end_date" class="form-control form-control-lg" required style="height: 50px;">
        </div>
        
        <!-- Export Button -->
        <div class="col-md-4 mb-3 d-flex align-items-end">
            <button type="submit" class="btn btn-success btn-lg btn-flat" style="height: 50px; width: 50%;">
                <i class="fa fa-download"></i> Export to Excel
            </button>
        </div>
    </div>
</form>
<br>

<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Kode Member</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Diskon</th>
                        <th>Payment</th>
                        <th>Total Bayar</th>
                        <th>Kasir</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody>
                        {{-- Data tables would populate the rows here --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('penjualan.detail')
@endsection

@push('scripts')
<script>
    let table, table1;

    $(function () {
        table = $('.table-penjualan').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('penjualan.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
                {data: 'kode_member'},
                {data: 'total_item'},
                {data: 'total_harga'},
                {data: 'diskon'},
                {data: 'metode_pembayaran'},
                {data: 'bayar'},
                {data: 'kasir'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'nama_produk'},
                {data: 'harga_jual'},
                {data: 'jumlah'},
                {data: 'subtotal'},
            ]
        })
    });
    function showDetail(url) {
    $('#modal-detail').modal('show');  // Menampilkan modal
    table1.ajax.url(url).load();        // Memuat data ke dalam DataTable
}

    // Add event listener for the Download Button
    $('#downloadBtn').on('click', function() {
        // Get the selected start date and end date
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();

        // Validate the dates before submitting
        if (!start_date || !end_date) {
            alert('Harap pilih tanggal mulai dan tanggal akhir!');
            return;
        }

        // Build the URL with query parameters
        var url = '{{ route('export.penjualan') }}' + '?start_date=' + start_date + '&end_date=' + end_date;

        // Redirect the user to the export route with the selected dates
        window.location.href = url;
    });

    function deleteData(url) {
        // Konfirmasi sebelum menghapus data
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: url,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Token CSRF untuk keamanan
                },
                success: function (response) {
                    alert('Data berhasil dihapus.');
                    $('.table-penjualan').DataTable().ajax.reload(); // Reload DataTable
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
                    console.error(xhr.responseText);
                }
            });
        }
    }
</script>
@endpush
