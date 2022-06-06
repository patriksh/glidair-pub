<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'competition_id'
    ];

    protected $hidden = ['updated_at'];

    // Tim pripada natjecanju.
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    // Klub pripada većem broju natjecatelja.
    public function participants()
    {
        return $this->belongsToMany(Participant::class);
    }
}
