<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahDistribusi extends Model
{
    protected $fillable = [
        'nama_wilayah',
        'provinsi',
        'tingkat_defisit',
        'biaya_logistik_kg',
        'faktor_harga',
        'jarak_km',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}