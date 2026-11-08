<?php

namespace App\Models;

class Ingredient {
    public string $nama;
    public float $berat_g;
    public string $keterangan;
    public ?array $gizi;
    public bool $ada_data_gizi;

    public function __construct(string $nama, float $berat_g = 100, string $keterangan = '') {
        $this->nama = $nama;
        $this->berat_g = $berat_g;
        $this->keterangan = $keterangan;
        
        $fact = NutritionFact::findByName($nama);
        if ($fact !== null) {
            $this->gizi = $fact;
            $this->ada_data_gizi = true;
        } else {
            $this->gizi = null;
            $this->ada_data_gizi = false;
        }
    }
}
