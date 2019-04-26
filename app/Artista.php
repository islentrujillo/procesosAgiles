<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artista extends Model
{
    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['nombre'];

    /**
     * Artista has many Canciones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function canciones()
    {
    	// hasMany(RelatedModel, foreignKeyOnRelatedModel = artista_id, localKey = id)
    	return $this->hasMany(Cancion::class);
    }
}
