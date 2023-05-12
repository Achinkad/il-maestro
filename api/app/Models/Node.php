<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'token'
    ];

    public function user() {
        return $this->belongsToMany(User::class, 'node_user', 'node_id', 'user_id')
            ->withPivot('node_id', 'user_id');
    }
}
