<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Type extends Model{
    public $timestamps=false;
    protected $table="type";
    protected $primaryKey = "type_id";


}