<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'password',
        'peran_sektor',
        'kode_otp',
        'otp_expires_at',
        'is_kontak_verified',
        'peran_diajukan',
        'nik_identitas',
        'alamat_usaha_riil',
        'dokumen_verifikasi_path',
        'status_verifikasi_peran',
        'catatan_admin_verifikasi',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'kode_otp',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'is_kontak_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }
}