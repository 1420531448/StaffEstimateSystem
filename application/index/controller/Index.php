<?php
namespace app\index\controller;

use app\index\model\Common;
use think\Controller;
use think\Db;
use think\Model;
use think\Request;

class Index extends Controller
{
    public function index()
    {

        return $this->fetch('index');
       /* return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';*/
    }
    public function test(){

        return $this->fetch('index');
    }
    /**
     * 评估结果列表
     * @return mixed
     */
    public function estimateResultList(){
        $station_name = Db::name('station_name')->select();
        $res = Db::name('station_estimate_result')->field('result_id,station_id,unit,estimate_date,estimate_name,station_detail_name,headman_sign,is_check')->select();
        foreach ($res as &$r){
            foreach ($station_name as $s){
                if($r['station_id'] == $s['station_id']){
                    $r['station_name'] = $s['station_name'];
                }
            }
        }
        return $this->fetch('estimateResultList',['result'=>$res]);
    }
    /**
     * 评估结果入库
     */
    public function getEstimateResult(){
        $post = getPost();
        $problem = [];
        $machine = '';
        $hole_depth = '';
        $section_experience = '';
        //处理人员类型
        if(!empty($post['type'])){
            $post['type'] = handleType($post['type']);;
        }

        //处理问题详情
        foreach ($post as $k=>$i){
            if(substr($k,0,10) == 'problem_id'){
                $problem_id = substr($k,10);
                $problem[$problem_id] = $i;
                unset($post[$k]);
            }
        }
        $problem = serialize($problem);
        $post['problem_detail'] = $problem;
        //处理钻机类型

        if(!empty($post['drilling_machine'])){
            foreach($post['drilling_machine'] as $m){
                $machine.=$m.';';
            }
        }

        $post['drilling_machine'] = $machine;
        //处理钻机井深类型
        foreach ($post['hole_depth'] as $h){
            $hole_depth.=$h.';';
        }
        $post['hole_depth'] = $hole_depth;
        //处理区块工作经验
        foreach ($post['section_experience'] as $s){
            $section_experience .= $s.';';
        }
        $post['section_experience'] = $section_experience;

        $res = Db::name('station_estimate_result')->insert($post);
        if($res==1){
            $this->success('提交成功','estimateResultList');
        }
         $this->error('提交失败','createTemplate');
    }
    /**
     * 评估结果修改
     * @return mixed|void
     */
    public function editEstimateResult($result_id=''){
        $post = getPost();
        if($post){
            //有修改
            $problem = [];
            $machine = '';
            $hole_depth = '';
            $section_experience = '';
//            dump($post);die;
            //处理人员类型
            if(!empty($post['type'])){
                $post['type'] = handleType($post['type']);;
            }

            //处理问题详情
            foreach ($post as $k=>$i){
                if(substr($k,0,10) == 'problem_id'){
                    $problem_id = substr($k,10);
                    $problem[$problem_id] = $i;
                    unset($post[$k]);
                }
            }
            $problem = serialize($problem);
            $post['problem_detail'] = $problem;
            //处理钻机类型
            foreach($post['drilling_machine'] as $m){
                $machine.=$m.';';
            }
            $post['drilling_machine'] = $machine;
            //处理钻机井深类型
            foreach ($post['hole_depth'] as $h){
                $hole_depth.=$h.';';
            }
            $post['hole_depth'] = $hole_depth;
            //处理区块工作经验
            foreach ($post['section_experience'] as $s){
                $section_experience .= $s.';';
            }
            $post['section_experience'] = $section_experience;

            $res = Db::name('station_estimate_result')->where(['result_id'=>$post['result_id']])->update($post);
            if($res==1){
                $this->success('提交成功','estimateResultList');
            }
            $this->error('提交失败或未修改','estimateResultList');
            die;
        }
        $station_name = Db::name('station_name')->select();
        $result = Db::name('station_estimate_result')->where(['result_id'=>$result_id])->find();
            foreach ($station_name as $s){
                if($result['station_id'] == $s['station_id']){
                    $result['station_name'] = $s['station_name'];
                }
            }

        $result['type'] = unHandleType($result['type']);

        //处理问题详情
        $problem = unserialize($result['problem_detail']);
        $sort = '';
        $problem_detail = Db::name("station_problem")->where(['station_id'=>$result['station_id']])->select();
        foreach ($problem_detail as $k=>&$pd){
            foreach ($problem as $i=>$p){
                if($pd['problem_id'] == $i){
                    $pd['value']=$p;
                }
            }
            $sort = $k;
        }
        //处理钻机类型数据
        $drilling_machine = $result['drilling_machine'];
        $drilling_machine = explode(';',$drilling_machine);
        //处理钻机可打深度类型数据
        $hole_depth = $result['hole_depth'];
        $hole_depth = explode(';',$hole_depth);
        //处理区块工作经验数据
        $section_experience = explode(';',$result['section_experience']);
        //后续问题排序
        $sort=$sort+2;
        return $this->fetch('estimateResultEdit',['result'=>$result,'problem_detail'=>$problem_detail,'drilling_machine'=>$drilling_machine,'hole_depth'=>$hole_depth,'section_experience'=>$section_experience,'sort'=>$sort]);
    }
    /**
     * 岗位评估问题添加
     * @return mixed
     */
    public function stationEstimateProblemAdd(){
        if(empty(getPost())){
          return $this->fetch('problemAdd');
        }

    }
    /**
     * 岗位列表
     * @return mixed
     */
    public function stationList(){
        $all = Db::name('station_name')->order('station_id','asc')->select();
        return $this->fetch('stationList',['all'=>$all]);
    }
    /**
     * 岗位添加
     * @return mixed
     */
    public function stationAdd(){
        if(empty(getPost())){
            return $this->fetch('stationAdd');
        }
        $post = getPost();
        $res= Db::name('station_name')->insert($post);
        if($res){
            $this->success('新增成功','stationList');
        }
        $this->error('新增失败','stationList');
    }
    /**
     * 岗位删除
     */
    public function stationDelete(){
        $id = getGet('station_id');
        $res = Db::name('station_name')->where('station_id',$id)->delete();
        if($res){
            $this->success('删除成功','stationList');
        }
        $this->error('删除失败','stationList');
    }
    /**
     * 岗位评估问题详情列表
     * @return mixed
     */
    public function problemDetail(){
        $station_id = getGet('station_id');
        if(!empty($station_id)){
            $station_name = Db::name('station_name')->where('station_id',$station_id)->find();
            $problem=Db::name('station_problem')
                ->where('station_id','=',$station_id)
                ->select();
            return $this->fetch('problemList',['problem'=>$problem,'station_name'=>$station_name,'station_id'=>$station_id]);
        }
    }
    /**
     * 岗位问题添加
     * @return mixed
     */
    public function problemAdd(){
        $post = getPost();
        if($post){
;            $res = Db::name('station_problem')->insert($post);
            if($res){
                $this->success('添加成功','problemDetail?station_id='.$post['station_id']);
            }
            $this->error('添加失败','problemDetail?station_id='.$post['station_id']);
            die;
        }
        return $this->fetch('problemAdd');
    }
    /**
     * 修改问题详情
     * @param $problem_id
     * @return mixed
     */
    public function editProblemDetail($problem_id){
        if(getPost('problem_id')){
            $post = getPost();
            $station = Db::name('station_problem')->field('station_id')->where('problem_id',$problem_id)->find();
            $res = Db::name('station_problem')->where('problem_id',$problem_id)->update($post);
            if($res){
                $this->success('修改成功',"ProblemDetail?station_id={$station['station_id']}");
            }
            $this->error('修改失败或无修改',"ProblemDetail?station_id={$station['station_id']}");
        }
        $res = Db::name('station_problem')->where('problem_id',$problem_id)->find();
        return $this->fetch('problemEdit',['detail'=>$res]);
    }
    /**
     * 删除问题详情
     * @param $problem_id
     */
    public function deleteProblemDetail($problem_id){
        $station = Db::name('station_problem')->field('station_id')->where('problem_id',$problem_id)->find();
        $res = Db::name('station_problem')->where('problem_id',$problem_id)->delete();
        if($res){
            $this->success('删除成功',"ProblemDetail?station_id={$station['station_id']}");
        }
        $this->success('删除失败',"ProblemDetail?station_id={$station['station_id']}");
    }
    /**
     * 逐级能力评估模板生成
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function createTemplate(){
        $post =getPost('station_id');
        $station_problem = Db::name('station_problem')->where('station_id',$post)->select();
        if($post && $station_problem){
            return $station_problem;
        }
        $station = Db::name('station_name')->select();
        return $this->fetch('template',['station_problem'=>$station_problem,'station'=>$station]);
    }
    /**
     * 评估结果显示页面
     * @param $result_id
     * @return mixed
     */
    public function screenEstimateProblem($result_id){
        $station_name = Db::name('station_name')->select();
        $result = Db::name('station_estimate_result')->where(['result_id'=>$result_id])->find();
        foreach ($station_name as $s){
            if($result['station_id'] == $s['station_id']){
                $result['station_name'] = $s['station_name'];
            }
        }

        $result['type'] = unHandleType($result['type']);

        //处理问题详情
        $problem = unserialize($result['problem_detail']);
        $sort = 0;
        $problem_detail = Db::name("station_problem")->where(['station_id'=>$result['station_id']])->select();
        foreach ($problem_detail as $k=>&$pd){
            foreach ($problem as $i=>$p){
                if($pd['problem_id'] == $i){
                    $pd['value']=$p;
                }
            }

            if($problem_detail[$k]['value']==1){
                unset($problem_detail[$k]);
            }else{
                $sort = $sort+1;
            }

        }
        //处理钻机类型数据
        $drilling_machine = $result['drilling_machine'];
        $drilling_machine = explode(';',$drilling_machine);
        //处理钻机可打深度类型数据
        $hole_depth = $result['hole_depth'];
        $hole_depth = explode(';',$hole_depth);
        //处理区块工作经验数据
        $section_experience = explode(';',$result['section_experience']);
        //后续问题排序
        $sort=$sort+2;

        if(empty($problem_detail)){
            return false;
        }
        return $this->fetch('needToSolveProblem',['result'=>$result,'problem_detail'=>$problem_detail,'drilling_machine'=>$drilling_machine,'hole_depth'=>$hole_depth,'section_experience'=>$section_experience,'sort'=>$sort-1]);
    }

