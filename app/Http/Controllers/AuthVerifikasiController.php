<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class AuthVerifikasiController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'kontak' => 'required|string|max:120', // Email asli atau Nomor HP aktif (08...)
            'password' => 'required|string|min:6|confirmed',
        ]);

        $kontak = trim($request->kontak);
        $isEmail = filter_var($kontak, FILTER_VALIDATE_EMAIL);
        $isPhone = preg_match('/^(\+62|62|0)8[1-9][0-9]{7,11}$/', preg_replace('/[\s\-]/', '', $kontak));

        if (!$isEmail && !$isPhone) {
            return back()->withErrors([
                'kontak' => 'Masukkan alamat Email asli yang valid atau Nomor HP aktif Indonesia (contoh: 081234567890).'
            ])->withInput();
        }

        $email = $isEmail ? strtolower($kontak) : null;
        $noHp = $isPhone ? $this->normalisasiHp($kontak) : null;

        if ($email && User::where('email', $email)->exists()) {
            return back()->withErrors(['kontak' => 'Email ini sudah terdaftar. Silakan masuk (login).'])->withInput();
        }
        if ($noHp && User::where('no_hp', $noHp)->exists()) {
            return back()->withErrors(['kontak' => 'Nomor HP ini sudah terdaftar. Silakan masuk (login).'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $email ?? ($noHp . '@noemail.panganku.local'),
            'no_hp' => $noHp,
            'password' => Hash::make($request->password),
            'peran_sektor' => 'konsumen',
            'kode_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
            'is_kontak_verified' => false,
        ]);

        $this->kirimKodeOtp($user, $otp, $isEmail ? 'email' : 'hp');

        Auth::login($user);

        return redirect()->route('verifikasi.otp')
            ->with('success', 'Kode verifikasi 6 digit telah dikirim ke ' . ($isEmail ? $email : $noHp) . '.');
    }

    public function showVerifikasiOtp()
    {
        $user = auth()->user();
        if ($user->is_kontak_verified) {
            return redirect()->route('beranda');
        }
        return view('auth.verifikasi-otp', compact('user'));
    }

    public function prosesVerifikasiOtp(Request $request)
    {
        $request->validate([
            'kode_otp' => 'required|digits:6',
        ]);

        $user = auth()->user();

        if (!$user->kode_otp || now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['kode_otp' => 'Kode OTP sudah kedaluwarsa. Klik "Kirim Ulang Kode".']);
        }

        if ($request->kode_otp !== $user->kode_otp) {
            return back()->withErrors(['kode_otp' => 'Kode OTP tidak sesuai. Periksa kembali pesan Anda.']);
        }

        $user->update([
            'is_kontak_verified' => true,
            'email_verified_at' => now(),
            'kode_otp' => null,
            'otp_expires_at' => null,
        ]);

        return redirect()->route('beranda')->with('success', 'Akun berhasil diverifikasi! Selamat datang di PanganKu Nasional.');
    }

    public function kirimUlangOtp()
    {
        $user = auth()->user();
        $otp = (string) random_int(100000, 999999);

        $user->update([
            'kode_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        $tujuan = str_ends_with($user->email, '@noemail.panganku.local') ? 'hp' : 'email';
        $this->kirimKodeOtp($user, $otp, $tujuan);

        return back()->with('success', 'Kode verifikasi baru telah dikirim ulang!');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'kontak' => 'required|string',
            'password' => 'required|string',
        ]);

        $kontak = trim($request->kontak);
        $user = filter_var($kontak, FILTER_VALIDATE_EMAIL)
            ? User::where('email', strtolower($kontak))->first()
            : User::where('no_hp', $this->normalisasiHp($kontak))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['kontak' => 'Email/Nomor HP atau kata sandi salah.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (!$user->is_kontak_verified) {
            return redirect()->route('verifikasi.otp');
        }

        return redirect()->intended(route('beranda'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('beranda');
    }

    // =========================================================================
    // UPGRADE PERAN DENGAN VERIFIKASI DATA & DOKUMEN ASLI
    // =========================================================================
    public function showUpgradePeran()
    {
        $user = auth()->user();
        return view('pages.upgrade-peran', compact('user'));
    }

    public function submitUpgradePeran(Request $request)
    {
        $user = auth()->user();

        if (!$user->is_kontak_verified) {
            return redirect()->route('verifikasi.otp')
                ->withErrors(['kode_otp' => 'Verifikasi Email atau Nomor HP Anda terlebih dahulu sebelum mengajukan peran usaha.']);
        }

        $validated = $request->validate([
            'peran_diajukan' => 'required|in:petani,peternak,nelayan',
            'nik_identitas' => 'required|digits:16',
            'no_hp_aktif' => 'required|string|min:10|max:20',
            'alamat_usaha_riil' => 'required|string|min:15|max:500',
            'dokumen_verifikasi' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'nik_identitas.digits' => 'NIK KTP wajib 16 digit angka sesuai identitas asli.',
            'dokumen_verifikasi.required' => 'Unggah foto KTP / Surat Keterangan Usaha / Foto Lahan atau Kapal asli.',
        ]);

        $path = $request->file('dokumen_verifikasi')->store('verifikasi-peran', 'public');

        $user->update([
            'no_hp' => $this->normalisasiHp($validated['no_hp_aktif']),
            'peran_diajukan' => $validated['peran_diajukan'],
            'nik_identitas' => $validated['nik_identitas'],
            'alamat_usaha_riil' => $validated['alamat_usaha_riil'],
            'dokumen_verifikasi_path' => $path,
            'status_verifikasi_peran' => 'pending',
            'catatan_admin_verifikasi' => null,
        ]);

        return redirect()->route('peran.upgrade')
            ->with('success', 'Pengajuan upgrade peran beserta dokumen identitas asli berhasil dikirim dan sedang diverifikasi oleh Admin!');
    }

    public function adminPutuskanPeran(Request $request, $userId)
    {
        if (auth()->user()?->peran_sektor !== 'admin') {
            abort(403);
        }

        $request->validate([
            'keputusan' => 'required|in:approved,rejected',
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        $targetUser = User::findOrFail($userId);

        if ($request->keputusan === 'approved' && $targetUser->peran_diajukan) {
            $targetUser->update([
                'peran_sektor' => $targetUser->peran_diajukan,
                'status_verifikasi_peran' => 'approved',
                'catatan_admin_verifikasi' => $request->catatan_admin ?? 'Dokumen identitas & lokasi usaha valid.',
            ]);
        } else {
            $targetUser->update([
                'status_verifikasi_peran' => 'rejected',
                'catatan_admin_verifikasi' => $request->catatan_admin ?? 'Dokumen tidak terbaca atau data tidak sesuai.',
            ]);
        }

        return back()->with('success', "Status verifikasi peran untuk {$targetUser->name} berhasil diperbarui.");
    }

    private function normalisasiHp(string $hp): string
    {
        $clean = preg_replace('/[^0-9]/', '', $hp);
        if (str_starts_with($clean, '62')) {
            return '0' . substr($clean, 2);
        }
        return $clean;
    }

    private function kirimKodeOtp(User $user, string $otp, string $jalur): void
    {
        Log::info("OTP PanganKu Nasional untuk {$user->name} ({$jalur}): {$otp}");

        if ($jalur === 'email' && !empty(config('mail.mailers.smtp.username'))) {
            try {
                Mail::raw(
                    "Halo {$user->name},\n\nKode verifikasi akun PanganKu Nasional Anda adalah: {$otp}\nKode berlaku selama 15 menit.\n\nJangan bagikan kode ini kepada siapa pun.",
                    function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Kode Verifikasi Akun PanganKu Nasional');
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim email OTP SMTP: ' . $e->getMessage());
            }
        } elseif ($jalur === 'hp' && env('FONNTE_TOKEN')) {
            try {
                Http::withHeaders(['Authorization' => env('FONNTE_TOKEN')])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $user->no_hp,
                        'message' => "*PanganKu Nasional*\nKode verifikasi (OTP) akun Anda: *{$otp}*\nBerlaku 15 menit.",
                    ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim WhatsApp OTP: ' . $e->getMessage());
            }
        }
    }

    // 1. Tampilkan form permintaan OTP lupa sandi
    public function showLupaSandi()
    {
        return view('auth.lupa-sandi');
    }

    // 2. Proses cari akun & simpan OTP reset sandi di session
    public function kirimOtpResetSandi(Request $request)
    {
        $request->validate([
            'identitas' => 'required|string|max:150',
        ]);

        $identitas = trim($request->identitas);

        // Cari user berdasarkan email atau nomor HP
        $user = User::where('email', $identitas)
            ->orWhere('no_hp', $identitas)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'identitas' => 'Akun dengan email atau nomor HP tersebut tidak ditemukan.',
            ])->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        // Simpan seluruh data verifikasi reset langsung di session (anti gagal mass-assignment)
        session([
            'reset_user_id' => $user->id,
            'reset_identitas' => $identitas,
            'reset_otp_code' => $otp,
            'reset_otp_expires' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('password.reset.form')->with(
            'success',
            "Kode OTP reset kata sandi telah dikirim ke {$identitas}. (Kode Uji Coba: {$otp})"
        );
    }

    // 4. Validasi OTP dari session & simpan kata sandi baru
    public function prosesResetSandi(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userId = session('reset_user_id');
        $otpTersimpan = (string) session('reset_otp_code');
        $waktuKadaluarsa = session('reset_otp_expires');

        $user = User::find($userId);

        if (!$user || !$otpTersimpan) {
            return redirect()->route('password.request')
                ->withErrors(['identitas' => 'Sesi reset kata sandi berakhir. Silakan ulangi kembali.']);
        }

        if (trim($request->otp_code) !== $otpTersimpan) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak sesuai. Periksa kembali 6 digit kode Anda.']);
        }

        if ($waktuKadaluarsa && now()->timestamp > $waktuKadaluarsa) {
            return back()->withErrors(['otp_code' => 'Kode OTP sudah kedaluwarsa (lebih dari 10 menit). Silakan minta kode baru.']);
        }

        // Perbarui password & hapus sesi OTP (one-time use)
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        session()->forget([
            'reset_user_id',
            'reset_identitas',
            'reset_otp_code',
            'reset_otp_expires',
        ]);

        return redirect()->route('login')->with(
            'success',
            'Kata sandi berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.'
        );
    }

    // 3. Tampilkan form input OTP & password baru
    public function showResetSandi()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.request')
                ->withErrors(['identitas' => 'Silakan masukkan email atau nomor HP Anda terlebih dahulu.']);
        }

        return view('auth.reset-sandi', [
            'identitas' => session('reset_identitas'),
        ]);
    }
}