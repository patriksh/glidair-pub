<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'director',
        'logo',
        'rounds',
        'rounds_ignored',
        'date'
    ];

    protected $hidden = ['updated_at'];

    // Natjecanje sadrži više natjecatelja (sortiranih po stupcu "order").
    public function participants()
    {
        return $this->hasMany(Participant::class)->orderBy('order');
    }

    // Natjecanje sadrži više timova.
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    // Natjecanje sadrži više sudaca.
    public function judges()
    {
        return $this->hasMany(Judge::class);
    }
}
