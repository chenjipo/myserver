<?php
namespace App\admin\common;

class Tree{
    //最简单的无限极分类
	static public function unlimitedForLevel($cate,$html='━━',$pid=0,$level=-1){
		$arr = array();
		foreach($cate as $v){
			if($v['parent_id']==$pid){
				$v['level']=$level+1;
				$v['html']=str_repeat($html,$level+1);
                if($pid != 0){
                    $v['html'] = '┗'.$v['html'];
                }
				$arr[]=$v;
				$arr = array_merge($arr,self::unlimitedForLevel($cate,$html,$v['id'],$level+1));
			}
		}
		return $arr;
	}

	//把子类压到父类下
	static public function unLimitedForLayer($cate,$pid=0){
		$arr = array();
		foreach($cate as $v){
			if($v['parent_id'] == $pid){
				$v['children'] = self::unLimitedForLayer($cate,$v['id']);
				if(empty($v['children'])) unset($v['children']);
				$arr[] = $v;
			}
		}
		return $arr;
	}

	//传入一个id找所有父级（找家谱，面包屑）
	static public function getParents($cate,$id){
		$arr = array();
		foreach($cate as $v){
			if($v['id']==$id){
				$arr[] = $v;
				$arr = array_merge(self::getParents($cate, $v['pid']),$arr);
			}
		}
		return $arr;
	}

	//传入一个父级id，找出所有子集(和第一个方法一样)
	static public function getClildren($cate,$pid = 0){
		$arr = array();
		foreach($cate as $v){
			if($v['parent_id']==$pid){
				$arr[] = $v;
				$arr = array_merge($arr,self::getClildren($cate, $v['id']));
			}
		}
		return $arr;
	}

	static public function getIdsFromTree($tree)
	{
		$arr = [];
		foreach($tree as $v){
			$arr[] = $v['id'];
			if(!empty($v['children'])){
				$arr = array_merge($arr,self::getIdsFromTree($v['children']));
			}
		}

		return $arr;
	}
}
