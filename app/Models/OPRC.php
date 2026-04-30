<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OPRC extends Model
{
    protected $connection = 'sqlsrv_sap';

    protected $table = 'oprc';
    protected $primaryKey = 'PrcCode';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
}
