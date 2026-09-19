<?php

namespace Database\Seeders;

use App\Models\DomainPrice;
use Illuminate\Database\Seeder;

class DomainPriceSeeder extends Seeder
{
    /**
     * Jalankan seed harga domain (snapshot lama).
     *
     * Sumber kebenaran harga = `php8.3 artisan rdash:sync-prices` (harga RDash × PPN).
     * Seeder ini hanya MENGISI baris yang belum ada (firstOrCreate) supaya tidak
     * menimpa harga hasil sinkronisasi / harga promo yang dikunci manual.
     */
    public function run(): void
    {
        $domains = [
            ['.biz.id', 10000, 55000, 15000, 65000],
            ['.my.id', 10000, 22000, 30000, 30000],
            ['.cc', 157000, 157000, 180000, 180000],
            ['.xyz', 180000, 180000, 205000, 205000],
            ['.org', 190000, 190000, 215000, 215000],
            ['.net', 195000, 200000, 220000, 225000],
            ['.click', 195000, 195000, 220000, 220000],
            ['.asia', 198000, 198000, 225000, 225000],
            ['.com', 227550, 227550, 225000, 265000],
            ['.id', 210000, 210000, 240000, 240000],
            ['.biz', 250000, 250000, 280000, 280000],
            ['.co.id', 270000, 270000, 305000, 305000],
            ['.cloud', 339000, 339000, 380000, 380000],
            ['.info', 375000, 375000, 420000, 420000],
            ['.pw', 399000, 399000, 450000, 450000],
            ['.or.id', 45000, 45000, 55000, 55000],
            ['.ac.id', 45000, 45000, 55000, 55000],
            ['.sch.id', 45000, 45000, 55000, 55000],
            ['.ponpes.id', 45000, 45000, 55000, 55000],
            ['.web.id', 45000, 45000, 55000, 55000],
            ['.co', 485000, 485000, 545000, 545000],
            ['.online', 629000, 629000, 705000, 705000],
            ['.site', 655000, 655000, 735000, 735000],
            ['.top', 99000, 99000, 115000, 115000],
        ];

        foreach ($domains as $domain) {
            DomainPrice::firstOrCreate(
                ['extension' => $domain[0]],
                [
                'extension' => $domain[0],
                'base_cost' => $domain[1],
                'renewal_cost' => $domain[2],
                'selling_price' => $domain[3],
                'renewal_price_with_tax' => $domain[4],
                'is_active' => true,
                ]
            );
        }
    }
}
