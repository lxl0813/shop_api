<?php

namespace App\Console\Commands;

use App\Exceptions\ApiException;
use App\Model\GoodsOrders;
use App\Model\Order;
use App\Model\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrderToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *+-
     *
     * @return mixed
     */
    public function handle()
    {
        $order=json_decode(Redis::rpop("order"),true);
        //var_dump($order);exit;
        $order_info=array_shift($order);
        //print_r($order_in);exit;
        DB::beginTransaction();
        $order_id=Order::insterGetID($order);
        //var_dump($order_id);exit;
        if(!$order_id){
            DB::rollback();
        }else{
            foreach($order_info as $k=>$v){
                $order_info[$k]["order_id"]=$order_id;
            }
            foreach($order_info as $key=>$value){
                if(!GoodsOrders::inster($value)){
                    DB::rollback();
                }
                if(Product::where("products_id",$value["products_id"])->update(["products_num"=>"products_num"-$value["products_num"]])){
                    DB::rollback();
                }
            }
        }
        DB::commit();
    }
}
