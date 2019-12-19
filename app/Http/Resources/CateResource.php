<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CateResource extends JsonResource
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
          "cate_id"=>$this->cates_id,
          "text"=>$this->cates_name,
          "cate_img"=>$this->cates_img
        ];
    }
}
