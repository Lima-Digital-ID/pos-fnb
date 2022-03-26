<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $table = 'tb_bahan';

    public function satuan()
    {
        return $this->belongsTo(\App\SatuanBahan::class, 'id_satuan');
    }
}
