<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    /**
     * Relation: Un département a plusieurs communes
     */
    public function communes()
    {
        return $this->hasMany(Commune::class);
    }

    /**
     * Relation: Un département a plusieurs clients
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
