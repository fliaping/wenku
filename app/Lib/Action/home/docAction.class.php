<?php
class docAction extends docbaseAction{
	 

	public function _initialize() {
		parent::_initialize();
		global $userinfo;
		$userinfo= $this->visitor->info;
		$this->assign('uid',$userinfo['uid']);
        $this->_mod=D('doc_con');
        $this->_cate_mod=D('doc_cate');
		
		$action=$this->_get('action','trim');
        $this->assign('action',$action);
		
		
	}
	public function doccate(){
		
		$id=$this->_request('id','trim');
		if($id==''){
			$id=D('doc_cate')->where(array('pid'=>0,'status'=>1))->order('id asc')->limit(1)->getField('id');
		}
		
		
		
		
		$cate=D('doc_cate')->where(array('pid'=>0,'status'=>1))->order('ordid')->select();
		
		
		
		
		
			
			$mapcate['pid']=array('eq',$id);
			$mapcate['status']=1;
			$tcate=D('doc_cate')->where($mapcate)->order('ordid')->select();
			
			foreach($tcate as $key1 =>$value1)
			{
				$mapcate1['pid']=array('eq',$value1['id']);
			    $mapcate1['status']=1;
			    $tcate[$key1]['scate']=D('doc_cate')->where($mapcate1)->order('ordid')->select();
				
			}
			
			
			//dump($tcate);
		$this->assign('id',$id);
		$this->assign('cate',$cate);
		$this->assign('tcate',$tcate);
		$this->display();
		
		
		
	}
	
	
	public function taglist(){
		$seoconfig=$this->_config_seo(C('mtceo_seo_config.taglist'));
		$this->assign('seoconfig',$seoconfig);
		$mod=D('tag');
		$taglist=$this->_list(0, $mod, $map,'count','desc','',100);
		$this->assign('taglist',$taglist);
		$this->display();
	}
	
	
    public function doclist(){
    	global $userinfo;
    	
    	$seoconfig=$this->_config_seo(C('mtceo_seo_config.doclist'));
	    $this->assign('seoconfig',$seoconfig);	
    	
		$id=$this->_request('id','trim');
		$cname=$this->_request('c','trim');
		if($id!=''){
		$idarr=getcateallid($id);
		
		if(is_array($idarr)){
		foreach ($idarr as $key =>$value){
			
			if($key==0){
				$catearr=$value;
				
			}else{
			
			
			$catearr.=','.$value;
		}
		}	
			
			
			
			
		}else{
			$catearr=$idarr;
			
		}
		$map['id']=$id;
		$pid=D('doc_cate')->where($map)->getField('pid');//id的上级id
		$spid=D('doc_cate')->where($map)->getField('spid');
		if($pid==0){
		$map1['pid']=$id;
		$map1['status']=1;
		$tcate=D('doc_cate')->where($map1)->select();//所有二级分类
		}
		
		if($spid!=''){
			
		$count=substr_count($spid, '|');
		//dump($count);
		if($count==2){//三级分类 得到二级分类id，然后该二级分类下面所有三级分类
		   
			
			$tcateid=D('doc_cate')->where(array('id'=>$pid,'status'=>1))->getField('id');
			
			
			$thcate=D('doc_cate')->where(array('pid'=>$tcateid,'status'=>1))->select();
			
	}else{//二级分类，本身的二级分类id，然后得到下面的三级分类
		
		$tcateid=$id;
		$thcate=D('doc_cate')->where(array('pid'=>$id,'status'=>1))->select();
	}
			
			
			
		}
		
		
		}
		$this->assign('pid',$pid);
		$this->assign('tcate',$tcate);
		$this->assign('tcateid',$tcateid);
		$this->assign('thcate',$thcate);
		
		
		//dump($catearr);
		if($id==''&&$cname==''){
			
			$navname='全部文档';
			
			
		}
		
		
		
		switch ($cname){
			
			case 'newdoc':
				$navname='最新文档';
				$order='add_time';
				
				break;
			case 'hotdoc':
				$navname='热门文档';
				$order='hits';
				break;
			default:
				break;
			
			
			
			
		}
		
		$this->assign('catearr',$catearr);
		$this->assign('cname',$cname);
    	$this->assign('navname',$navname);
    	$this->assign('id',$id);
		$this->display();
	}
	 public function docconview(){
	 	
	 	global $userinfo;
     	$uid=$userinfo['uid'];
	 	$id=$this->_request('id','trim');
	 	$map['id']=$id;
     	$info=D('doc_con')->where($map)->find();//得到文档的信息存放在info中
     	$download=downorbuy($info['score'],$uid,$info['uid'],$id);
        $this->assign('download',$download);   //是显示购买还是下载1为下载2为购买
	    //////////////////////////////////////////////////////////////////////////////////////
        $web_model=$info['model'];
    	if($web_model==1)
    	{
    	   $ipstr=geturlipstr($info['ext'],$info['filename']);
        
    	}else{
    		
    		$ipstr=getipstr($info['ext'],$info['filename']);
    		
    	}
    	if($download==2){//如果需要购买
	 	    $expstr="<div class=\"stl_01 mod reader-page complex reader-page-\">";
    	    $ipstrsarr=explode($expstr,$ipstr);
    	    $ipstr=$ipstrsarr[0].$expstr.$ipstrsarr[1];
    	}
    	$this->assign('ipstr',$ipstr);
	 	$this->assign('info',$info);
	 	$this->display();
	 }
    public function doccon(){
    	
    	$mapad['tpl']='indexfocus';
    	$adinfo=D('adboard')->where($mapad)->find();
    	$adinfo['height']=$adinfo['height']+40;
    	$this->assign('adinfo',$adinfo);//广告信息
    	$id=$this->_request('id','trim');
        D('doc_con')->where(array('id'=>$id))->setInc('hits',1);//点击增加1
     	
     	global $userinfo;
     	$uid=$userinfo['uid'];
     	$map['id']=$id;
     	$info=D('doc_con')->where($map)->find();//获得文档信息
        $download=downorbuy($info['score'],$uid,$info['uid'],$id);
        
        $this->assign('download',$download);   //是显示购买还是下载1为下载2为购买
        if(!$info){
        $this->error(L('operation_failure'));
        }
        if($info['status']<2){
        $this->error('该文档已关闭或者未审核');
        }
        $similar=similardoc($id,10);
        $samecatedoc=samecatedoc($id);
        $mapraty['itemid']=$id;
        $mapraty['typeid']=1;
        $ratydata= D('raty')->where($mapraty)->find();
        $raty=getraty($id,1);//计算平均分
        $commentcount= D('comment')->where($mapraty)->count();//评论数量
        $mapratyuser['ratyid']=$ratydata['id'];
        $ratyuser= D('raty_user')->where($mapratyuser)->select();//所有用户的评分详细信息
        $info['tags']=explode(',',$info['tags']);
        foreach($info['tags'] as $key =>$value){
        	
        	$maptag['name']=$value;
        	$tagarr[]=D('tag')->where($maptag)->find();
        	
        }//获得文档的相关信息
        $web_model=$info['model'];
    	if($web_model==1)
    	{
    		
    		$ipstr=geturlipstr($info['ext'],$info['filename']);
    	   
    	}else{
    		
    		$ipstr=getipstr($info['ext'],$info['filename']);
    		
    	}
    	
    	
        $realstrcount=substr_count($ipstr,'stl_01');
        //求得文档总页数，如果没有生成预览，则显示未1页
        if($download==2){//如果需要购买，未生成预览和已经生成预览的都是取第一页
    		$expstr="<div class=\"stl_01 mod reader-page complex reader-page-\">";
    	    $ipstrsarr=explode($expstr,$ipstr);
    	    $ipstr=$ipstrsarr[0].$expstr.$ipstrsarr[1];
    	    $strcount=1;
    	}else{
    		
    		$strcount=$realstrcount;
    		//如果可以随意下载的，则显示页数为所有页数，不限制
    		
    	}
    	
    	
    	
    	
        if($info['intro']==''){
        	

        
         $extarr=array('xls','xlsx','ppt','pptx','dps','et');
	        if(in_array($info['ext'], $extarr)){
	         

	                if($web_model==1)
    	{
$pngtxt=file_get_contents('http://www.mtceo.net/data/upload/docswf/data/'.$info['filename'].'/'.$info['filename'].'.txt');	
    	}else{
 $pngtxt=file_get_contents(HOST.__ROOT__ . '/' . C('mtceo_attach_path').'docswf/data/'.$info['filename'].'/'.$info['filename'].'.txt');
    	}
	          
	          
	          $intro=strip_tags($pngtxt);
	        }else{
	 	      $intro=strip_tags($ipstr);
	        }
	    
    	$intro=str_replace("&nbsp;", '', $intro);
    	$intro=str_replace(" ", '', $intro);
    	$intro=str_replace("\t", '', $intro);
    	$intro=str_replace("\n", '', $intro);
    	$intro=str_replace("\r", '', $intro);
    	$intro=substr($intro,0,300);
    	
    	if($intro!='该文档尚未生成预览'&&$intro!='该网站授权时间已到。如需租用平台，请联系176314141'&&$intro!='该网站未授权'){
    		$info['intro']=$intro;
    	    D('doc_con')->where(array('id'=>$id))->save($info);
    	}
    	
    	
    	
    	
    	
    	
    	
    	}else{
    		$intro=$info['intro'];
    	}
    	
    	
    	
    	
    	$this->assign('intro',$intro);
    	$this->assign('strcount',$strcount);
    	$this->assign('realstrcount',$realstrcount);
    	 $this->assign('ipstr',$ipstr);
    	 
    	 
    	 
    	 
    	 
        $seoconfig=$this->_config_seo(C('mtceo_seo_config.doccon'));
        $seoconfig['title']=$info['title'].'|'.$seoconfig['title'];
        $seoconfig['description']=$intro.'|'.$seoconfig['description'];
        
       
        
        
        
        
		$this->assign('seoconfig',$seoconfig);
        $this->assign('info',$info);
        $this->assign('raty',$raty);
        $this->assign('tagarr',$tagarr);
        $this->assign('commentcount',$commentcount);
        $this->assign('flashurl',$flashurl);
        $this->assign('ratydata',$ratydata);
        $this->assign('ratyuser',$ratyuser);
        $this->assign('similar',$similar);
        $this->assign('uid',$userinfo['uid']);
     	//谁下载过
    	$downnum=D('itemlog')->where(array('itemid'=>$id,'type'=> 1,'typeid'=> 1))->count();
    	$tuijiannum=D('itemlog')->where(array('itemid'=>$id,'type'=> 3,'typeid'=> 1))->count();
    	$xhnum=D('itemlog')->where(array('itemid'=>$id,'type'=> 2,'typeid'=> 1))->count();
    	if(D('itemlog')->where(array('itemid'=>$id,'type'=> 2,'typeid'=> 1,'uid'=>$uid))->find()){
    		
    		$hasxh=1;
    	}
        if(D('itemlog')->where(array('itemid'=>$id,'type'=> 3,'typeid'=> 1,'uid'=>$uid))->find()){
    		
    		$hastj=1;
    	}
    	$this->assign('hasxh',$hasxh);
    	$this->assign('hastj',$hastj);
    	$downuser=$this->downdoc($id, 5);//谁下载过
    	$this->assign('downnum',$downnum);
     	$this->assign('tuijiannum',$tuijiannum);
     	$this->assign('xhnum',$xhnum);
     	$this->assign('downuser',$downuser);
     	
     	
     	
     	
     	
        $emot=C('DEFAULT_EMOT');
     	$emotdata=R('home/common/emot',array($emot));
		$this->assign('emot',$emot);
     	$this->assign('emotwidth',$emotdata['width']);
     	$this->assign('emotnum',$emotdata['num']);
     	$mapraty['itemid']=$id;
        $mapraty['typeid']=1;
     	$commentcount= D('comment')->where($mapraty)->count();//评论数量
     	$this->assign('id',$id);
		$this->assign('commentcount',$commentcount);
        $this->display();
	}
	public function docconcom(){
		//dump($_COOKIE);
		global $userinfo;
     	$uid=$userinfo['uid'];
		$id=$this->_request('id','trim');
		$commentdata=R('home/common/comment',array($id,1));
		$mapraty['itemid']=$id;
        $mapraty['typeid']=1;
		 $commentcount= D('comment')->where($mapraty)->count();//评论数量
		  $info=D('doc_con')->where(array('id'=>$id))->find();
		 //获得表情的相关配置、减少js的读取量
     	$emot=C('DEFAULT_EMOT');
     	$emotdata=R('home/common/emot',array($emot));
		 $this->assign('emot',$emot);
     	$this->assign('emotwidth',$emotdata['width']);
     	$this->assign('emotnum',$emotdata['num']);
     	 $seoconfig=$this->_config_seo(C('mtceo_seo_config.doccon'));
        $seoconfig['title']=$info['title'].'-'.$seoconfig['title'];
		$this->assign('seoconfig',$seoconfig);
     	$downnum=D('itemlog')->where(array('itemid'=>$id,'type'=> 1,'typeid'=> 1))->count();
    	$tuijiannum=D('itemlog')->where(array('itemid'=>$id,'type'=> 3,'typeid'=> 1))->count();
    	$xhnum=D('itemlog')->where(array('itemid'=>$id,'type'=> 2,'typeid'=> 1))->count();
    	
     	$this->assign('downnum',$downnum);
     	$this->assign('tuijiannum',$tuijiannum);
     	$this->assign('xhnum',$xhnum);
		 $this->assign('info',$info);
		 $this->assign('id',$id);
		 $this->assign('uid',$uid);
		 $this->assign('commentcount',$commentcount);
		$this->assign('commentdata',$commentdata);
		$this->display();
	}
	
	
	public function comment(){
		
		global $userinfo;
     	$uid=$userinfo['uid'];
		$id=$this->_request('id','trim');
		$emot=C('DEFAULT_EMOT');
     	$emotdata=R('home/common/emot',array($emot));
		 $this->assign('emot',$emot);
     	$this->assign('emotwidth',$emotdata['width']);
     	$this->assign('emotnum',$emotdata['num']);
     	$mapraty['itemid']=$id;
        $mapraty['typeid']=1;
     	 $commentcount= D('comment')->where($mapraty)->count();//评论数量
     	 $this->assign('id',$id);
		 $this->assign('uid',$uid);
		 
		 
		 
     	$this->assign('commentcount',$commentcount);
	if (IS_AJAX) {
				 
				$response = $this->fetch();
				$this->ajaxReturn(1, '', $response);
			} else {
				 
				$this->display();
	}
		
	}
//谁下载过
     public function downdoc($id,$num){
     	
     	$map['itemid']=$id;
     	$map['typeid']=1;
     	$map['type']=1;
     	//$data=D('itemlog')->where($map)->select();
     	
     	$data=D('itemlog')->where($map)->limit($num)->select();
     	
     	return $data;
     }	
	
	
	

   
     
   

     
     
