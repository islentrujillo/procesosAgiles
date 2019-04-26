<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pivot extends Model
{
    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['cancion_id','lista_id'];
}
