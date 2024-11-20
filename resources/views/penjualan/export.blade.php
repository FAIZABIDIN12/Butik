<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kode Member</th>
            <th>Total Item</th>
            <th>Total Harga</th>
            <th>Diskon</th>
            <th>Metode Pembayaran</th>
            <th>Total Bayar</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($penjualan as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ tanggal_indonesia($item->created_at) }}</td>
            <td>{{ $item->member->kode_member ?? '-' }}</td>
            <td>{{ $item->total_item }}</td>
            <td>{{ format_uang($item->total_harga) }}</td>
            <td>{{ $item->diskon }}%</td>
            <td>{{ $item->metode_pembayaran }}</td>
            <td>{{ format_uang($item->bayar) }}</td>
            <td>{{ $item->user->name ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>