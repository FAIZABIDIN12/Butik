    @extends('layouts.master')

    @section('title')
    Laporan Penjualan
    @endsection

    @section('breadcrumb')
    @parent
    <li class="active">Laporan Penjualan</li>
    @endsection


    @section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-body table-responsive with-border">
                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <!-- Tabel Saldo -->
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <table class="table table-striped table-bordered" style="font-size: 16px;" id="balanceTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <h4>Jenis</h4>
                                        </th>
                                        <th>
                                            <h4>Saldo</h4>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Saldo Tunai</strong></td>
                                        <td>{{ number_format($cashBalance, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Saldo Non Tunai</strong></td>
                                        <td>{{ number_format($nonCashBalance, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered" style="font-size: 18px; color: green;">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-left">
                                    <div>
                                        <span><strong>Total Penjualan: Rp.</strong></span>
                                        <span><strong>{{ number_format($totalSales, 0, ',', '.') }}</strong></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <br>
                    <!-- Penarikan Saldo -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="{{ route('payment.withdraw') }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_id" value="{{ $paymentId }}"> <!-- Add this line -->
                                <div class="form-group">
                                    <label for="amount">Jumlah Penarikan:</label>
                                    <input type="number" name="amount" class="form-control" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="method">Metode:</label>
                                    <select name="method" class="form-control" required>
                                        <!-- <option value="tunai">Tunai</option> -->
                                        <option value="non_tunai">Non Tunai</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Tarik Saldo</button>
                            </form>
                        </div>
                        <!-- Total Penjualan -->

                        <div class="col-md-6">
                            <h4>History Penarikan</h4>
                            <table class="table table-striped table-bordered" style="font-size: 16px;" id="withdrawalTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <h4>Jumlah Penarikan</h4>
                                        </th>
                                        <th>
                                            <h4>Metode</h4>
                                        </th>
                                        <th>
                                            <h4>Tanggal</h4>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($withdrawals as $withdrawal)
                                    <tr>
                                        <td>{{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                        <td>{{ ucfirst($withdrawal->method) }}</td>
                                        <td>{{ $withdrawal->created_at->format('d-m-Y H:i:s') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data penarikan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
    @endsection



    @push('scripts')
    <script>
        $(document).ready(function() {
            var withdrawalTable = $('#withdrawalTable');

            // Ensure the table has rows before initializing
            if (withdrawalTable.find('tbody tr').length > 0) {
                withdrawalTable.DataTable({
                    "order": [] // No initial sorting
                });
            } else {
                console.log('No data available for the withdrawal table.');
            }
        });
    </script>
    @endpush