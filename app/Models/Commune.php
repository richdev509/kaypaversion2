<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $fillable = ['department_id', 'name'];

    /**
     * Relation: Une commune appartient à un département
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relation: Une commune a plusieurs villes
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Relation: Une commune a plusieurs clients
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
