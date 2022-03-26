<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SatuanBahan extends Model
{
    use SoftDeletes;
    protected $table = 'tb_satuan_bahan';
}
