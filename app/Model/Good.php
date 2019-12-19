<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Good extends Model
{
    public $timestamps=false;
    protected $primaryKey = "goods_id";
    public function attr(){
        return $this->belongsToMany("App\Model\Attr","goods_attr","goods_id","attr_id");
    }
    public function product(){
        return $this->belongsToMany("App\Model\Product","products","goods_id","products_id");
    }
}
