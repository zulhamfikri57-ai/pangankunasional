<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PanduanTanam;

class PanduanSektorSeeder extends Seeder
{
    public function run(): void
    {
        PanduanTanam::truncate();

        PanduanTanam::insert([
            // 1. PETANI
            ['sektor' => 'petani', 'komoditas_fokus' => 'Pertanian & Hortikultura', 'judul' => 'Waktu Tanam & Rotasi Lahan', 'deskripsi' => 'Tanam di awal musim hujan dengan kelembapan terjaga. Lakukan rotasi tanaman (cabai–jagung–kacang) untuk memutus siklus hama tanah.', 'ikon' => '🌱'],
            ['sektor' => 'petani', 'komoditas_fokus' => 'Pemupukan Berimbang', 'judul' => 'Takaran Pupuk Organik & NPK', 'deskripsi' => 'Gunakan pupuk kandang matang 2 ton/ha ditambah NPK bertahap pada fase vegetatif dan pembungaan agar buah tidak mudah rontok.', 'ikon' => '🧪'],
            ['sektor' => 'petani', 'komoditas_fokus' => 'Pascapanen Pertanian', 'judul' => 'Sortasi Grade & Pendinginan Awal', 'deskripsi' => 'Pisahkan hasil panen Grade A untuk pasar segar dan Grade B untuk olahan pasta/kering agar tidak menumpuk dan membusuk.', 'ikon' => '📦'],
            ['sektor' => 'petani', 'komoditas_fokus' => 'Pengendalian Hama', 'judul' => 'Pencegahan Lalat Buah & Jamur', 'deskripsi' => 'Pasang perangkap likat kuning dan perbaiki drainase bedengan guna mencegah busuk akar saat curah hujan tinggi.', 'ikon' => '🐞'],

            // 2. PETERNAK
            ['sektor' => 'peternak', 'komoditas_fokus' => 'Manajemen Pakan', 'judul' => 'Formulasi Pakan & Konversi (FCR)', 'deskripsi' => 'Berikan pakan sesuai fase umur (starter, grower, finisher) dan manfaatkan pakan fermentasi lokal untuk menekan biaya pakan hingga 20%.', 'ikon' => '🌾'],
            ['sektor' => 'peternak', 'komoditas_fokus' => 'Kesehatan Ternak', 'judul' => 'Jadwal Vaksinasi & Biosekuriti', 'deskripsi' => 'Semprot disinfektan kandang minimal 2x seminggu dan berikan vitamin antistres saat peralihan musim.', 'ikon' => '💉'],
            ['sektor' => 'peternak', 'komoditas_fokus' => 'Pascapanen Peternakan', 'judul' => 'Penanganan Telur, Susu & Daging', 'deskripsi' => 'Simpan telur pada suhu sejuk dengan ujung tumpul di atas, serta dinginkan susu segar maksimal 2 jam setelah pemerahan.', 'ikon' => '🥚'],
            ['sektor' => 'peternak', 'komoditas_fokus' => 'Limbah Kandang', 'judul' => 'Olah Kotoran Jadi Pupuk Kompos', 'deskripsi' => 'Fermentasi limbah kandang menggunakan EM4 untuk dijual kembali ke petani sekitar sebagai tambahan pendapatan.', 'ikon' => '♻️'],

            // 3. NELAYAN
            ['sektor' => 'nelayan', 'komoditas_fokus' => 'Rantai Dingin (Cold Chain)', 'judul' => 'Rasio Es Curah 1:1 di Palka', 'deskripsi' => 'Segera lapisi ikan hasil tangkapan dengan es curah bersih (1 kg es : 1 kg ikan) di palka berinsulasi untuk mempertahankan mutu Grade A.', 'ikon' => '🧊'],
            ['sektor' => 'nelayan', 'komoditas_fokus' => 'Penanganan Tangkapan', 'judul' => 'Penyiangan & Pencucian Air Bersih', 'deskripsi' => 'Hindari menumpuk ikan terlalu tinggi agar tidak memar, dan cuci menggunakan air laut bersih sebelum masuk kotak pendingin.', 'ikon' => '🐟'],
            ['sektor' => 'nelayan', 'komoditas_fokus' => 'Antisipasi Cuaca & Surplus', 'judul' => 'Pengolahan Ikan Asin, Asap & Beku', 'deskripsi' => 'Saat musim tangkapan melimpah dan harga TPI turun, alihkan sebagian tangkapan ke unit pengasapan atau pembekuan.', 'ikon' => '🌊'],
            ['sektor' => 'nelayan', 'komoditas_fokus' => 'Efisiensi Melaut', 'judul' => 'Perawatan Mesin & Alat Tangkap', 'deskripsi' => 'Periksa filter solar dan jaring sebelum melaut untuk menghemat konsumsi BBM dan mencegah kerusakan di tengah laut.', 'ikon' => '⚓'],

            // 4. PELAKU PANGAN LAINNYA
            ['sektor' => 'pelaku_pangan', 'komoditas_fokus' => 'Manajemen Gudang', 'judul' => 'Sistem Rotasi Stok FIFO & FEFO', 'deskripsi' => 'Keluarkan stok yang lebih dulu masuk (First-In First-Out) dan periksa tanggal kedaluwarsa guna mencegah bahan pangan terbuang.', 'ikon' => '🏭'],
            ['sektor' => 'pelaku_pangan', 'komoditas_fokus' => 'Nilai Tambah Produk', 'judul' => 'Diversifikasi Olahan Pangan', 'deskripsi' => 'Serap bahan baku berlebih dari petani/nelayan sekitar untuk diolah menjadi bumbu siap pakai, keripik, atau makanan beku.', 'ikon' => '🥫'],
            ['sektor' => 'pelaku_pangan', 'komoditas_fokus' => 'Kemasan & Distribusi', 'judul' => 'Kemasan Kedap Udara & Vakum', 'deskripsi' => 'Gunakan kemasan vakum untuk memperpanjang masa simpan produk olahan hingga 3–6 bulan tanpa pengawet berlebih.', 'ikon' => '📦'],
            ['sektor' => 'pelaku_pangan', 'komoditas_fokus' => 'Kemitraan Pasokan', 'judul' => 'Kontrak Pasok Langsung Produsen', 'deskripsi' => 'Kunci harga bahan baku langsung dengan petani/peternak lokal melalui listing PanganKu untuk menjaga stabilitas margin usaha.', 'ikon' => '🤝'],

            // 5. KONSUMEN
            ['sektor' => 'konsumen', 'komoditas_fokus' => 'Belanja Cerdas', 'judul' => 'Beli Komoditas Musim Panen Raya', 'deskripsi' => 'Manfaatkan fitur Pantauan Harga untuk membeli sayur, buah, atau ikan saat pasokan sedang melimpah sehingga harga lebih hemat.', 'ikon' => '🛒'],
            ['sektor' => 'konsumen', 'komoditas_fokus' => 'Penyimpanan Rumah Tangga', 'judul' => 'Tips Simpan Sayur & Lauk Tahan Lama', 'deskripsi' => 'Simpan cabai dan bawang dalam wadah kering beralas tisu dapur, serta bekukan ikan/daging dalam porsi sekali masak.', 'ikon' => '❄️'],
            ['sektor' => 'konsumen', 'komoditas_fokus' => 'Kurangi Food Waste', 'judul' => 'Perencanaan Menu Mingguan (Meal Prep)', 'deskripsi' => 'Belanja sesuai kebutuhan mingguan dan olah bahan yang paling cepat layu terlebih dahulu agar tidak ada makanan terbuang.', 'ikon' => '🥗'],
            ['sektor' => 'konsumen', 'komoditas_fokus' => 'Dukung Pangan Lokal', 'judul' => 'Belanja Langsung dari Produsen', 'deskripsi' => 'Beli langsung dari petani, peternak, dan nelayan lokal di menu Pasar untuk mendapatkan bahan lebih segar dengan harga adil.', 'ikon' => '🌾'],
        ]);
    }
}