 public function setscore(){ //评分  
       $score = $this->_request('score','intval');
       $uid =$this->_request('uid','intval');
       $id =$this->_request('id','intval');
       if($uid == '')
       {
        	$this->ajaxReturn(0, '你尚未登录，无法评分');
       }
            $map1['ratyid']=$id;
       		$map1['uid']=$uid;
       		$data=D('raty_user')->where($map1)->getField('id');
       		if($data>0){
       			$this->ajaxReturn(0, '你已经对该文档评过分了');
       			      			
       		}
       
       if (isset ($score)) 
       {
       	$map['itemid']=$id;
       	$map['typeid']=1;
       	$newratyid=D('raty')->where($map)->getField('id');
       	
       	if($newratyid>0){
       		D('raty')->where($map)->setInc('voter',1);
       		D('raty')->where($map)->setInc('total',$score);
       		
       		
       		
       		
       		
       	}else{
       		
       		$data['itemid']=$id;
       		$data['typeid']=1;
       		$data['voter']=1;
       		$data['total']=$score;
       	if(false===$newratyid=D('raty')->add($data)){
       		
       			$this->ajaxReturn(0, '评分失败');
       				
       	}
       		
       	        
       		
       	}
       $data1['ratyid']=$newratyid;
       $data1['uid']=$uid;
       $data1['score']=$score;
      if(false!==D('raty_user')->add($data1)){
      	
      	
        $ratydata= D('raty')->where($map)->find();
        $raty=getraty($ratydata);
      	$mapratyuser['ratyid']=$ratydata['id'];
      	$ratycount=D('raty_user')->where($mapratyuser)->count();
      	$raty['count']=$ratycount;
      	
       	$this->ajaxReturn(1, $raty);
       				
       			}else{
       				$this->ajaxReturn(0, '评分失败');
       				
       }
       	 
       }else{
       	
       	$this->ajaxReturn(0, '评分失败');
       	
       }
       
      
  }
  
