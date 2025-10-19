<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Product;
use App\Models\ProductUnit;
use App\ProductWarehouseStock;
use App\Warehouse;
use Illuminate\Support\Str;

class PharmacyProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get unit ID for Pieces
        $piecesUnit = ProductUnit::where('name', 'Pieces')->first();
        $unitId = $piecesUnit ? $piecesUnit->id : 1;
        
        // Get the main store warehouse
        $storeWarehouse = Warehouse::where('type', 'store')->first();
        if (!$storeWarehouse) {
            $this->command->error('Tidak ada gudang dengan tipe "store". Jalankan WarehouseSeeder terlebih dahulu.');
            return;
        }

        $products = [
            // Acyclovir Products
            ['name' => 'Acyclovir 400 mg HJ', 'category' => 'Antiviral', 'price_retail' => 5000],
            ['name' => 'Acyclovir 200 mg HJ', 'category' => 'Antiviral', 'price_retail' => 3000],
            ['name' => 'Acifar Salap', 'category' => 'Antiviral Topical', 'price_retail' => 15000],
            ['name' => 'Acifar 200 mg', 'category' => 'Antiviral', 'price_retail' => 3500],
            ['name' => 'Acifar 400 mg', 'category' => 'Antiviral', 'price_retail' => 6000],
            ['name' => 'Acyclovir Cream Berno', 'category' => 'Antiviral Topical', 'price_retail' => 12000],
            
            // Alpara Products
            ['name' => 'Alpara Tab', 'category' => 'Analgesic', 'price_retail' => 2000],
            ['name' => 'Alpara Syr', 'category' => 'Analgesic', 'price_retail' => 8000],
            
            // Amoxicillin Products
            ['name' => 'Amox Putih', 'category' => 'Antibiotic', 'price_retail' => 4000],
            ['name' => 'Amox DS Mersi', 'category' => 'Antibiotic', 'price_retail' => 6000],
            ['name' => 'Amoxsan Caps', 'category' => 'Antibiotic', 'price_retail' => 5000],
            ['name' => 'Amoxsan Drop', 'category' => 'Antibiotic', 'price_retail' => 12000],
            ['name' => 'Amoxicilin HJ Tab 200s', 'category' => 'Antibiotic', 'price_retail' => 4500],
            
            // Allopurinol Products
            ['name' => 'Allo 100 mg HJ', 'category' => 'Antigout', 'price_retail' => 3000],
            ['name' => 'Allo 100 mg Mersi', 'category' => 'Antigout', 'price_retail' => 3200],
            ['name' => 'Allo 100 mg Rama', 'category' => 'Antigout', 'price_retail' => 3100],
            ['name' => 'Allo 300 mg Berno', 'category' => 'Antigout', 'price_retail' => 8000],
            ['name' => 'Allo 300 mg HJ', 'category' => 'Antigout', 'price_retail' => 7500],
            ['name' => 'Alofar 100 mg', 'category' => 'Antigout', 'price_retail' => 3300],
            ['name' => 'Alofar 300 mg', 'category' => 'Antigout', 'price_retail' => 8200],
            ['name' => 'Allopurinol 100 mg HJ', 'category' => 'Antigout', 'price_retail' => 3000],
            ['name' => 'Allopurinol 100 mg Rama', 'category' => 'Antigout', 'price_retail' => 3100],
            
            // Antacid Products
            ['name' => 'Antasida Tab Erela', 'category' => 'Antacid', 'price_retail' => 1500],
            ['name' => 'Antasida Mersi Tab', 'category' => 'Antacid', 'price_retail' => 1600],
            ['name' => 'Antasida Tab Mersi', 'category' => 'Antacid', 'price_retail' => 1600],
            ['name' => 'Antasida Syr Mersi', 'category' => 'Antacid', 'price_retail' => 8000],
            
            // Cough & Cold Products
            ['name' => 'Anakonidin OBH', 'category' => 'Cough Medicine', 'price_retail' => 12000],
            ['name' => 'Anakonidin Syr', 'category' => 'Cough Medicine', 'price_retail' => 10000],
            ['name' => 'Anacetine Syr', 'category' => 'Cough Medicine', 'price_retail' => 15000],
            
            // Acid Products
            ['name' => 'Asam Erita', 'category' => 'Supplement', 'price_retail' => 2500],
            ['name' => 'Asam HJ', 'category' => 'Supplement', 'price_retail' => 2000],
            ['name' => 'Asam Mersi Tab', 'category' => 'Supplement', 'price_retail' => 2200],
            
            // Cardiovascular Products
            ['name' => 'Adalat Oros', 'category' => 'Cardiovascular', 'price_retail' => 15000],
            ['name' => 'Ator 20 HJ', 'category' => 'Cardiovascular', 'price_retail' => 8000],
            ['name' => 'Ascardia 80 mg', 'category' => 'Cardiovascular', 'price_retail' => 6000],
            ['name' => 'Aspilet Tab Kuning', 'category' => 'Cardiovascular', 'price_retail' => 2000],
            ['name' => 'Aspilet Trombo', 'category' => 'Cardiovascular', 'price_retail' => 3000],
            
            // Amlodipine Products
            ['name' => 'Amlodipin 10 mg KF', 'category' => 'Cardiovascular', 'price_retail' => 4000],
            ['name' => 'Amlodipin 5 mg KF', 'category' => 'Cardiovascular', 'price_retail' => 3000],
            ['name' => 'Amlodipin 10 mg Berno', 'category' => 'Cardiovascular', 'price_retail' => 4200],
            ['name' => 'Amlodipin 5 mg Berno', 'category' => 'Cardiovascular', 'price_retail' => 3200],
            ['name' => 'Amlodipin 10 mg HJ', 'category' => 'Cardiovascular', 'price_retail' => 3800],
            ['name' => 'Amlodipin 5 mg HJ', 'category' => 'Cardiovascular', 'price_retail' => 2800],
            ['name' => 'Amlodipin 10 mg Dexa', 'category' => 'Cardiovascular', 'price_retail' => 4100],
            ['name' => 'Amlodipin 5 mg Dexa', 'category' => 'Cardiovascular', 'price_retail' => 3100],
            ['name' => 'Amlodipin 10 mg Novell', 'category' => 'Cardiovascular', 'price_retail' => 4300],
            ['name' => 'Amlodipin 5 mg Novell', 'category' => 'Cardiovascular', 'price_retail' => 3300],
            
            // Atorvastatin Products
            ['name' => 'Atorvastatin 10 mg Fahrenheit', 'category' => 'Cardiovascular', 'price_retail' => 5000],
            ['name' => 'Atorvastatin 20 mg Fahrenheit', 'category' => 'Cardiovascular', 'price_retail' => 8000],
            ['name' => 'Atorvastatin 40 mg Fahrenheit', 'category' => 'Cardiovascular', 'price_retail' => 12000],
            ['name' => 'Atorvastatin 10 mg Dexa', 'category' => 'Cardiovascular', 'price_retail' => 4800],
            ['name' => 'Atorvastatin 20 mg Dexa', 'category' => 'Cardiovascular', 'price_retail' => 7800],
            
            // Supplements & Vitamins
            ['name' => 'Albumin Inayah', 'category' => 'Supplement', 'price_retail' => 25000],
            ['name' => 'Albusmin Pharos', 'category' => 'Supplement', 'price_retail' => 28000],
            ['name' => 'Arkavit Ungu', 'category' => 'Vitamin', 'price_retail' => 15000],
            ['name' => 'Arkavit C', 'category' => 'Vitamin', 'price_retail' => 12000],
            
            // Topical Products
            ['name' => 'Armacort CR', 'category' => 'Topical', 'price_retail' => 18000],
            ['name' => 'Autosol 15 gr', 'category' => 'Topical', 'price_retail' => 8000],
            ['name' => 'Autosol 50 gr', 'category' => 'Topical', 'price_retail' => 20000],
            ['name' => 'Aclonac Gel', 'category' => 'Topical', 'price_retail' => 15000],
            ['name' => 'Acnol Gel 10 gr', 'category' => 'Dermatology', 'price_retail' => 12000],
            ['name' => 'Acnol Lotion 10 ml', 'category' => 'Dermatology', 'price_retail' => 10000],
            
            // Herbal & Traditional
            ['name' => 'Antangin Candy 250s', 'category' => 'Herbal', 'price_retail' => 25000],
            ['name' => 'Antangin Candy Bag', 'category' => 'Herbal', 'price_retail' => 2000],
            ['name' => 'Antangin Cair', 'category' => 'Herbal', 'price_retail' => 8000],
            ['name' => 'Antangin Tab', 'category' => 'Herbal', 'price_retail' => 1500],
            ['name' => 'Antangin Habatusauda', 'category' => 'Herbal', 'price_retail' => 10000],
            ['name' => 'Antangin Mint', 'category' => 'Herbal', 'price_retail' => 8500],
            ['name' => 'Antangin Good Night', 'category' => 'Herbal', 'price_retail' => 9000],
            ['name' => 'Antangin Anak', 'category' => 'Herbal', 'price_retail' => 7000],
            ['name' => 'Akar Lawang 60 ml', 'category' => 'Herbal', 'price_retail' => 8000],
            ['name' => 'Akar Lawang 235 ml', 'category' => 'Herbal', 'price_retail' => 25000],
            ['name' => 'Adem Sari Gantung', 'category' => 'Herbal', 'price_retail' => 3000],
            ['name' => 'Antalinu', 'category' => 'Herbal', 'price_retail' => 5000],
            ['name' => 'Anak Sumang', 'category' => 'Herbal', 'price_retail' => 12000],
            
            // Allergy Products
            ['name' => 'Alerin Syr Kecil', 'category' => 'Antihistamine', 'price_retail' => 8000],
            ['name' => 'Alerin Syr Besar', 'category' => 'Antihistamine', 'price_retail' => 15000],
            ['name' => 'Allercyl 4s', 'category' => 'Antihistamine', 'price_retail' => 6000],
            
            // Pain Relief
            ['name' => 'Alphamol Tab', 'category' => 'Analgesic', 'price_retail' => 2500],
            ['name' => 'Aclonac Tab', 'category' => 'NSAID', 'price_retail' => 3000],
            
            // Respiratory
            ['name' => 'Ambroxol Tab Trifa', 'category' => 'Respiratory', 'price_retail' => 3500],
            ['name' => 'Acetylcisteine Dexa', 'category' => 'Respiratory', 'price_retail' => 8000],
            
            // Antibiotics
            ['name' => 'Ampicilin Tab Nova', 'category' => 'Antibiotic', 'price_retail' => 4000],
            ['name' => 'Azytromicin Kap Berno', 'category' => 'Antibiotic', 'price_retail' => 15000],
            
            // Gastrointestinal
            ['name' => 'Ambeven Caps', 'category' => 'Gastrointestinal', 'price_retail' => 8000],
            
            // Medical Supplies
            ['name' => 'Altamed Swab', 'category' => 'Medical Supply', 'price_retail' => 5000],
            ['name' => 'Alkohol IKA 100 ml', 'category' => 'Medical Supply', 'price_retail' => 3000],
            ['name' => 'Alkohol 100 ml Kunwell', 'category' => 'Medical Supply', 'price_retail' => 2800],
            ['name' => 'Alkohol 300 ml Kunwell', 'category' => 'Medical Supply', 'price_retail' => 7000],
            ['name' => 'Alkohol 1 liter Kunwell', 'category' => 'Medical Supply', 'price_retail' => 20000],
            ['name' => 'Alkohol 1 liter SAE', 'category' => 'Medical Supply', 'price_retail' => 22000],
            ['name' => 'Aseton Tokyo', 'category' => 'Medical Supply', 'price_retail' => 5000],
            ['name' => 'Alkindo Masker', 'category' => 'Medical Supply', 'price_retail' => 15000],
            
            // Skincare
            ['name' => 'Acne Plast Nourish', 'category' => 'Skincare', 'price_retail' => 25000],
            ['name' => 'Acnes Creamy Wash 100 gr', 'category' => 'Skincare', 'price_retail' => 18000],
            ['name' => 'Acnes Creamy Wash 50 gr', 'category' => 'Skincare', 'price_retail' => 12000],
            ['name' => 'Acnes Gel 18 gr', 'category' => 'Skincare', 'price_retail' => 15000],
            ['name' => 'Acnes Gel 9 gr', 'category' => 'Skincare', 'price_retail' => 8000],
            
            // Contraceptives
            ['name' => 'Andalan Pil Biru', 'category' => 'Contraceptive', 'price_retail' => 8000],
            ['name' => 'Andalan Lactasi', 'category' => 'Contraceptive', 'price_retail' => 10000],
            ['name' => 'Andalan FE Hijau', 'category' => 'Contraceptive', 'price_retail' => 9000],
            
            // Motion Sickness
            ['name' => 'Antimo Tab', 'category' => 'Motion Sickness', 'price_retail' => 3000],
            ['name' => 'Antimo Cair Anak', 'category' => 'Motion Sickness', 'price_retail' => 8000],
            
            // Pediatric
            ['name' => 'Apialis Syr', 'category' => 'Pediatric', 'price_retail' => 12000],
            ['name' => 'Apialis Drop', 'category' => 'Pediatric', 'price_retail' => 8000],
            
            // Antiseptic
            ['name' => 'Antis 55 ml', 'category' => 'Antiseptic', 'price_retail' => 5000],
            
            // Diabetes
            ['name' => 'Amaryl 1 mg', 'category' => 'Antidiabetic', 'price_retail' => 8000],
            ['name' => 'Amaryl 2 mg', 'category' => 'Antidiabetic', 'price_retail' => 12000],
            ['name' => 'Amaryl 3 mg', 'category' => 'Antidiabetic', 'price_retail' => 15000],
            ['name' => 'Amaryl 4 mg', 'category' => 'Antidiabetic', 'price_retail' => 18000],
            ['name' => 'Amaryl M 250/1', 'category' => 'Antidiabetic', 'price_retail' => 10000],
            ['name' => 'Amaryl M 500/2', 'category' => 'Antidiabetic', 'price_retail' => 15000],
            ['name' => 'Acarbose 50 mg Dexa', 'category' => 'Antidiabetic', 'price_retail' => 6000],
            ['name' => 'Acarbose 100 mg Dexa', 'category' => 'Antidiabetic', 'price_retail' => 10000],
            ['name' => 'Alat Easytouch', 'category' => 'Medical Device', 'price_retail' => 150000],
            
            // Neurological
            ['name' => 'Alpentin 100 mg', 'category' => 'Neurological', 'price_retail' => 8000],
            ['name' => 'Alpentin 300 mg', 'category' => 'Neurological', 'price_retail' => 15000],
            
            // Capsules
            ['name' => 'Anelat Kap', 'category' => 'Supplement', 'price_retail' => 5000],
            ['name' => 'Amostera Kap', 'category' => 'Supplement', 'price_retail' => 8000],
            
            // Eye Products
            ['name' => 'Alletrol Tetes Mata', 'category' => 'Ophthalmology', 'price_retail' => 12000],
            ['name' => 'Alletrol Salap Mata', 'category' => 'Ophthalmology', 'price_retail' => 15000],
            
            // Vascular
            ['name' => 'Ardium 500 mg', 'category' => 'Vascular', 'price_retail' => 12000],
            ['name' => 'Ardium 1000 mg', 'category' => 'Vascular', 'price_retail' => 20000],
            
            // Insect Repellent
            ['name' => 'Autan Sachet', 'category' => 'Insect Repellent', 'price_retail' => 2000],
            ['name' => 'Autan Tube 50 ml', 'category' => 'Insect Repellent', 'price_retail' => 8000],
            
            // Health Drinks
            ['name' => 'AMH Jahe', 'category' => 'Health Drink', 'price_retail' => 5000],
            ['name' => 'AMH Etawa', 'category' => 'Health Drink', 'price_retail' => 8000],
            ['name' => 'AMH Susu Jahe', 'category' => 'Health Drink', 'price_retail' => 6000],
            
            // Fever Reducer
            ['name' => 'Anastan F', 'category' => 'Antipyretic', 'price_retail' => 3000],
            
            // Miscellaneous
            ['name' => 'Adrome Tab', 'category' => 'Supplement', 'price_retail' => 4000],
            ['name' => 'Avocel', 'category' => 'Supplement', 'price_retail' => 12000],
            ['name' => 'Akurat', 'category' => 'Medical Supply', 'price_retail' => 8000],
            ['name' => 'Actived', 'category' => 'Supplement', 'price_retail' => 15000],
            ['name' => 'Asepso', 'category' => 'Antiseptic', 'price_retail' => 5000],
            ['name' => 'Avail', 'category' => 'Supplement', 'price_retail' => 10000]
        ];

        foreach ($products as $index => $productData) {
            // Generate SKU and barcode
            $sku = 'PHR' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $barcode = '8901234' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            
            // Calculate prices based on retail price
            $retailPrice = $productData['price_retail'];
            $baseCost = $retailPrice * 0.7; // 30% margin
            $semiGrosirPrice = $retailPrice * 0.9; // 10% discount from retail
            $grosirPrice = $retailPrice * 0.8; // 20% discount from retail
            
            // Random stock between 10-100
            $stockQuantity = rand(10, 100);
            
            $product = Product::create([
                'sku' => $sku,
                'barcode' => $barcode,
                'name' => $productData['name'],
                'category' => $productData['category'],
                'unit_id' => $unitId,
                'base_cost' => $baseCost,
                'price_retail' => $retailPrice,
                'price_semi_grosir' => $semiGrosirPrice,
                'price_grosir' => $grosirPrice,
                'min_margin_pct' => 30.00,
                'default_price_type' => 'retail',
                'current_stock' => $stockQuantity,
                'status' => 'active'
            ]);
            
            // Create warehouse stock record for the main store
            ProductWarehouseStock::create([
                'product_id' => $product->id,
                'warehouse_id' => $storeWarehouse->id,
                'stock_on_hand' => $stockQuantity,
                'reserved_stock' => 0,
                'safety_stock' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->info('Created ' . count($products) . ' pharmacy products with stock in ' . $storeWarehouse->name);
    }
}