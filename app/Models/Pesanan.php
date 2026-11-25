<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
        'email',
        'telepon',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'catatan',
        'metode_bayar',
        'subtotal',
        'ongkir',
        'total',
        'status',
        'bukti_bayar',
        'batas_bayar',
        'dibayar_pada',
        'dibuat_pada',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'ongkir' => 'integer',
        'total' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id')->orderBy('id');
    }
}
