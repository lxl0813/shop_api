<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GoodsAttr extends Model
{
    public $timestamps=false;
    protected $table="goods_attr";
    protected $primaryKey = "goods_attr_id";
    public function attr()
    {
        return $this->belongsTo('App\Model\Attr','attr_id','attr_id');
    }
}
