<?php

namespace App\Filament\Pages;

use App\Models\Service;
use App\Services\QueueService;
use App\Services\ThermalPrinterService;
use Filament\Pages\Page;

class Queuekiosk extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.queuekiosk';

    protected static string $layout = 'filament.layouts.base-kiosk';

    protected static ?string $navigationLabel = 'Tiket Antrian';

    protected ThermalPrinterService $thermalPrinterService;

    protected QueueService $queueService;   

    public function __construct()
    {
        $this->thermalPrinterService = app(ThermalPrinterService::class);
        $this->queueService = app(QueueService::class);
    }

    public function getViewData() : array
    {
        return [
            'services' => Service::where('is_active', true)->get()
        ];
    }

    public function print($serviceId)
    {
        $newQueue = $this->queueService->addQueue($serviceId);

        $text = $this->thermalPrinterService->createText([
            ['text' => 'Sistem Antrian AlfatihDev', 'align' => 'center'],
            ['text' => 'Jl. AlfatihDev', 'align' => 'center'],
            ['text' => '---------------------', 'align' => 'center'],
            ['text' => 'NOMOR ANTRIAN', 'align' => 'center'],
            ['text' => $newQueue->number, 'align' => 'center', 'style' =>'double'],
            ['text' => 'Layanan : ' . $newQueue->service->name, 'align' => 'center'],
            ['text' => $newQueue->created_at->format('d-m-Y H:i'), 'align' => 'center'],
            ['type' => 'qrcode', 'data' => route('queue.status', ['id' => generate_id($newQueue->id)]), 'size' => 6, 'align' => 'center'],
            ['text' => 'Silahkan Scan QRCode Di Atas', 'align' => 'center'],
            ['text' => 'Untuk Melihat Status Antrian', 'align' => 'center'],
            ['text' => '---------------------', 'align' => 'center'],
            ['text' => 'Terima Kasih', 'align' => 'center'],
        ]);
      
        // manggil event print-start
        $this->dispatch("print-start", $text);
    }
}
