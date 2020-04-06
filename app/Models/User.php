<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends Base
{
    protected $table = 'user';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';

}
