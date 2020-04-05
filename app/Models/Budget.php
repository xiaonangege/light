<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;
use GuzzleHttp;

class Budget extends Base
{
    protected $table = 'budget';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';


}