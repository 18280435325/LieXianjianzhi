<?php
/**
 * 兼职工作类.
 * User: zyy
 * Date: 2017/11/26
 * Time: 11:14
 */

namespace App;

class Job extends Base
{
    static $standardWords = [];

    /** 兼职列表
     * @return string
     */
    public function joblist()
    {
        try{
            $db = Base::$mysql;
            $today = date('Y-m-d H:i:s');
            $column = ['pid','title','content','address','start_time','end_time','wages','type',
                'labor_hour_type','clear_type'];
            $where = [
                'start_time[<=]'=>$today,
                'end_time[>=]'=>$today,
                'job_state'=>'1',
                'LIMIT'=>[isset($_GET['start'])?$_GET['start']:0,isset($_GET['length'])?$_GET['length']:10],
                'ORDER'=>['create_time'=>'DESC']
            ];
            if(isset($_GET['keywords'])&&$_GET['keywords']){
                $keywords = $_GET['keywords'];
                $where['title[~]']="%$keywords%";
            }
            $list = $db->select('job',$column,$where);
            $standardWords = $this->getStandardWords();
            $wordsMap = [];
            array_map(function($item)use(&$wordsMap){
                $wordsMap[$item['pid']]=$item;
            },$standardWords);
            //排序
            $orderBy = isset($_GET['order_by'])?$_GET['order_by']:'';
            switch($orderBy){
                case 'money':
                    array_multisort(array_column($list,'wages'),SORT_DESC,$list);
                    break;
                case 'hot':
                    array_multisort(array_column($list,'upper_limit'),SORT_DESC,$list);
                    break;
                case 'comprehensive':
                    array_multisort(array_column($list,'wages'),SORT_DESC,array_column($list,'upper_limit'),SORT_DESC,$list);
            }
            foreach($list as $key=>$val){
                $list[$key]['wages'] = (int) $list[$key]['wages'];
                $list[$key]['type']=$wordsMap[$val['type']]['name'];
                $list[$key]['labor_hour_type']=$wordsMap[$val['labor_hour_type']]['name'];
                $list[$key]['clear_type']=$wordsMap[$val['clear_type']]['name'];
            }
             parent::returnSuccess($list) ;
        }catch (\Exception $e){
             parent::returnFail($e->getMessage()) ;
        }
    }

    /** 规范词列表
     * @return array
     */
    public function getStandardWords()
    {
        if(self::$standardWords){
            return self::$standardWords;
        }
        $db = Base::$mysql;
        $words = $db->select('sys_dict_item',['pid','code','name'],['deleted'=>'0']);
        self::$standardWords = $words;
        return $words;
    }

    /**
     * 兼职详情
     */
    public function getinfo()
    {
        if(!isset($_GET['job_id'])&&!$_GET['job_id']){
            parent::returnFail('缺少必要参数');
        }
        $db = Base::$mysql;
        $column = ['pid','longitude','latitude','title','content','description','upper_limit',
         'address','start_time','end_time','wages','type', 'labor_hour_type','clear_type'];
        $info = $db->select('job',$column,['pid'=>$_GET['job_id']]);
        if(!$info){
            parent::returnFail('兼职信息不存在了');
        }
        $standardWords = $this->getStandardWords();
        $jobInfo = $info[0];
        $jobInfo['type'] = $standardWords[$jobInfo['type']]['name'];
        $jobInfo['labor_hour_type'] = $standardWords[$jobInfo['labor_hour_type']]['name'];
        $jobInfo['clear_type'] = $standardWords[$jobInfo['clear_type']]['name'];
        $jobInfo['wages'] = (int)  $jobInfo['wages'];
        parent::returnSuccess($jobInfo);
    }




}