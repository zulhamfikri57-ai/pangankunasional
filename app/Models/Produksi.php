<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    protected $guarded = [];

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siklus()
    {
        return $this->belongsTo(SiklusProduksi::class, 'siklus_produksi_id');
    }

    public function listings()
    {
        return $this->hasMany(ListingPanen::class, 'produksi_id');
    }
}