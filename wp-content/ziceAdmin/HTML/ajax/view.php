<?php
// connect  server 
include ('../config/config.php'); 
		$result =q("SELECT  * FROM " .$prefix_table."reload  where id='".$_REQUEST[id]."'");
		$arr=mysql_fetch_assoc($result);
?>
<script type="text/javascript" >
	 $(function(){	
			$('.gender-ck').customInput();
	 }); 
</script>                 

<div class="modal_dialog" >
  <div class="header"><span>View_Profile</span><div class="close_me"><a  id="close_windows"  class="butAcc"  ><img src="images/icon/closeme.png"  alt="closeme"/> </a></div> </div>
  
  <div class="clear"></div>
  <div class="content">
    <form >
          <div class="section">
          		<label> Name </label>   
                <div>
                <input type="text" name="name" id="name" class="large"   readonly  value="<?php echo $arr[title]." ".$arr[name];?>"/>
                <span class="f_help">Don't Edited</span>
                </div>
          </div>
          <div class="section">
          		<label> E-mail </label>   
                <div>
                <input type="text" name="name" id="name" class="large"  readonly  value="<?php echo $arr[email]?>"/>
                <span class="f_help">Don't Edited</span>
                </div>
          </div>
          <div class="section last">
              		<label> Gender </label>   
                    <div>
                        <div>
                            <p><?php if($arr[gender]=="1"){?><span class="ico color user"></span> <strong>Male</strong><?php }else{ ?><span class="ico color user_woman"></span> <strong>Female</strong><?php }?></p>
                        </div>
                    </div>
               </div>
    </form>
  </div>
</div>