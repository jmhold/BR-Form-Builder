<script>
$(document).ready(function(){	
			// sendform-lightbox  click  
			$('.sendform-lightbox').click(function(){
						// search-form id   
						var form_id=$(this).parents('form').attr('id');
						// submit form
						$("#"+form_id).submit();
			});
			
			// validationEngine  select  
			var prefix = "selectBox_";
		   $('#demosubmit-lightbox').validationEngine({
					prettySelect : true,usePrefix: prefix,
					ajaxFormValidation: true,
					onBeforeAjaxFormValidation: Add_database_light
			});		
			$("select").selectBox();
			$('select').each(function(){ 
					$(this).next('a.selectBox')
					.attr("id", prefix + this.id )
					.removeClass("validate[required]");		
			  })
});	

	function Add_database_light(form, options){
		 loading('Loading',0);
		 var data=form.serialize();		
		$.ajax({
			url: "ajax/adddatabase.php",
			data: data,
			success: function(data){	
				  if(data.check==0){   // uncomplete return 0
				  		// loading remove
					  $('#preloader').fadeOut(400,function(){ $(this).remove(); });		
					  // show error messages
					  showError('Error : Sorry you can submit agian');
					   return false;
				  }
				  if(data.check==1){ // complete return 1
				  	  // show error messages
					   showSuccess('Success',5000); 
					   // reload data
					 $(".reloaddata").fadeOut(400).load('ajax/lastReload.php').fadeIn(400);
					 $("#tab1 .load_page").fadeOut(400).load('ajax/tableReload.php').fadeIn(400,function(){
						 // fancybox close
						$.fancybox.close();
					   setTimeout('unloading()',500); 
					  });	
				  }
			},
			cache: false,type: "POST",dataType: 'json'
		});				
	} 
</script>
<div class="modal_dialog"  style="min-height:50px">
  <div class="header"><span>ADD_DIALOG</span><div class="close_me"><a  id="close_windows"  class="butAcc"  ><img src="images/icon/closeme.png"  alt="closeme"/> </a></div> </div>
  
  <div class="clear"></div>
  <div class="content">
			<form name="demosubmit" id="demosubmit-lightbox">
				  <div class="section" >
					  <label> title name <small>Text custom</small></label>   
					  <div>
				  <select name="titlename" id="titlename2" class="validate[required]" style="width:150px;" >
						 <option value="">Choose Me.</option>
						 <option value="Mrs.">Mrs.</option>
						 <option value="Miss">Miss</option>
						 <option value="Ms.">Ms.</option>
						 <option value="Mr.">Mr.</option>
						 <option value="Master">Master</option>
					</select> 
					  </div>
				 </div>
				  <div class="section">
					  <label> Name <small>Text custom</small></label>   
					  <div> <input type="text" name="name" id="name2" class="validate[required,minSize[3],maxSize[20] ] medium" /><span class="f_help">Name</span></div>
					  <div> <input type="text" name="lastname" id="lastname2" class="validate[required,minSize[3],maxSize[40] ] medium"  /><span class="f_help">Last name</span></div>
				 </div>
				  <div class="section">
					  <label> email <small>Text custom</small></label>   
					  <div> <input type="text" name="email" id="email2" class="validate[required,custom[email]] large" /><span class="f_help">Text custom help</span></div>
				 </div>
				  <div class="section">
					  <label> Gender <small>Text custom</small></label>   
					  <div>
							  <div class="radiorounded">
							  <input type="radio"  id="radiorounded" name="gender"  checked="checked"  value="1"/><label for="radiorounded" title="male"></label>
							  </div>
							  <div class="radiorounded">
							  <input type="radio"  id="radiorounded2" name="gender"  value="2"/><label for="radiorounded2" title="female"></label>
							  </div>
					  </div>
				 </div>
				  <div class="section last">
					  <div><a class="uibutton sendform-lightbox" >submit</a> <a class="uibutton special" onClick="ResetForm()"  >clear form</a></div>
				 </div>
			</form>
  </div>
</div>