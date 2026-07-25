<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'period',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
