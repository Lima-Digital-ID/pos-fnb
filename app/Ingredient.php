<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $table = 'tb_bahan';
    protected $primaryKey = 'id_bahan';
    protected $fillable = [
        'nama_bahan', 'id_satuan', 'stok', 'limit_stok', 'limit_pemakaian'
    ];
    public function satuan()
    {
        return $this->belongsTo(\App\SatuanBahan::class, 'id_satuan');
    }
    public function satuan_besar()
    {
        return $this->belongsTo(\App\SatuanBesar::class, 'id_satuan_besar');
    }
}
