<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\AiClient;

class MakananController extends Controller {
    public function index(): void {
        $profile = session_get('user_profile');
        if (empty($profile)) {
            $this->redirect('profil');
            return;
        }

        // Ambil data analisis jika ada (misal pengguna kembali dari konfirmasi atau ubah profil)
        $analysis = session_get('analysis_data', [
            'nama_makanan' => '',
            'foto' => null
        ]);

        $this->render('makanan', [
            'profile' => $profile,
            'analysis' => $analysis,
            'error' => session_get('flash_error')
        ]);
        session_set('flash_error', null);
    }

    /**
     * Dijalankan saat pengguna selesai hitung gizi dan klik "Analisis makanan lain"
     */
    public function reset(): void {
        session_set('user_profile', null);
        session_set('analysis_data', null);
        session_set('flash_error', null);
        $this->redirect('profil');
    }

    public function process(): void {
        $namaMakanan = trim($_POST['nama_makanan'] ?? '');
        $photoUploaded = null;

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $fileType = mime_content_type($tmpName);

            if (in_array($fileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $newFilename = 'makanan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $uploadDir = __DIR__ . '/../../public/assets/uploads/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $destination = $uploadDir . $newFilename;
                if (move_uploaded_file($tmpName, $destination)) {
                    $photoUploaded = 'assets/uploads/' . $newFilename;
                }
            }
        }

        if (empty($photoUploaded)) {
            $photoUploaded = !empty($_POST['foto_path']) ? trim($_POST['foto_path']) : null;
        }

        if (empty($namaMakanan) || empty($photoUploaded)) {
            session_set('flash_error', 'Isi nama makanan dan tambah foto dulu.');
            $this->redirect('makanan');
            return;
        }

        $hasilAi = AiClient::identify($namaMakanan, $photoUploaded);

        if (!$hasilAi['sukses']) {
            session_set('flash_error', 'Makanan tidak dikenali. Coba ketik nama makanan lain.');
            $this->redirect('makanan');
            return;
        }

        session_set('analysis_data', [
            'nama_makanan' => $hasilAi['nama'],
            'foto' => $photoUploaded,
            'deskripsi' => $hasilAi['deskripsi'] ?? '',
            'bahan' => $hasilAi['bahan'],
            'porsi' => 1.0,
            'konflik' => $hasilAi['konflik'] ?? false,
            'konflik_teks' => $hasilAi['konflik_teks'] ?? '',
            'nama_foto' => $hasilAi['nama_foto'] ?? ''
        ]);

        $this->redirect('konfirmasi');
    }
}
