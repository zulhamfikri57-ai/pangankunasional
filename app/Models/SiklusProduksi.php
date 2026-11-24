<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiklusProduksi extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'estimasi_tanggal_panen' => 'date',
        'tanggal_panen_aktual' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function jadwals()
    {
        return $this->hasMany(JadwalPerawatan::class)->orderBy('tanggal_jadwal');
    }

    public function keuangans()
    {
        return $this->hasMany(Keuangan::class, 'siklus_produksi_id');
    }

    public function produksi()
    {
        return $this->hasOne(Produksi::class, 'siklus_produksi_id');
    }

    public function listings()
    {
        return $this->hasMany(ListingPanen::class, 'siklus_produksi_id');
    }
}