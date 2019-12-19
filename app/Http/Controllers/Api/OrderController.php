<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Model\Good;
use App\Model\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;

class OrderController extends Controller
{
    //生成需要确认的订单
    public function order(Request $request){
        $product=$request->input();
        $product_info=[];
        foreach($product as $k=>$v){
            $pro=Product::find($v['product_id'])->toArray();
            foreach($pro as $key=>$val){
                $pro["cart_number"]=$v['cart_number'];
            }
            $product_info[]=$pro;
        }
        //var_dump($product_info);exit;
        foreach($product_info as $k=>$v){
            $good_name=Good::find($v['goods_id'],["goods_name","goods_img"])->toArray();
            $product_info[$k]["goods_name"]=$good_name["goods_name"];
            $product_info[$k]["goods_img"]=$good_name["goods_img"];
            $product_info[$k]["goods_attrs_value"]=explode("|",trim($v['goods_attrs_value'],"|"));
        }
        //var_dump($product_info);
        return response(["status"=>20000,"message"=>"ok","data"=>$product_info],200);

    }

    //对提交的订单进行审核
    public function examineOrder(Request $request){
        $examineOrder=$request->input();
        //var_dump($examineOrder);exit;
        $user_id=$request->user_id;
        //将货品的数量添加到redis队列中（这一步应该是在管理员添加货品时就应该存进去的，这边做一个模拟）
        //$pro_name="pro".":".$user_id.":"."36";
        //var_dump($user_id);exit;
//        for($i=1;$i<=3;$i++){
//            $a=Redis::lpush($pro_name,1);
//        }
//        var_dump($a)
        $lock="li".$user_id;
        try{
            Redis::del($lock);
            if(Redis::setnx($lock,3)){

                //循环，根据里面的订单数量，去redis中减去订单数量
                $num=[];//以货品id作键，踢出数量作值；
                foreach($examineOrder as $k=>$v){
                    if($v["products_id"]==0){
                        $pro_name="goods:".$user_id.":"."0";
                    }else{
                        $pro_name="pro:".$user_id.":".$v['products_id'];
                    }
                    //踢出订单购买数量；
                    $outNum=Redis::lrem($pro_name,$v["cart_number"],"1");
                    $num[$pro_name]=$outNum;//redis键作数组键，踢出值作值
                    //如果直接踢出数量小于订单商品数量，循环塞进去，然后报出异常
                    if($outNum<$v["cart_number"]){
                        foreach($num as $k1=>$v1){
                            //var_dump($v1);exit;
                            for($i=1;$i<=$v1;$i++){
                                Redis::lpush($pro_name,1);
                            }
                        }
                        throw new ApiException($v["goods_name"]."库存不足哦！");
                    }

                    /*
                     * 查询订单商品最新信息（主要对比价格），
                     * 从优化角度考虑，应该从redis中取（可在后台添加货品时，将货品信息存入redis中，修改时也修改redis）
                     * 由于之前没有存入redis，这边从数据库中取
                    */
                    $pro_info=Product::find($v["products_id"],["products_price","goods_id"])->toArray();
                   //var_dump($pro_price);exit;
                    $pro_info["products_priceAll"]=$pro_info["products_price"]*$v["cart_number"];
                    $pro_info["products_num"]=$v["cart_number"];
                    $pro_info["products_id"]=$v["products_id"];
                    $order_info["products_info"][]=$pro_info;
                    //var_dump($order_info);
                    //计算该订单总价格（各货品数量乘以价格相加）
                    $pay_money=0;
                    foreach($order_info["products_info"] as $k2=>$v2){
                        $pay_money+=$v2["products_priceAll"];
                    }
                    $order_info["pay_money"]=$pay_money;
                    //订单生成时间
                    $order_info["order_stime"]=time();
                    //用户id
                    $order_info["user_id"]=$user_id;
                    //生成唯一的订单号
                    $year_code = array('A','B','C','D','E','F','G','H','I','J');
                    $order_sn = $year_code[intval(date('Y'))-2010].strtoupper(dechex(date('m'))).date('d').substr(time(),-5).substr(microtime(),2,5).sprintf('d',rand(0,99));
                    //var_dump($order_sn);
                    $order_info["order_sn"]=$order_sn;
                    var_dump($order_info);
                    //将处理好的订单存入redis
                    if(!Redis::lpush("order",json_encode($order_info))){
                        throw new ApiException("网络异常");
                    }
                    Redis::del($lock);
                    return response(["status"=>"20000","message"=>"订单生成"],200);
                }
                //var_dump($order_info);
            }else{
                return response(["status"=>"20000","message"=>"订单已生成"],200);
            }
        }catch(ApiException $e){
            Redis::del($lock);
            return response(["status"=>"500","message"=>$e->getMessage()],500);
        }
    }
}
