<?php

namespace App\Filament\Pages;

use App\Models\Counter;
use App\Models\Queue;
use Filament\Pages\Page;

class DashboardKiosk extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.pages.dashboard-kiosk';

    protected static string $layout = 'filament.layouts.base-kiosk';

    protected static ?string $navigationLabel = 'Ruang Tunggu';

    public function getViewData() : array
    {
        return [
            'counters' => Counter::with(['service', 'activeQueue'])->get()
        ];
    }

    public function callNextQueue()
    {
        $nextQueues = Queue::where('status', 'waiting')
                        ->whereDate('created_at', now()->format('Y-m-d'))
                        ->whereNull('called_at')
                        ->get();

        foreach($nextQueues as $nextQueue){
            // cek counter_id
            if(!$nextQueue->counter) continue;

            $this->dispatch('queue-called', 'Nomor Antrian ' . $nextQueue->number . ' Silahkan Ke ' . $nextQueue->counter->name);

            $nextQueue->update([
                'called_at' => now(),
            ]);
        }
    }   
}
