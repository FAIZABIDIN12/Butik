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
            <div class="box-body table-responsive">
                <!-- Add a form to filter by month -->
                <form action="{{ route('report.profit_loss') }}" method="GET">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <input type="month" class="form-control" id="month" name="month" value="{{ request('month') }}">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Pendapatan -->
                <h4>Laba Rugi</h4>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Code</th>
                            <th>Account</th>
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
                    </tbody>
                </table>

                <!-- Tabel HPP Barang Dagang -->
                <h4>Beban/Biaya</h4>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Code</th>
                            <th>Account</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>102</td>
                            <td>HPP Barang Dagang</td>
                            <td id="total_hpp">{{ $total_hpp }}</td> <!-- Pre-populated value -->
                        </tr>
                    </tbody>
                </table>

                <!-- Total Laba Rugi -->
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
    <!-- Gabungkan dalam satu <th> dan gunakan flex untuk layout -->
    <th colspan="4" class="text-left">
        <div>
            <span><strong>Total Laba Rugi: Rp.</strong></span>
            <span><strong>{{ $profit_loss }}</strong></span>
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
