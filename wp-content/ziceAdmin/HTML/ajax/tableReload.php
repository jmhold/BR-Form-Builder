<?php
// connect  server 
include ('../config/config.php'); 
		$result =q("SELECT  * FROM " .$prefix_table."reload  ORDER BY id  DESC");
?>
<script>
$(document).ready(function(){	
	$('.data_table3').dataTable({
	  "sDom": "<'row-fluid tb-head'<'span6'f><'span6'<'pull-right'Cl>>r>t<'row-fluid tb-foot'<'span4'i><'span8'p>>",
	  "bJQueryUI": false,
	  "iDisplayLength": 10,
	  "sPaginationType": "bootstrap",
	  "oLanguage": {
		  "sLengthMenu": "_MENU_",
		  "sSearch": "Search"
	  }
	});
	// Select boxes
	$("select").not("select.chzn-select,select[multiple],select#box1Storage,select#box2Storage").selectBox();
	// Fancybox 
	$(".pop_box").fancybox({ 'showCloseButton': false, 'hideOnOverlayClick'	:	false });	
});	
</script>
                              <div class="btn-group pull-top-right btn-square">
                                <a class="btn  btn-large pop_box" href="ajax/lightboxadd.php"><i class="icon-plus"></i>  Add User</a>
                                <a class="btn  btn-large btn-danger DeleteAll" href="javascript:void(0)"><i class="icon-trash"></i> Delete  All</a>
                              </div>
                              <form>
                                <table class="table table-bordered table-striped  data_table3" id="data_table3" >
                                <thead>
                                  <tr align="center">
                                    <th width="15" >
										<div class="checksquared"><input type="checkbox"  class="checkAll" /><label></label></div>
									</th>
                                    <th width="352" align="left">Name</th>
                                    <th width="174" >Email</th>
                                    <th width="246" >Date register</th>
									<th width="199" >Status</th>
                                  </tr>
                                </thead>
                                <tbody align="center">
<?php
		$i=1;
		while($arr=mysql_fetch_assoc($result)){
?>
                                  <tr>
                                    <td  width="15" ><div class="checksquared"><input type="checkbox"  name="checkbox[]" /><label></label></div>
									</td>
                                    <td  align="left"><?php echo $arr[title]." ".$arr[name]."   ".$arr[lastname]?></td>
                                    <td ><?php echo $arr[email]?></td>
                                    <td ><?php echo $arr[datetime]?></td>
									<td >
										<span class="checkslide">
											<input type="checkbox" checked="checked" />
											<label data-on="ON" data-off="OFF"></label>
										</span>
									</td>
                                  </tr>
<? $i++; }?>
                                </tbody>
                              </table>
                              </form>