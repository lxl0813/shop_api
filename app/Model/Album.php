<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    public $timestamps=false;
    protected $primaryKey = "album_id";

}