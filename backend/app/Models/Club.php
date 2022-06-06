<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo'
    ];

    protected $hidden = ['updated_at'];

    // Klub pripada većem broju korisnika.
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
