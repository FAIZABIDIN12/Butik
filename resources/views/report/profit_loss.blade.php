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
            <div class="box-body table-responsive with-border">
                <!-- Form untuk filter berdasarkan bulan -->
                <form action="{{ route('report.profit_loss') }}" method="GET">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <select class="form-control form-control-lg" id="month" name="month" required style="height: 50px;">
                                <option value="">Pilih Bulan</option>
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" {{ request('month') == str_pad($month, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select class="form-control form-control-lg" id="year" name="year" required style="height: 50px;">
                                <option value="">Pilih Tahun</option>
                                @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-lg btn-flat" style="height: 50px; width: 50%;">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <br>

                <!-- Tabel Pendapatan -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h4 class="font-weight-bold" style="font-size: 24px; text-align: center;">Pendapatan</h4>
                        <table id="profitTable" class="table table-striped table-bordered table-hover" style="font-size: 16px;">
                            <thead>
                                <tr>
                                    <th><h4>Kode</h4></th>
                                    <th><h4>Nama Akun</h4></th>
                                    <th><h4>Saldo</h4></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomeAccounts as $item)
                                    <tr>
                                        <td>{{ $item['account']->code }}</td>
                                        <td>{{ $item['account']->name }}</td>
                                        <td>{{ number_format($item['balance'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabel Beban -->
                    <div class="col-md-6 mb-4">
                        <h4 class="font-weight-bold" style="font-size: 24px; text-align: center;">Beban/Biaya</h4>
                        <table id="lossTable" class="table table-striped table-bordered table-hover" style="font-size: 16px;">
                            <thead>
                                <tr>
                                    <th><h4>Kode</h4></th>
                                    <th><h4>Nama Akun</h4></th>
                                    <th><h4>Saldo</h4></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outcomeAccounts as $item)
                                    <tr>
                                        <td>{{ $item['account']->code }}</td>
                                        <td>{{ $item['account']->name }}</td>
                                        <td>{{ number_format($item['balance'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Total Laba Rugi -->
                <div class="mb-5"></div> <!-- Tambahkan jarak -->
                <table class="table table-striped table-bordered" style="font-size: 18px; color: green;">
                    <thead>
                        <tr>
                            <th colspan="3" class="text-left">
                                <div>
                                    <span><strong>Total Laba Rugi: Rp.</strong></span>
                                    <span><strong>{{ number_format($totalIncome - $totalOutcome, 0, ',', '.') }}</strong></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">

<script>
$(document).ready(function() {
    $('#profitTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "lengthChange": true,
        "pageLength": 10,
        "language": {
            "lengthMenu": "Tampilkan _MENU_ entri",
            "search": "Cari:",
            "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
            "infoEmpty": "Menampilkan 0 hingga 0 dari 0 entri",
            "paginate": {
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });

    $('#lossTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "lengthChange": true,
        "pageLength": 10,
        "language": {
            "lengthMenu": "Tampilkan _MENU_ entri",
            "search": "Cari:",
            "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
            "infoEmpty": "Menampilkan 0 hingga 0 dari 0 entri",
            "paginate": {
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>
@endpush
