<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItem extends Model
{
    protected $table = 'pesanan_item';

    public $timestamps = false;

    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'nama_produk',
        'ukuran',
        'harga',
        'jumlah',
        'subtotal',
    ];

    protected $casts = [
        'pesanan_id' => 'integer',
        'produk_id' => 'integer',
        'harga' => 'integer',
        'jumlah' => 'integer',
        'subtotal' => 'integer',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
