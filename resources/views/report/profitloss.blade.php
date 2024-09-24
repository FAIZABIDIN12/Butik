@extends('layouts.master')

@section('title')
    Laporan Laba Rugi
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Laporan Laba Rugi</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Laba Rugi</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Akun</th>
                            <th>Deskripsi</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
    <tr>
        <td>1</td>
        <td>103</td>
        <td>Pendapatan (HPP BD)</td>
        <td id="total_income">{{ $total_income }}</td> <!-- Pre-populated value -->
    </tr>
    <tr>
        <td>2</td>
        <td>102</td>
        <td>HPP Barang Dagang</td>
        <td id="total_hpp">{{ $total_hpp }}</td> <!-- Pre-populated value -->
    </tr>
    <tr>
        <td colspan="3"><strong>Total Laba Rugi</strong></td>
        <td id="profit_loss"><strong>{{ $profit_loss }}</strong></td> <!-- Pre-populated value -->
    </tr>
</tbody>

                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('pengeluaran.form') <!-- Include form for adding expenses -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Fetch the profit-loss report data
        $.ajax({
            url: '{{ route('report.profit_loss') }}',  // Ensure this route is correct
            type: 'GET',
            dataType: 'json', // Ensure the response is treated as JSON
            success: function(response) {
                // Populate the table cells with the values from the response
                $('#total_income').text(response.total_income);
                $('#total_hpp').text(response.total_hpp);
                $('#profit_loss').text(response.profit_loss);
            },
            error: function(xhr, status, error) {
                // Log the error for debugging
                console.error('Error fetching profit/loss data:', error);
                alert('Gagal mengambil data laporan laba rugi.');
            }
        });
    });
</script>
@endpush
