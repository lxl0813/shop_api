<?php
namespace App\Http\Controllers\Traits;
use Illuminate\Http\Resources\Json\JsonResource;

trait ResultTrait
{
    public function success(JsonResource $data,$status, $message="ok")
    {
        return response(["status"=>$status,"message"=>$message,"data"=>$data],200);
    }

    public function error( $status, $message="no")
    {
        return response(["status"=>$status,"message"=>$message],200);
    }
}