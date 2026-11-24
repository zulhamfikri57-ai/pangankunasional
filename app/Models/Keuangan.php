<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    protected $fillable = [
        'user_id',
        'siklus_produksi_id',
        'sumber_transaksi',
        'referensi_kode',
        'jenis',
        'kategori',
        'jumlah',
        'nominal',
        'keterangan',
        'tanggal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siklus()
    {
        return $this->belongsTo(SiklusProduksi::class, 'siklus_produksi_id');
    }
}