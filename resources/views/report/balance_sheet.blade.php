@extends('layouts.master')

@section('title')
    Laporan Neraca
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Laporan Neraca</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                <!-- Form untuk filter berdasarkan bulan -->
                <form action="{{ route('report.balance_sheet') }}" method="GET">
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


                <!-- Tabel Aktiva -->
                <h4>Aktiva</h4>
                <table class="table striped-table">
                    <thead>
                        <tr>
                            <th><h6>Kode</h6></th>
                            <th><h6>Nama Akun</h6></th>
                            <th><h6>Saldo</h6></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activaAccounts as $item)
                            <tr>
                                <td>{{ $item['account']->code }}</td>
                                <td>{{ $item['account']->name }}</td>
                                <td>{{ number_format($item['balance'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Total Aktiva:</th>
                            <th>{{ number_format($totalActiva, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>

                <!-- Tabel Pasiva -->
                <h4>Pasiva</h4>
                <table class="table striped-table">
                    <thead>
                        <tr>
                            <th><h6>Kode</h6></th>
                            <th><h6>Nama Akun</h6></th>
                            <th><h6>Saldo</h6></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($passivaAccounts as $item)
                            <tr>
                                <td>{{ $item['account']->code }}</td>
                                <td>{{ $item['account']->name }}</td>
                                <td>{{ number_format($item['balance'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Total Pasiva:</th>
                            <th>{{ number_format($totalPassiva, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>

                <!-- Keseimbangan Aktiva dan Pasiva -->
                <h4>Keseimbangan</h4>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="3" class="text-left">
                                <div>
                                    <span><strong>Total Aktiva: Rp.</strong></span>
                                    <span><strong>{{ number_format($totalActiva, 0, ',', '.') }}</strong></span>
                                </div>
                                <div>
                                    <span><strong>Total Pasiva: Rp.</strong></span>
                                    <span><strong>{{ number_format($totalPassiva, 0, ',', '.') }}</strong></span>
                                </div>
                                <div>
                                    <span><strong>Keseimbangan: Rp.</strong></span>
                                    <span><strong>{{ number_format($totalActiva - $totalPassiva, 0, ',', '.') }}</strong></span>
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
<script>
document.querySelector('form').addEventListener('submit', function (event) {
    const monthInput = document.getElementById('month');
    const monthValue = monthInput.value; // e.g., '2024-09'
    
    if (monthValue) {
        // Format it to 'mm-yyyy'
        const [year, month] = monthValue.split('-');
        monthInput.value = `${month}-${year}`;
    }
});
</script>
@endpush
