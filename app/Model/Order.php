<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps=false;
    protected $primaryKey = "order_id";
}
