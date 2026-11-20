<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPerawatan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_jadwal' => 'date',
    ];

    public function siklus()
    {
        return $this->belongsTo(SiklusProduksi::class, 'siklus_produksi_id');
    }
}