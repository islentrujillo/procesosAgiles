<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['user_id','estado'];

    public function canciones(){    //
   		 return $this->belongsToMany(Cancion::class,'pivots','lista_id','cancion_id');//->withPivot('id_symptom','peso');
  	}
}
