<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'counter_id', 'number', 'status', 'called_at', 'served_at', 'canceled_at', 'finished_at'];

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getKioskStatusAttribute()
    {
        if($this->status === 'waiting') return 'Dipanggil';
        if($this->status === 'serving') return 'Dilayani';
        return '';
    }
}
