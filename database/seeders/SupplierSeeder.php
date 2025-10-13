<?php

namespace Database\Seeders;

use App\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Sumber Makmur',
                'email' => 'sales@sumbermakmur.co.id',
                'contact_person' => 'Bapak Hendra',
                'phone' => '021-6661234',
                'address' => 'Jl. Industri Raya No. 45, Bekasi',
                'status' => 'active'
            ],
            [
                'name' => 'CV Mitra Sejahtera',
                'email' => 'info@mitrasejahtera.com',
                'contact_person' => 'Ibu Sari',
                'phone' => '021-7772345',
                'address' => 'Jl. Raya Bogor KM 25, Cibinong',
                'status' => 'active'
            ],
            [
                'name' => 'PT Global Supply',
                'email' => 'procurement@globalsupply.co.id',
                'contact_person' => 'Bapak Rudi',
                'phone' => '021-8883456',
                'address' => 'Jl. Margonda Raya No. 123, Depok',
                'status' => 'active'
            ],
            [
                'name' => 'Toko Grosir Bahagia',
                'email' => 'bahagia.grosir@yahoo.com',
                'contact_person' => 'Bapak Joko',
                'phone' => '021-9994567',
                'address' => 'Jl. Pasar Besar No. 67, Jakarta Pusat',
                'status' => 'active'
            ],
            [
                'name' => 'PT Elektronik Prima',
                'email' => 'sales@elektronikprima.com',
                'contact_person' => 'Ibu Dewi',
                'phone' => '021-1115678',
                'address' => 'Jl. Mangga Dua No. 89, Jakarta Utara',
                'status' => 'active'
            ],
            [
                'name' => 'CV Bahan Bangunan Jaya',
                'email' => 'admin@bahanbangunan.co.id',
                'contact_person' => 'Bapak Agus',
                'phone' => '021-2226789',
                'address' => 'Jl. Raya Serpong No. 234, Tangerang',
                'status' => 'active'
            ],
            [
                'name' => 'PT Kimia Farma Distribusi',
                'email' => 'distribusi@kimiafarma.co.id',
                'contact_person' => 'Ibu Linda',
                'phone' => '021-3337890',
                'address' => 'Jl. Sudirman No. 456, Jakarta Pusat',
                'status' => 'active'
            ],
            [
                'name' => 'Supplier Lama Corp',
                'email' => 'old@supplierlama.com',
                'contact_person' => 'Bapak Tono',
                'phone' => '021-4448901',
                'address' => 'Jl. Veteran No. 78, Jakarta Timur',
                'status' => 'inactive'
            ],
            [
                'name' => 'PT Teknologi Digital',
                'email' => 'sales@tekdigital.co.id',
                'contact_person' => 'Ibu Maya',
                'phone' => '021-5559012',
                'address' => 'Jl. Kuningan No. 345, Jakarta Selatan',
                'status' => 'active'
            ],
            [
                'name' => 'CV Makanan Sehat',
                'email' => 'order@makanansehat.com',
                'contact_person' => 'Bapak Andi',
                'phone' => '021-6660123',
                'address' => 'Jl. Kemang Raya No. 567, Jakarta Selatan',
                'status' => 'active'
            ]
        ];

        foreach ($suppliers as $supplier) {
            // Check if supplier with this email already exists
            if (!Supplier::where('email', $supplier['email'])->exists()) {
                Supplier::create($supplier);
            }
        }
    }
}