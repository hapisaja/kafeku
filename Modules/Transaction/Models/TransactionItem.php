<?php

namespace Modules\Transaction\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Transaction\Models\Transaction;
use Modules\Product\Models\Product;

class TransactionItem extends Model
{
    protected $fillable = ['transaction_id', 'product_id', 'qty', 'price', 'subtotal'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
