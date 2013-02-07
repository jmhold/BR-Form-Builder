<script type="text/javascript" >
$(function() {		  	
	// move images  to  news album
	$('.album').droppable({
		hoverClass	: 'over',
		activeClass	: 'dragging',
		tolerance		:  'pointer',
		drop				:   function(event,ui){
								   if( $( this).hasClass('selected') ) return false;
											   loading('Moving',0);
											   var album = $(this).attr('id');		
											   var datavalue='newalbumid='+album+'&lastalbumid='+ ui.draggable.imgdata(2)+'&picid='+ ui.draggable.imgdata(0); 
											   
											  $.ajax({ // Call Aajx
												  url				: "gallery/move.php",
												  data			: datavalue,
												  cache		: false,
												  type			: "POST",
												  dataType	: 'json',
												  success	: function(data){	
																		if(data.check==0){ 
																			  alertMessage('error','Error ;)) Try  Agian');
																			  return false;
																	   }
																	  ui.helper.fadeOut(function(){ui.helper.remove();});				
																	  $('#albumsLoad').fadeOut().load('gallery/loadAlbum.php').fadeIn(function(){ 
																				$('#albumsLoad #albumsList').find("#"+ui.draggable.imgdata(2)).addClass('selected');
																				unloading(); 																							
																	  });		
												  					}
											  }); // End Call Aajx
								  
								  ui.helper.fadeOut(400);
								  setTimeout("unloading()",1500); 		
							  }
	});
	// mouseenter Over album with  CSS3
	$(".preview").delegate('img', 'mouseenter', function() {
		  if ($(this).hasClass('stackphotos')) {
		  var $parent = $(this).parent();
				$parent.find('img#photo1').addClass('rotate1');
				$parent.find('img#photo2').addClass('rotate2');
				$parent.find('img#photo3').addClass('rotate3');
		  }
	  }).delegate('img', 'mouseleave', function() {
				$('img#photo1').removeClass('rotate1');
				$('img#photo2').removeClass('rotate2');
				$('img#photo3').removeClass('rotate3');
	});
	// jScrollPane  Overflow
	$('#albumsList').jScrollPane({ autoReinitialise: true });
	$('.album.load').live('click',function(e){
		  $('.album').removeClass('selected');
		  var albumid=$(this).attr('id');
		  $(this).addClass('selected');
		  loadalbum(albumid);
	});
	//Load Album
	function loadalbum(albumid){
			loading('Loading');
			$('.screen-msg').hide();
			$('#imageLoad').load("gallery/loadpics.php?albumid="+albumid,function(){
			  imgRow();
						  $("#uploadAlbum").attr('href','modalupload.php?albumid='+albumid); 	
						  $("#uploadDisableBut").hide();
						  $('#uploadAlbum').removeClass('disable secure ').addClass('special add  ');

			  unloading();												   
			});
		}
				  
 }); 
</script>   
 <div id="albumsList" >
 <?php
		include("../config/config.php");
		$albumsResult = q("SELECT  al.*, pic.filename  AS thumbnail FROM 01_albums  AS al LEFT JOIN 01_pics  AS pic ON (al.thumb=pic.id) ORDER BY dt  DESC ");
		while($arr=mysql_fetch_assoc($albumsResult)){
			  if($arr['thumbnail']){
				  $arr['thumbnail']=$config['imageDir'].'/s/'.$arr['thumbnail'];
			  }else{
				  $arr['thumbnail']='images/icon/empty_album_icon_small.jpg';
			  }
			?>
                 <div class="album load" id="<?=$arr[id]?>">
                      <div class="preview">
					  <?php
                         $thumbPreview = q(" SELECT * FROM ".$prefix_table."pics  WHERE albumid='".$arr[id]."' and id<>'".$arr[thumb]."' ORDER BY key_position  DESC limit 0,2");
                         $num=mysql_num_rows( $thumbPreview);
                         if($num){
                             if($num==2){
                                 $i=1;
                                  while($thumb_arr=mysql_fetch_assoc($thumbPreview)){
                                 $thumb=$config['imageDir'].'/s/'.$thumb_arr['filename'];
                                 ?>
                                 <img width="130" id="photo<?php echo $i?>" class="stackphotos" src="<?php echo $thumb?>" alt="Thumbnail" />
                                 <?php $i++; } }elseif($num==1){
                                 $thumb_arr=mysql_fetch_assoc($thumbPreview);
                                 $thumb=$config['imageDir'].'/s/'.$thumb_arr['filename'];
                                 ?>
                                    <img width="130" id="p1" class="stackphotos" src="<?php echo $thumb?>" alt="Thumbnail" />
                             <?php }?>
                                   <img width="130" id="photo3" class="stackphotos" src="<?php echo $arr['thumbnail']?>" alt="Thumbnail" />
                            <?php }else{?>
                                   <img width="130" id="p1" class="stackphotos" src="<?php echo $arr['thumbnail']?>" alt="Thumbnail" />
                            <?php }?>
                      <div style="clear:both"></div>
                      </div>

                  <div class="title"><?php echo htmlspecialchars($arr['name'])?></div>
                            <div class="stats">Images: <span class="picCount"><?php echo (int)$arr['cnt']?></span></div>
                            <div class="clear"></div>
                   </div>
<?php }?>
</div><!-- End albumsList -->