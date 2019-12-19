<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "goods_id"=>$this->goods_id,
            "goods_name"=>$this->goods_name,
            "goods_img"=>$this->goods_img,
            "goods_price"=>$this->goods_price,
            "goods_desc"=>$this->goods_small_content,
            "goods_market_price"=>$this->goods_market_price
        ];
    }
}