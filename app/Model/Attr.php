<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Attr extends Model{
    public $timestamps=false;
    protected $table="attr";
    protected $primaryKey = "attr_id";


}