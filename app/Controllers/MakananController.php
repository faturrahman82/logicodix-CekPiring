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

    public function reset(): void {
        session_set('user_profile', null);
        session_set('analysis_data', null);
        session_set('flash_error', null);
        $this->redirect('profil');
    }

    public function process(): void {
        $namaMakanan = mb_substr(trim($_POST['nama_makanan'] ?? ''), 0, 100);
        $photoUploaded = null;

        $ekstensiDiizinkan = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $batasUkuran = 5 * 1024 * 1024;

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $fileType = mime_content_type($tmpName);
            $ukuran = (int)($_FILES['foto']['size'] ?? 0);

            if (!isset($ekstensiDiizinkan[$fileType]) || @getimagesize($tmpName) === false) {
                session_set('flash_error', 'Foto harus berupa gambar JPG, PNG, atau WEBP.');
                $this->redirect('makanan');
                return;
            }
            if ($ukuran > $batasUkuran) {
                session_set('flash_error', 'Ukuran foto maksimal 5 MB.');
                $this->redirect('makanan');
                return;
            }

            $newFilename = 'makanan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensiDiizinkan[$fileType];
            $uploadDir = __DIR__ . '/../../public/assets/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($tmpName, $uploadDir . $newFilename)) {
                $photoUploaded = 'assets/uploads/' . $newFilename;
            }
        }

        if (empty($photoUploaded)) {
            $sebelumnya = session_get('analysis_data')['foto'] ?? null;
            if (is_string($sebelumnya) && preg_match('#^assets/uploads/makanan_\d+_[0-9a-f]{8}\.(jpg|png|webp)$#', $sebelumnya)) {
                $photoUploaded = $sebelumnya;
            }
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
            'nama_input' => $namaMakanan,
            'foto' => $photoUploaded,
            'deskripsi' => $hasilAi['deskripsi'] ?? '',
            'bahan' => $hasilAi['bahan'],
            'porsi' => 1.0,
            'ukuran_porsi' => $hasilAi['ukuran_porsi'] ?? \App\Services\PortionSize::bawaan(),
            'sumber_analisis' => $hasilAi['sumber_analisis'] ?? 'contoh',
            'konflik' => $hasilAi['konflik'] ?? false,
            'konflik_teks' => $hasilAi['konflik_teks'] ?? '',
            'nama_foto' => $hasilAi['nama_foto'] ?? ''
        ]);

        $this->redirect('konfirmasi');
    }
}
