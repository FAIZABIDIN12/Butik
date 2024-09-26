@section('content')@extends('layouts.master')

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
            <div class="box-body table-responsive">
                <!-- Form untuk filter berdasarkan bulan -->
                <form action="{{ route('report.profit_loss') }}" method="GET">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <select class="form-control" id="month" name="month" required>
                                <option value="">Pilih Bulan</option>
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" {{ request('month') == str_pad($month, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <select class="form-control" id="year" name="year" required>
                                <option value="">Pilih Tahun</option>
                                @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Pendapatan -->
                <h4>Pendapatan</h4>
                <table id="profit-table" class="table striped-table">
                    <thead>
                        <tr>
                            <th><h6>Kode</h6></th>
                            <th><h6>Nama Akun</h6></th>
                            <th><h6>Saldo</h6></th>
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
                <!-- Tabel Beban -->
                <h4>Beban/Biaya</h4>
                <table id="loss-table" class="table striped-table">
                    <thead>
                        <tr>
                            <th><h6>Kode</h6></th>
                            <th><h6>Nama Akun</h6></th>
                            <th><h6>Saldo</h6></th>
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

                <!-- Total Laba Rugi -->
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="4" class="text-left">
                                <div>
                                    <span><strong>Total Laba Rugi: Rp.</strong></span>
                                    <span><strong>{{ number_format($totalIncome - $totalOutcome, 2) }}</strong></span>
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
