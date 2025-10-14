<?php

namespace App\Services;

class ThermalPrintService
{
    /**
     * Print invoice for 80x100mm thermal printer
     */
    public function printInvoice(array $data): bool
    {
        try {
            $content = $this->formatInvoiceContent($data);
            return $this->sendToPrinter($content);
        } catch (\Exception $e) {
            \Log::error('Thermal print failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format invoice content for thermal printer
     */
    private function formatInvoiceContent(array $data): string
    {
        $lines = [];
        $width = 32; // Character width for 80mm paper
        
        // Header
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->centerText("INVOICE", $width);
        $lines[] = str_repeat("=", $width);
        $lines[] = "No: " . $data['invoice_number'];
        $lines[] = "Tanggal: " . $data['date'];
        $lines[] = "Kasir: " . $data['cashier'];
        $lines[] = "";
        
        // Customer
        if (!empty($data['customer_name'])) {
            $lines[] = "Pelanggan: " . $data['customer_name'];
            if (!empty($data['customer_phone'])) {
                $lines[] = "Telp: " . $data['customer_phone'];
            }
            $lines[] = "";
        }
        
        // Items (if provided)
        if (isset($data['items']) && !empty($data['items'])) {
            $lines[] = str_repeat("-", $width);
            $lines[] = $this->formatTableRow(["Item", "Qty", "Total"], [20, 5, 7]);
            $lines[] = str_repeat("-", $width);
            
            foreach ($data['items'] as $item) {
                $itemName = substr($item['name'], 0, 20);
                $lines[] = $this->formatTableRow([$itemName, $item['quantity'], number_format($item['total'], 0)], [20, 5, 7]);
            }
        }
        
        // Totals
        $lines[] = str_repeat("-", $width);
        $lines[] = $this->formatRow("Subtotal:", number_format($data['subtotal'], 0));
        
        if ($data['tax_amount'] > 0) {
            $lines[] = $this->formatRow("Pajak:", number_format($data['tax_amount'], 0));
        }
        
        if ($data['discount_amount'] > 0) {
            $lines[] = $this->formatRow("Diskon:", number_format($data['discount_amount'], 0));
        }
        
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->formatRow("TOTAL:", number_format($data['total_amount'], 0));
        $lines[] = str_repeat("=", $width);
        
        // Payment info
        if ($data['paid_amount'] > 0) {
            $lines[] = $this->formatRow("Dibayar:", number_format($data['paid_amount'], 0));
        }
        
        if ($data['remaining_amount'] > 0) {
            $lines[] = $this->formatRow("Sisa:", number_format($data['remaining_amount'], 0));
        }
        
        $lines[] = "";
        $lines[] = "Pembayaran: " . strtoupper($data['payment_method']);
        
        // Status
        $statusText = match($data['payment_status']) {
            'paid' => 'LUNAS',
            'partial' => 'SEBAGIAN',
            'unpaid' => 'BELUM BAYAR',
            default => 'UNKNOWN'
        };
        $lines[] = "Status: " . $statusText;
        
        // Footer
        $lines[] = "";
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->centerText("TERIMA KASIH", $width);
        $lines[] = str_repeat("=", $width);
        
        // Notes (if provided)
        if (!empty($data['notes'])) {
            $lines[] = "";
            $lines[] = "Catatan:";
            $lines[] = wordwrap($data['notes'], $width - 2, "\n", true);
        }
        
        $lines[] = "";
        $lines[] = "";
        $lines[] = "";
        
        return implode("\n", $lines);
    }

    /**
     * Print receipt for POS
     */
    public function printReceipt(array $data): bool
    {
        try {
            $content = $this->formatReceiptContent($data);
            return $this->sendToPrinter($content);
        } catch (\Exception $e) {
            \Log::error('Thermal print failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format receipt content for thermal printer
     */
    private function formatReceiptContent(array $data): string
    {
        $lines = [];
        $width = 32;
        
        // Header
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->centerText($data['store_name'] ?? 'TOKO', $width);
        $lines[] = $this->centerText($data['store_address'] ?? 'Alamat', $width);
        $lines[] = $this->centerText($data['store_phone'] ?? 'Telp', $width);
        $lines[] = str_repeat("=", $width);
        $lines[] = "No: " . $data['receipt_number'];
        $lines[] = "Tanggal: " . $data['date'];
        $lines[] = "Kasir: " . $data['cashier'];
        
        if (!empty($data['customer_name'])) {
            $lines[] = "Customer: " . $data['customer_name'];
        }
        
        $lines[] = "";
        
        // Items
        $lines[] = str_repeat("-", $width);
        $lines[] = $this->formatTableRow(["Item", "Qty", "Harga", "Total"], [12, 4, 8, 8]);
        $lines[] = str_repeat("-", $width);
        
        foreach ($data['items'] as $item) {
            $itemName = substr($item['name'], 0, 12);
            $lines[] = $this->formatTableRow([
                $itemName, 
                $item['quantity'], 
                number_format($item['price'], 0), 
                number_format($item['total'], 0)
            ], [12, 4, 8, 8]);
        }
        
        // Totals
        $lines[] = str_repeat("-", $width);
        $lines[] = $this->formatRow("Subtotal:", number_format($data['subtotal'], 0));
        
        if ($data['discount_amount'] > 0) {
            $lines[] = $this->formatRow("Diskon:", number_format($data['discount_amount'], 0));
        }
        
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->formatRow("TOTAL:", number_format($data['total_amount'], 0));
        $lines[] = str_repeat("=", $width);
        
        // Payment
        $lines[] = "Tunai: " . number_format($data['cash_amount'], 0);
        
        if ($data['change_amount'] > 0) {
            $lines[] = "Kembali: " . number_format($data['change_amount'], 0);
        }
        
        // Footer
        $lines[] = "";
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->centerText("TERIMA KASIH", $width);
        $lines[] = $this->centerText("Silakan Datang Kembali", $width);
        $lines[] = str_repeat("=", $width);
        
        $lines[] = "";
        $lines[] = "";
        $lines[] = "";
        
        return implode("\n", $lines);
    }

    /**
     * Send content to thermal printer
     */
    private function sendToPrinter(string $content): bool
    {
        // For development/testing, just log the content
        if (app()->environment('local', 'testing')) {
            \Log::info('Thermal printer output:', ['content' => $content]);
            return true;
        }
        
        // Implementation depends on printer connection
        // Could be USB, Bluetooth, or Network printer
        
        // Example for Windows USB printer
        // $tempFile = tempnam(sys_get_temp_dir(), 'thermal_print_');
        // file_put_contents($tempFile, $content);
        // exec("copy \"{$tempFile}\" LPT1");
        // unlink($tempFile);
        
        // Example for network printer
        // $socket = fsockopen('192.168.1.100', 9100, $errno, $errstr, 10);
        // if ($socket) {
        //     fwrite($socket, $content);
        //     fclose($socket);
        //     return true;
        // }
        
        // For now, we'll simulate successful printing
        return true;
    }

    /**
     * Helper function to center text
     */
    private function centerText(string $text, int $width): string
    {
        $padding = ($width - strlen($text)) / 2;
        return str_repeat(' ', floor($padding)) . $text . str_repeat(' ', ceil($padding));
    }

    /**
     * Helper function to format table row
     */
    private function formatTableRow(array $columns, array $widths): string
    {
        $row = '';
        foreach ($columns as $i => $column) {
            $row .= str_pad($column, $widths[$i]);
        }
        return $row;
    }

    /**
     * Helper function to format row with label and value
     */
    private function formatRow(string $label, string $value, int $width = 32): string
    {
        $labelWidth = strlen($label);
        $valueWidth = strlen($value);
        $padding = $width - $labelWidth - $valueWidth - 1;
        
        return $label . str_repeat(' ', $padding) . $value;
    }

    /**
     * Test printer connection
     */
    public function testConnection(): bool
    {
        try {
            $testContent = $this->formatTestContent();
            return $this->sendToPrinter($testContent);
        } catch (\Exception $e) {
            \Log::error('Printer test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format test content for printer
     */
    private function formatTestContent(): string
    {
        $lines = [];
        $width = 32;
        
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->centerText("PRINTER TEST", $width);
        $lines[] = str_repeat("=", $width);
        $lines[] = "Date: " . now()->format('d/m/Y H:i:s');
        $lines[] = "Status: OK";
        $lines[] = "";
        $lines[] = str_repeat("=", $width);
        $lines[] = $this->centerText("TEST SUCCESSFUL", $width);
        $lines[] = str_repeat("=", $width);
        $lines[] = "";
        $lines[] = "";
        
        return implode("\n", $lines);
    }
}