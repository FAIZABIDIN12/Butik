<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kode Member</th>
            <th>Total Item</th>
            <th>Total Harga</th>
            <th>Diskon</th>
            <th>Payment</th>
            <th>Total Bayar</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($penjualan as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->created_at->format('Y-m-d') }}</td>
                <td>{{ $item->kode_member }}</td>
                <td>{{ $item->total_item }}</td>
                <td>{{ $item->total_harga }}</td>
                <td>{{ $item->diskon }}%</td>
                <td>{{ $item->metode_pembayaran }}</td>
                <td>{{ $item->bayar }}</td>
                <td>{{ $item->kasir->name ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
