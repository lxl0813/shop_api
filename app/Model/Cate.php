<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cate extends Model{
    public $timestamps=false;
    protected $table="cates";
    protected $primaryKey = "cates_id";
    public function good(){
        return $this->belongsToMany("App\Model\Good","goods_cates","cates_id","goods_id");
    }

}