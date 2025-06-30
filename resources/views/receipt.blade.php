<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; }
        .items th, .items td { padding: 5px; border-bottom: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h2>KafeKu</h2>
        <p>Struk Transaksi #{{ $transaction->id }}</p>
    </div>

    <p><strong>Tanggal:</strong> {{ $transaction->created_at->format('d M Y H:i') }}</p>
    <p><strong>Kasir:</strong> {{ $transaction->user->name }}</p>
    <p><strong>Customer:</strong> {{ $transaction->customer_name ?? 'Umum' }}</p>
    <p><strong>Metode Pembayaran:</strong> {{ $transaction->payment_method ?? '-' }}</p>

    <table width="100%" class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>Rp {{ number_format($item->price) }}</td>
                <td>Rp {{ number_format($item->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total:</strong> Rp {{ number_format($transaction->total_price) }}</p>

    <p>Terima kasih!</p>
</body>
</html>
