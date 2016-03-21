<?php
// 
class indexAction extends frontendAction {
    public function _initialize() {
		parent::_initialize();
		$seoconfig=$this->_config_seo(C('mtceo_seo_config.index'));
        $this->assign('seoconfig',$seoconfig);
        
	}
	public function index(){
		
		
		$cate=D('doc_cate')->where(array('pid'=>0,'status'=>1))->order('ordid')->select();
		foreach ($cate as $key =>$value){
			
			$mapcate['pid']=array('eq',$value['id']);
			$mapcate['status']=1;
			$cate[$key]['tcate']=D('doc_cate')->where($mapcate)->order('ordid')->select();
			
			foreach($cate[$key]['tcate'] as $key1 =>$value1)
			{
				$mapcate1['pid']=array('eq',$value1['id']);
			    $mapcate1['status']=1;
			$cate[$key]['tcate'][$key1]['scate']=D('doc_cate')->where($mapcate1)->order('ordid')->select();
				
			}
			
			
			
		}
		$this->assign('cate',$cate);//所有分类，首页只取前八个大类
		
		
		$totaldocnum=D('doc_con')->count('id');
		$this->assign('totaldocnum',$totaldocnum);
		
    	//友情链接
    	
    	$maplink['cate_id']=2;
    	$maplink['status']=1;
    	$flink['text']=D('flink')->where($maplink)->order('ordid')->select();
    	
    	$maplinkimg['cate_id']=1;
    	$maplinkimg['status']=1;
    	$flink['img']=D('flink')->where($maplinkimg)->order('ordid')->select();
		$this->assign('flink',$flink);
    	$this->display();

	}
	
}