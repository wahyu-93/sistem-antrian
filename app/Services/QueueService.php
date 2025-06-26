<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use Hamcrest\Core\IsSame;

class QueueService {
    public function addQueue($serviceId)
    {
        $number = $this->generateNumber($serviceId);

        // simpan data di table queue
        return Queue::create([
            'service_id'    => $serviceId,
            'number'        => $number,
            'status'        => 'waiting'
        ]);
    }

    public function generateNumber($serviceId)
    {
        // get table service 
        $service = Service::findOrFail($serviceId);

        // ambil field terakhir berdasarkan service_id
        $lastQueue = Queue::where('service_id', $serviceId)
                    ->orderByDesc('id')
                    ->first();

        // mengecek kondisi tgl sekarang
        $currentDate = now()->format('Y-m-d');
        $lastQueueDate = $lastQueue ? $lastQueue->created_at->format('Y-m-d') : null;

        $isSameDate = $currentDate === $lastQueueDate;


        // last queue ada funsi pengeeckan juga jika data null maka otomastis nilai lastQueueNumber = 0
        // mengambil number yang asal A001 -> dijadikan output menjadi 001
        // kemudian diconversikan menjadikan bilangan bulat dengan fungis intval
        // ouputlastQueueNumber jika tidak tidak null ada lah 00X
        $lastQueueNumber = $lastQueue ? intval(substr($lastQueue->number, strlen($service->prefix))) : 0;

        $maximumNumber = pow(10, $service->padding) - 1;

        $isMaximumNumber = $lastQueueNumber === $maximumNumber;

        if($isSameDate && !$isMaximumNumber){
            $newQueueNumber = $lastQueueNumber + 1;
        }
        else {
            $newQueueNumber = 1;
        }

        return $service->prefix . str_pad($newQueueNumber, $service->padding, "0", STR_PAD_LEFT);
    } 

    public function getNextQueue($counterId)
    {
        // get nilai counter berdasarkan id
        $counter = Counter::findOrFail($counterId);

        return Queue::where('status', 'waiting')
                    ->where('service_id', $counter->service_id)
                    ->where(function($query) use ($counterId){
                        $query->whereNull('counter_id')->orWhere('counter_id', $counterId);
                    })
                    ->orderBy('id')
                    ->first();
    }

    public function callNextQueue($counterId)
    {
        $nextQueue = $this->getNextQueue($counterId);
        
        // update counter_id di table queue
        if($nextQueue && !$nextQueue->counter_id){
            // update table queue
            $nextQueue->update([
                'counter_id'    => $counterId,
                'called_at'     => now(),
            ]);
        }   
        
        return $nextQueue;
    }

    public function serveQueue(Queue $queue)
    {
        if($queue === "waiting"){
            return;
        };

        $queue->update([
            'status'    => 'serving',
            'served_at' => now(),
        ]);
    }

    public function finishQueue(Queue $queue)
    {
        if($queue === "serving"){
            return;
        };

        $queue->update([
            'status'        => 'finish',
            'finished_at'   => now(),
        ]);
    }

    public function cancelQueue(Queue $queue)
    {
        $queue->update([
            'status'    => 'cancel',
            'canceled_at' => now(),
        ]);
    }
}