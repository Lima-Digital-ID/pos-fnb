<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SatuanBesar extends Model
{
    protected $table = 'tb_satuan_besar';
    protected $primaryKey = 'id_satuan_besar';
    protected $fillable = [
        'satuan_besar'
    ];
}
