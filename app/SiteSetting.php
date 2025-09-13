<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'brand_name',
        'tagline',
        'address',
        'maps_lat',
        'maps_lng',
        'maps_url',
        'whatsapp',
        'email',
        'instagram',
        'hero_image_url',
        'gallery_json',
        'copyright'
    ];

    protected $casts = [
        'maps_lat' => 'decimal:8',
        'maps_lng' => 'decimal:8',
        'gallery_json' => 'array'
    ];

    // Method untuk mendapatkan setting pertama (singleton pattern)
    public static function getSettings()
    {
        return self::first() ?? self::create([
            'brand_name' => 'POS Nabila',
            'tagline' => 'Sistem Point of Sale Terpercaya',
            'copyright' => '© 2025 POS Nabila. All rights reserved.'
        ]);
    }

    // Method untuk update setting
    public static function updateSettings(array $data)
    {
        $settings = self::getSettings();
        return $settings->update($data);
    }

    // Method untuk mendapatkan gallery images
    public function getGalleryImages()
    {
        return $this->gallery_json ?? [];
    }

    // Method untuk menambah gallery image
    public function addGalleryImage($imageUrl)
    {
        $gallery = $this->getGalleryImages();
        $gallery[] = $imageUrl;
        $this->gallery_json = $gallery;
        $this->save();
    }

    // Method untuk remove gallery image
    public function removeGalleryImage($index)
    {
        $gallery = $this->getGalleryImages();
        if (isset($gallery[$index])) {
            unset($gallery[$index]);
            $this->gallery_json = array_values($gallery);
            $this->save();
        }
    }

    // Method untuk format nomor WhatsApp
    public function getFormattedWhatsapp()
    {
        if (!$this->whatsapp) return null;
        
        // Remove non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp);
        
        // Add country code if not present
        if (!str_starts_with($number, '62')) {
            $number = '62' . ltrim($number, '0');
        }
        
        return $number;
    }

    // Method untuk generate WhatsApp URL
    public function getWhatsappUrl($message = null)
    {
        $number = $this->getFormattedWhatsapp();
        if (!$number) return null;
        
        $url = "https://wa.me/{$number}";
        if ($message) {
            $url .= '?text=' . urlencode($message);
        }
        
        return $url;
    }
}
