@extends('layouts.master')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body">
            <h2>Tabel Beban Biaya</h2>
            <table class="table table-bordered">
    <thead>
        <tr>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($profitLossAccounts as $account)
            <tr>
                <td>{{ $account['code'] }}</td>
                <td>{{ $account['name'] }}</td>
                <td>Rp. {{ number_format($account['balance'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>Tabel Beban Biaya</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($expenseAccounts as $account)
            <tr>
                <td>{{ $account['code'] }}</td>
                <td>{{ $account['name'] }}</td>
                <td>Rp. {{ number_format($account['balance'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<h3>Laba Bersih: Rp. {{ number_format($netProfitLoss, 2) }}</h3>
</div>
        </div>
    </div>
</div>


@endsection
