<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SatuanBahan extends Model
{
    protected $table = 'tb_satuan_bahan';
    protected $primaryKey = 'id_satuan';
    protected $fillable = [
        'satuan'
    ];
}
