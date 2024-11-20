@extends('layouts.master')

@section('title', 'Laporan Jurnal')

@section('breadcrumb')
@parent
<li class="active">Laporan Jurnal</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                <!-- Table for Journal Report -->
                <table class="table table-striped table-bordered table-jurnal">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Kategori</th>
                            <th>Nama Kategori</th>
                            <th>Akun Debit</th>
                            <th>Saldo Debit</th>
                            <th>Akun Kredit</th>
                            <th>Saldo Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categoryData as $index => $category)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $category['category_code'] }}</td>
                            <td>{{ $category['category_name'] }}</td>
                            <td>{{ $category['debit_account'] }}</td> <!-- Menampilkan akun debit -->
                            <td class="text-right">
                                {{ number_format($category['debit_total'], 2, '.', ',') }}
                            </td>
                            <td>{{ $category['credit_account'] }}</td> <!-- Menampilkan akun kredit -->
                            <td class="text-right">
                                {{ number_format($category['credit_total'], 2, '.', ',') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                        // Total debit dan kredit dari semua kategori
                        $totalDebit = collect($categoryData)->sum('debit_total');
                        $totalCredit = collect($categoryData)->sum('credit_total');
                        $balance = $totalDebit - $totalCredit;
                        @endphp
                        <tr>
                            <th colspan="1" class="text-left">Total Debit:</th>
                            <th class="text-right">{{ number_format($totalDebit, 2, '.', ',') }}</th>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-left">Total Kredit:</th>
                            <th class="text-right">{{ number_format($totalCredit, 2, '.', ',') }}</th>
                        </tr>

                        <tr style="background-color: #d4edda;">
                            <th colspan="1" class="text-left">Balance</th>
                            <th class="text-right" style="color: #28a745;">
                                {{ number_format($balance, 2, '.', ',') }}
                            </th>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Initialize DataTable with pagination and sorting features
        $('.table-jurnal').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering: true,
        });
    });
</script>
@endpush