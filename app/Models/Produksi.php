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
}