<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    protected $table = 'produk';

    public $timestamps = false;

    protected $fillable = [
        'kategori_id',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'harga_asli',
        'ukuran',
        'kondisi',
        'stok',
        'gambar',
        'unggulan',
        'dibuat_pada',
    ];

    protected $casts = [
        'kategori_id' => 'integer',
        'harga' => 'integer',
        'harga_asli' => 'integer',
        'stok' => 'integer',
        'unggulan' => 'boolean',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}