  public function download(){
  	
  	global $userinfo;
  	$uid=$userinfo['uid'];
  	$score=getuserscore($uid) ;
  	$id = $this->_get('id', 'trim');
  	$down = $this->_get('down', 'trim');
  	$fileurl=D('doc_con')->where(array('id'=>$id))->find();
    if($fileurl == ''){
  	 	$this->error('此文档未提供下载');
  	 	
  	 }
    if($uid < 0){
  	 	
  	 	$url=U('user/login');
  	 	$this->error('请先登陆或者注册',$url);
  	 	
  	 }
     if($down=='yes'){
     	
     	
     	
     }	else{
     	
     if($score < $fileurl['score']){
     	
     	
     	$this->error('积分不足以支持此次下载',$url);
     }
     	
     	
     	
     	
     $data['typeid']=1;
  	 $data['type']=1;
  	 $data['itemid']=$id;
  	 $data['uid']=$uid;
  	 $data['add_time']=time();
     D('itemlog')->add($data);	
     
  	 opuserscore($uid, 0, 'score', $fileurl['score']);
  	 opuserscore($uid, 0, 'down', $fileurl['score']);
  	 
  	
  	 
  	 opuserscore($fileurl['uid'], 1, 'score', floor($fileurl['score']*C('mtceo_score_rule.docscore')/100)+C('mtceo_score_rule.docdown'));
  	 opuserscore($fileurl['uid'], 1, 'doc', floor($fileurl['score']*C('mtceo_score_rule.docscore')/100)+C('mtceo_score_rule.docdown'));
  	 
  	 
  	 
  	 
     }
  	 
  	
  	
  	 $down = new Http();
  	 $file=$fileurl['fileurl'];

  	  	 $dir=dirname($_SERVER['SCRIPT_FILENAME']).'/'.C('mtceo_attach_path').'doc_store/';
  	
  	 
  	  	
  	 $down->download($dir.$file,$fileurl['oldname'].'.'.$fileurl['ext']);

  	
  	 
  	 
  	
  	
  	
  }
  public function showdownload(){
  	$id = $this->_request('id', 'trim');
  	global $userinfo;
  	$uid=$userinfo['uid'];
  	$userscore=getuserscore($uid);
    $info=D('doc_con')->where(array('id'=>$id))->find();
    
    if($uid==''){
    	$url= U('user/login');
  		$scoreinfo='您还未登陆，点我登陆！';
    	
    }else{
    	  	if($uid == $info['uid']||$uid ==1){
  		$url= U('doc/download',array('id'=>$id,'down'=>'yes'));
  		$scoreinfo='您可以无限制下载此文档！';
  		
   	}else{
  	
  	
  	
  	
  	
  	$map['uid']=$uid;
  	$map['itemid']=$info['id'];
  	$map['typeid']=1;
  	$map['type']=1;//表示下载
  	if(D('itemlog')->where($map)->getField('id')>0){
  		$url= U('doc/download',array('id'=>$id,'down'=>'yes'));
  		$scoreinfo='您已经下载过此文档，再次下载免费！';
  		
  		
  	}else{
  		
  		$userscore=$userscore-$info['score'];
  	if($userscore < 0){
  		
  		
  		$url= 'javascript:void(0);';
  		$scoreinfo='您的积分不足以进行此次下载！';
  		
  	}else{
  		
  	    $url= U('doc/download','id='.$id );
  		$scoreinfo='恭喜！您的积分可以进行此次下载！';
  	}
  	
  	
  	}
  	
  	
   	
   	}
  	
    	
    }
    
    $fileurl=D('doc_con')->where(array('id'=>$id))->find();
     $file=$fileurl['fileurl'];
  if($fileurl['model']==1){

  	 if(!file_exists('./' . C('mtceo_attach_path')."docswf/data/".$file)){
  	 	$http=new Http();
	    $http->curlDownload('http://www.mtceo.net/data/upload/doc_con/'.$file,'./' . C('mtceo_attach_path').'doc_con/'.$file);
	
  	 }
			
  	 
  	 
}
    

  	
  	$this->assign('url',$url);
  	$this->assign('scoreinfo',$scoreinfo);
  	$this->assign('userscore',$userscore);
  	$this->assign('info',$info);
  	$response = $this->fetch();
	$this->ajaxReturn(1, '', $response);
  }
  
  
     public function doc_share(){
     $seoconfig=$this->_config_seo(C('mtceo_seo_config.doclist'));
	 $this->assign('seoconfig',$seoconfig);	
     
     $mod = D('doc_con');
		 global $userinfo;
         $uid=$userinfo['uid']; 
         
        
         if($uid<1||$uid == ''){

         	
         	$this->redirect('user/login');
         }
         
		if (IS_POST) {
			
			
			
			$type=$this->_request('type','trim');
			
			if (false === $data = $mod->create()) {
				IS_AJAX && $this->ajaxReturn(0, $mod->getError());
				$this->error($mod->getError());
			}
			
			if($data['cateid']<1){
				
				$this->error('请选择分类');
			}
			
			$data['score']=intval($data['score']);
			
			
			if($data['tags']==''){$data['tags']=getcatename('doc',$data['cateid']);}else{
			$data['tags']=$data['tags'].','.getcatename('doc',$data['cateid']);}
			$tagarr=explode(',', $data['tags']);
			$tagarr=array_unique($tagarr);
			
			$data['tags']=implode(',', $tagarr);
			
			
			$fileinfo=explode('.',$data['fileurl']);
			$data['filename']=$fileinfo[0];
			
			if($type=='add'){
			
			
			$data['model']=C('mtceo_web_model');
			
			
			
			
			
			
			$data['status']=C('mtceo_web_switch.doc_con');
			$data['uid']=$uid;
			
			if(D('doc_con')->title_exists($data['title'])){
				
				
				$this->error('该标题已经存在');
			}
			
			//dump($data);
			
			
			if( $topicid=$mod->add($data) ){
				
				if(C('mtceo_web_model')==1){
				$http=new Http();
				$http->curlDownload('http://www.mtceo.net/data/upload/doc_con/'.$data['fileurl'],'./' . C('mtceo_attach_path').'doc_con/'.$data['fileurl']);
				}
				
				opuserscore($uid,1,'score',C('mtceo_score_rule.docup'));
				opuserscore($uid,1,'up',C('mtceo_score_rule.docup'));
				
				$mod->singletags($data['tags']);
				IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
				
				
				
				
				//$url=U('doc/doccon',array('id'=>$topicid));
				$url=U('ucenter/mydoclist');
				$this->success(L('operation_success'),$url);
				//$this->success(L('operation_success'));
			} else {
				IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
				$this->error(L('operation_failure'));
			}
			
			}else{
				
				
				
				
				
			if(D('doc_con')->title_exists($data['title'],$data['id'])){
				
				
				$this->error('该标题已经存在');
			}

			$oldfileurl=D('doc_con')->where(array('id'=>$data['id']))->getField('fileurl');
			if($data['fileurl']!=$oldfileurl){
			
			$data['model']=C('mtceo_web_model');
			
			}
			
				
			if( $mod->save($data) ){
				
				if($data['fileurl']!=$oldfileurl&&C('mtceo_web_model')==1){
				
				$http=new Http();
				$http->curlDownload('http://www.mtceo.net/data/upload/doc_con/'.$data['fileurl'],'./' . C('mtceo_attach_path').'doc_con/'.$data['fileurl']);
				
				
				}
				
				$mod->singletags($data['tags']);
				IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
				//$url=U('doc/doccon',array('id'=>$data['id']));
				$url=U('ucenter/mydoclist');
				$this->success(L('operation_success'),$url);
				
				
				//$this->success(L('operation_success'));
			} else {
				IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
				$this->error(L('operation_failure'));
			}
				
				
				
				
				
			}
			 
			 
			 
			
		} else {
			
			
		if(C('mtceo_web_model')==1){
			$dataattach=file_get_contents('http://www.mtceo.net/common-getattachinfo'); 
			$data=json_decode( $dataattach,true);
			
		 $data1['ext']=C('mtceo_doctype');
         $data1['size']=C('mtceo_attr_allow_size');
			
			
         if(strlen($data1['ext'])<=strlen($data['ext'])){
         	
         	$data['ext']=$data1['ext'];
         }
         
		if(intval($data1['size'])<=$data['size']){
         	
         	$data['size']=$data1['size'];
         }
         
		}else{
			$data['ext']=C('mtceo_doctype');
         $data['size']=C('mtceo_attr_allow_size');
			
		}
			    $rooturl=	C('mtceo_site_url');
			    
			    
			   
			 
			    if(C('mtceo_web_model')==1){
			    	 $uploadimgurl='http://www.mtceo.net/index.php?m=common&a=ajax_upload_imgs';
			    }else{
			    	 $uploadimgurl=U('doc/ajax_upload_imgs');
			    }
		$this->assign('attachext',$data['ext']);
		$this->assign('attachsize',$data['size']);
			    
			    
			    $this->assign('uploadimgurl',$uploadimgurl);
			    $this->assign('rooturl',$rooturl);
			    
				$this->display();
			
		}
     	
     	
     	
     	
    }
public function editdoc(){
		
		$seoconfig=$this->_config_seo(C('mtceo_seo_config.doclist'));
	    $this->assign('seoconfig',$seoconfig);	
		$id=$this->_request('id','trim');
		$map['id']=$id;
		$info=D('doc_con')->where($map)->find();
	   	$data2=D('doc_cate')->where(array('id'=>$info['cateid']))->find();
		$info['spid'] = $data2['spid'] ? $data2['spid'].$data2['id'] : $data2['id'];
        
		
		
			 if(C('mtceo_web_model')==1){
			$dataattach=file_get_contents('http://www.mtceo.net/common-getattachinfo'); 
			
			$data=json_decode( $dataattach,true);
			
		 $data1['ext']=C('mtceo_doctype');
         $data1['size']=C('mtceo_attr_allow_size');
			
			
         if(strlen($data1['ext'])<=strlen($data['ext'])){
         	
         	$data['ext']=$data1['ext'];
         }
         
		if(intval($data1['size'])<=$data['size']){
         	
         	$data['size']=$data1['size'];
         }
         
		}else{
			$data['ext']=C('mtceo_doctype');
         $data['size']=C('mtceo_attr_allow_size');
			
		}
			    $rooturl=	C('mtceo_site_url');
			   
			    
			    if(C('mtceo_web_model')==1){
			    	 $uploadimgurl='http://www.mtceo.net/index.php?m=common&a=ajax_upload_imgs';
			    }else{
			    	 $uploadimgurl=U('doc/ajax_upload_imgs');
			    }
		$this->assign('attachext',$data['ext']);
		$this->assign('attachsize',$data['size']);
			    
			    
			    $this->assign('uploadimgurl',$uploadimgurl);
			    $this->assign('rooturl',$rooturl);
	
	
		$this->assign('info',$info);
		$this->display();
		
	}
 public function ajax_upload_imgs() {
        //上传图片
          global $userinfo;
          $uid=$userinfo['uid'];
          $userdir=md5($uid);
          
          $paths =C('mtceo_attach_path');
          
          
          
        if (!empty($_FILES['Filedata'])) {
        	$result = $this->_upload($_FILES['Filedata'], 'doc_con/');
        	 if ($result['error']) {
            	$data['status']=0;
            	$data['info']=$result['info'];
                //$this->error($result['info']);
            }else {
            	$data['info']['size'] =  $result['info'][0]['size'];
                $data['info']['name'] =  $result['info'][0]['savename'];
                $oldname=explode('.', $result['info'][0]['name']);
                 $data['info']['oldname'] =  $oldname[0];
                 $data['info']['ext'] =  $result['info'][0]['extension'];
                $data['info']['hash'] =  $result['info'][0]['hash'];
                $data['status']=1;
                if( D('doc_con')->hash($result['info'][0]['hash'])){
               	
               
               @unlink($paths.'doc_con/'.$result['info'][0]['savename']);
               $data['info']='已经有人先一步上传了该文件，您可以搜索该文档';
               $data['status']=0;
               }
                
                
               // $this->ajaxReturn(1, L('operation_success'), $data['img']);
            }


        } else {
        	$data['status']=0;
        	$data['info']='无文件上传';
            //$this->ajaxReturn(0, L('illegal_parameters'));
        }
       
        	 echo json_encode($data);
        
       
    }
/**
     * ajax获取标签
     */
    public function ajax_gettags() {
        $title = $this->_get('title', 'trim');
        if ($title) {
            $tags = D('tag')->get_tags_by_title($title);
            $tags = implode(',', $tags);
            $this->ajaxReturn(1, L('operation_success'), $tags);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
}