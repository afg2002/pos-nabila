<?php

namespace Database\Factories;

use App\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        // Realistic Indonesian product names by category
        $categories = [
            'Makanan' => [
                'Nasi Goreng Spesial', 'Ayam Geprek Sambal Matah', 'Gado-Gado Jakarta', 'Soto Ayam Lamongan',
                'Rendang Daging Sapi', 'Gudeg Jogja Original', 'Bakso Malang Spesial', 'Mie Ayam Pangsit',
                'Pecel Lele Sambal Kecap', 'Nasi Padang Komplit', 'Sate Kambing Madura', 'Rawon Daging Sapi',
                'Ketoprak Jakarta', 'Bubur Ayam Kuah', 'Nasi Uduk Betawi', 'Ayam Bakar Taliwang'
            ],
            'Minuman' => [
                'Es Teh Manis', 'Kopi Hitam Robusta', 'Jus Jeruk Segar', 'Es Campur Jakarta',
                'Teh Tarik Malaysia', 'Kopi Latte Premium', 'Jus Alpukat Susu', 'Es Kelapa Muda',
                'Cappuccino Original', 'Jus Mangga Harum Manis', 'Es Cincau Hijau', 'Wedang Jahe Merah',
                'Americano Coffee', 'Jus Tomat Segar', 'Es Doger Betawi', 'Susu Jahe Hangat'
            ],
            'Elektronik' => [
                'Smartphone Samsung Galaxy A54', 'Laptop ASUS VivoBook 14', 'Earphone Sony WH-1000XM4',
                'Power Bank Xiaomi 20000mAh', 'Charger Fast Charging 65W', 'Speaker Bluetooth JBL',
                'Mouse Gaming Logitech G502', 'Keyboard Mechanical RGB', 'Webcam HD 1080p',
                'Monitor LED 24 Inch', 'Printer Canon Pixma', 'Hard Disk External 1TB',
                'Flash Drive SanDisk 64GB', 'Tablet iPad Air 256GB', 'Smartwatch Apple Watch SE', 'Router WiFi TP-Link'
            ],
            'Pakaian' => [
                'Kaos Polo Katun Premium', 'Kemeja Batik Pria Lengan Panjang', 'Celana Jeans Slim Fit',
                'Jaket Hoodie Fleece', 'Dress Casual Wanita', 'Blouse Chiffon Elegant', 'Celana Kulot Wanita',
                'Sepatu Sneakers Canvas', 'Sandal Jepit Karet', 'Tas Selempang Kulit', 'Dompet Pria Kulit Asli',
                'Hijab Segi Empat Premium', 'Kacamata Sunglasses UV Protection', 'Topi Baseball Cap',
                'Kaos Kaki Katun', 'Ikat Pinggang Kulit Sapi'
            ],
            'Alat Tulis' => [
                'Pulpen Gel Pilot G2', 'Pensil 2B Faber-Castell', 'Penghapus Karet Putih', 'Penggaris Plastik 30cm',
                'Buku Tulis 38 Lembar', 'Stabilo Highlighter Set', 'Spidol Whiteboard Snowman',
                'Lem Stick UHU 21g', 'Gunting Kertas Kenko', 'Stapler Kenko HD-10', 'Paper Clip Plastik Warna',
                'Map Plastik L Warna', 'Amplop Putih Sedang', 'Lakban Bening 2 Inch', 'Kertas HVS A4 70gsm', 'Tinta Printer Canon'
            ],
            'Kesehatan' => [
                'Masker KN95 Medical Grade', 'Hand Sanitizer 60ml', 'Vitamin C 1000mg', 'Paracetamol 500mg',
                'Betadine Antiseptik 60ml', 'Minyak Kayu Putih 60ml', 'Balsem Geliga 20g', 'Termometer Digital',
                'Plester Luka Hansaplast', 'Alkohol 70% 100ml', 'Kapas Bulat Steril', 'Perban Elastis 6cm',
                'Tissue Basah Antibakteri', 'Sabun Cuci Tangan Lifebuoy', 'Obat Batuk Herbal', 'Madu Murni 250ml'
            ]
        ];
        
        // Select random category and product
        $category = $this->faker->randomElement(array_keys($categories));
        $productName = $this->faker->randomElement($categories[$category]);
        
        // Price calculation with realistic ranges per category
        $priceRanges = [
            'Makanan' => ['min' => 15000, 'max' => 75000],
            'Minuman' => ['min' => 5000, 'max' => 35000], 
            'Elektronik' => ['min' => 50000, 'max' => 15000000],
            'Pakaian' => ['min' => 25000, 'max' => 500000],
            'Alat Tulis' => ['min' => 2000, 'max' => 50000],
            'Kesehatan' => ['min' => 10000, 'max' => 200000]
        ];
        
        $baseCost = $this->faker->numberBetween(
            $priceRanges[$category]['min'], 
            $priceRanges[$category]['max']
        );
        
        // Calculate retail price (30-80% markup)
        $retailPrice = $baseCost * $this->faker->randomFloat(2, 1.3, 1.8);
        
        // Calculate semi grosir price (20-50% markup) - between base cost and retail
        $semiGrosirPrice = $baseCost * $this->faker->randomFloat(2, 1.2, 1.5);
        
        // Calculate grosir price (10-30% markup) - lowest markup
        $grosirPrice = $baseCost * $this->faker->randomFloat(2, 1.1, 1.3);
        
        // Determine appropriate unit for category (using unit_id)
        $categoryUnits = [
            'Makanan' => [1, 7, 8], // Pieces, Pack, Kg
            'Minuman' => [1, 6, 7], // Pieces, Liter, Pack
            'Elektronik' => [1, 4], // Pieces, Box
            'Pakaian' => [1, 7], // Pieces, Pack
            'Alat Tulis' => [1, 7, 4, 2], // Pieces, Pack, Box, Lusin
            'Kesehatan' => [1, 7, 4] // Pieces, Pack, Box
        ];
        
        $unitId = $this->faker->randomElement($categoryUnits[$category]);
        
        // Stock levels based on category
        $stockRanges = [
            'Makanan' => [5, 50],
            'Minuman' => [10, 100],
            'Elektronik' => [1, 25],
            'Pakaian' => [5, 50],
            'Alat Tulis' => [20, 200],
            'Kesehatan' => [10, 100]
        ];
        
        $stock = $this->faker->numberBetween(
            $stockRanges[$category][0],
            $stockRanges[$category][1]
        );
        
        // Price type distribution
        $priceTypes = ['retail', 'semi_grosir', 'grosir'];
        $defaultPriceType = $this->faker->randomElement($priceTypes);
        
        return [
            'sku' => strtoupper($category[0] . $category[1]) . '-' . $this->faker->unique()->numerify('####'),
            'barcode' => $this->faker->unique()->numerify('############'),
            'name' => $productName,
            'category' => $category,
            'unit_id' => $unitId,
            'base_cost' => $baseCost,
            'price_retail' => round($retailPrice, -2), // Round to nearest hundred
            'price_semi_grosir' => round($semiGrosirPrice, -2),
            'price_grosir' => round($grosirPrice, -2),
            'default_price_type' => $defaultPriceType,
            'min_margin_pct' => $this->faker->randomFloat(2, 10, 25),
            'current_stock' => $stock,
            'is_active' => $this->faker->boolean(95), // 95% active
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            // Create initial stock movement with system user (ID 1)
            \App\StockMovement::create([
                'product_id' => $product->id,
                'qty' => $product->current_stock,
                'type' => 'IN',
                'ref_type' => 'initial_stock',
                'ref_id' => null,
                'note' => 'Initial stock from seeder',
                'performed_by' => 1, // Assuming user ID 1 exists (admin)
            ]);
        });
    }
}