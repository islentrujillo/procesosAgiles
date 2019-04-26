<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genero extends Model
{
    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['nombre'];

    /**
     * Genero has many Canciones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function canciones()
    {
    	// hasMany(RelatedModel, foreignKeyOnRelatedModel = genero_id, localKey = id)
    	return $this->hasMany(Cancion::class);
    }
}
