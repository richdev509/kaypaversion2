<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['commune_id', 'name'];

    /**
     * Relation: Une ville appartient à une commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Relation: Une ville a plusieurs clients
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
