<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Judge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    // Sudac pripada natjecanju.
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
