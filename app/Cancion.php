<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cancion extends Model
{
     /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['nombre','artista_id','genero_id','duracion'];   
    /**
     * Cancion belongs to Artista.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function artista()
    {
    	// belongsTo(RelatedModel, foreignKey = artista_id, keyOnRelatedModel = id)
    	return $this->belongsTo(Artista::class);
    }

    /**
     * Cancion belongs to Genero.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function genero()
    {
    	// belongsTo(RelatedModel, foreignKey = genero_id, keyOnRelatedModel = id)
    	return $this->belongsTo(Genero::class);
    }

    public function listas(){    //
   		return $this->belongsToMany(Lista::class,'pivots','cancion_id','lista_id');
  	}

    public function infoSong(){
        return ".Titulo: " .$this->nombre."\n".
                "Genero: ".$this->genero->nombre."\n".
                "Artista: ".$this->artista->nombre. "\n".
                "Duración: ".$this->duracion." min";
    }
}
