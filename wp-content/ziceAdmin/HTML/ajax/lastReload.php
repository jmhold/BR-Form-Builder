<script type="text/javascript" >
	 $(document).ready(function(){	
	// Fancybox 
	$(".pop_box").fancybox({ 'showCloseButton': false, 'hideOnOverlayClick'	:	false });	
 }); 
</script>   
<?php
// connect  server 
include ('../config/config.php'); 
		$result =q("SELECT  * FROM " .$prefix_table."reload  ORDER BY id  DESC limit 0,10");
?>
                    <ol class="rectangle-list">
<?php
		while($arr=mysql_fetch_assoc($result)){
?>
                        <li><a href="ajax/view.php?id=<?php echo $arr[id]?>" class="pop_box"><?php echo $arr[title]." ".$arr[name]."   ".$arr[lastname]?><small><?php echo $arr[datetime]?></small></a></li>         
<?php } ?>
                    </ol>
