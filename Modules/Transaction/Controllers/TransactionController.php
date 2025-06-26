<?php

namespace Modules\Transaction\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Transaction\Models\Transaction;
use Modules\Transaction\Models\TransactionItem;
use Modules\Product\Models\Product;

class TransactionController
{
    // Ambil semua transaksi
    public function index()
    {
        $transactions = Transaction::with(['user', 'items.product'])->latest()->get();
        return response()->json($transactions);
    }

    // Ambil detail transaksi by id
    public function show($id)
    {
        $transaction = Transaction::with(['user', 'items.product'])->findOrFail($id);
        return response()->json($transaction);
    }

    // Simpan transaksi baru
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['qty'];
                $total += $subtotal;

                // Kurangi stok
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok untuk {$product->name} tidak mencukupi.");
                }

                $product->decrement('stock', $item['qty']);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'total_price' => $total,
            ]);

            foreach ($itemsData as $data) {
                $data['transaction_id'] = $transaction->id;
                TransactionItem::create($data);
            }

            DB::commit();

            return response()->json(['message' => 'Transaksi berhasil disimpan', 'data' => $transaction->load('items.product')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Transaction::with('items');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $query->get();

        $totalRevenue = $transactions->sum('total_price');

        $totalItems = $transactions->sum(function ($transaction) {
            return $transaction->items->sum('qty');
        });

        return response()->json([
            'total_transaction' => $transactions->count(),
            'total_revenue' => $totalRevenue,
            'total_items_sold' => $totalItems
        ]);
    }

    public function monthlySalesReport()
    {
        $result = DB::table('transactions')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw("SUM(total_price) as total_income")
            ->selectRaw("COUNT(*) as total_transactions")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($result);
    }

    public function bestSellingProducts(Request $request)
    {
        $limit = $request->query('limit', 10); // default ambil 10 produk terlaris

        $bestProducts = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(transaction_items.qty) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json($bestProducts);
    }

}
