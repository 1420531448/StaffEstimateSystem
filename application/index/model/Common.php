<?php
/**
 * Created by PhpStorm.
 * User: 14205
 * Date: 2019/9/27
 * Time: 17:11
 */
namespace app\index\model;
use think\Db;
use think\Model;

class Common extends Model {
    public function setThis(){
        $res = Db::name('db')->select();
        return ['res'=>$res];
    }
}