    public function getSolveWays(){
        $post = getPost();
        if(!empty($post)){
            $is_check = Db::name('station_estimate_result')
                ->field('is_check')->where(['result_id'=>$post['result_id'],'station_id'=>$post['station_id'],'estimate_name'=>$post['estimate_name']])->find();
            if($is_check['is_check'] == 0){
                $post['way'] = serialize($post['way']);
                $insert_res = Db::name('station_solve_ways')->strict(false)->insert($post);
                if($insert_res ==1){
                    $res = Db::name('station_estimate_result')
                        ->field('is_check')->where(['result_id'=>$post['result_id'],'station_id'=>$post['station_id'],'estimate_name'=>$post['estimate_name']])->update(['is_check'=>1]);
                    if($res == 1){
                        $this->success('提交成功','estimateResultList');
                    }
                }
            }
        }else{
            $this->error('请勿非法提交数据','estimateResultList');
        }
    }

    /**
     * 统计结果列表
     * @return mixed
     */
    public function statisticsResultList(){
        $result = Db::name('station_estimate_result')
            ->field('station_id,estimate_name,station_detail_name,problem_detail')
            ->select();
        foreach($result as $i=>&$res){
            $res['problem_detail'] = unserialize($res['problem_detail']);
            foreach ($res['problem_detail'] as $k=>&$re){
                if($re == 1){
                    //剔除合格的问题
                    unset($res['problem_detail'][$k]);
                }
            }
            if(empty($res['problem_detail'])){
                unset($result[$i]);
            }
        }

        //重组数据
        $reResult = [];
        foreach ($result as $r){
            $station_name = Db::name('station_name')->field('station_name')->where('station_id',$r['station_id'])->find();

            foreach ($r['problem_detail'] as $i=>$item){
                $problem_description = Db::name('station_problem')->field('description')->where('problem_id',$i)->find();
                /*$reResult[$r['station_id']]['station_name']=;*/

                $reResult[$station_name['station_name']][$problem_description['description']][] = $r['estimate_name'].'('.($item==2?"部分":"否").')';

            }
        }
        foreach($reResult as &$l){
            foreach($l as &$v){
                $key = count($v);
                $v['sort'] = '该问题总人数为'.($key-1);
            }
        }
//dump($reResult);die;

        return $this->fetch('statisticsResultList',['result'=>$reResult]);
    }
}
