<?php session_start();
// connect  server 
include ('config/config.php');

if(isset($_POST["username"]) and isset($_POST["password"])){
sleep(1);
$pass_login=$_POST["password"];
 // $pass_login=md5($_POST["password"]);  if use md5 encode 
$result=q("SELECT * FROM " .$prefix_table."user  where  username='".$_POST["username"]."' and password='".$pass_login."' ");

	  if(mysql_num_rows($result)==0){ 
			$return_arr["status"]=0;		
			echo json_encode($return_arr); // return value 
	  }else{
	  $row=mysql_fetch_assoc($result);
			if($_POST["remember"]){ //  if remeber checked
					$cookieTime=time()+3600*24*356;	 //  cookie  time
					setcookie("account_name",$row[username],$cookieTime); 
					// create cookie  ("your cookie name", parameter , cookie time )
			}else{ 
					$_SESSION["account_name"]=$row[username];	// create SESSION  
			}
			$return_arr["status"]=1;		 
			echo json_encode($return_arr); // return value 
}  //end else
exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
        <meta charset="utf-8">
        <title>Ziceinclude&trade; admin version 1.7 online</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <link type="text/css" rel="stylesheet" href="components/bootstrap/bootstrap.css" />
        <link type="text/css" rel="stylesheet" href="css/zice.style.css"/>
        <style type="text/css">
        html {
            background-image: none;
        }
		body{
			background-position:0 0;
			}
        label.iPhoneCheckLabelOn span {
            padding-left:0px
        }
        #versionBar {
            background-color:#212121;
            position:fixed;
            width:100%;
            height:35px;
            bottom:0;
            left:0;
            text-align:center;
            line-height:35px;
            z-index:11;
            -webkit-box-shadow: black 0px 10px 10px -10px inset;
            -moz-box-shadow: black 0px 10px 10px -10px inset;
            box-shadow: black 0px 10px 10px -10px inset;
        }
        .copyright{
            text-align:center; font-size:10px; color:#CCC;
        }
        .copyright a{
            color:#A31F1A; text-decoration:none
        }    
        </style>
        </head>
        <body >
         
        <div id="successLogin"></div>
        <div class="text_success"><img src="images/loadder/loader_green.gif"  alt="ziceAdmin" /><span>Please wait</span></div>
        
        <div id="login" >
          <div class="ribbon"></div>
          <div class="inner clearfix">
          <div class="logo" ><img src="images/logo/logo_login.png" alt="ziceAdmin" /></div>
          <div class="formLogin">
         <form name="formLogin"  id="formLogin" method="post">
      
                <div class="tip">
                      <input name="username" type="text"  id="username_id"  title="Username"   />
                </div>
                <div class="tip">
                      <input name="password" type="password" id="password"   title="Password"  />
                </div>
      
                <div class="loginButton">
                  <div style="float:left; margin-left:-9px;">
                      <input type="checkbox" id="on_off" name="remember" class="on_off_checkbox"  value="1"  />
                      <span class="f_help">Remember me</span>
                  </div>
                  <div class=" pull-right" style="margin-right:-8px;">
                      <div class="btn-group">
                        <button type="button" class="btn" id="but_login">Login</button>
                        <button type="button" class="btn" id="forgetpass"> Forget Pass</button>
                      </div>
                     <span class="f_help">or <a href="#" id="createacc">Create Account</a></span>
                  </div>
                  <div class="clear"></div>
                </div>
      
          </form>
          <form id="createaccPage" method="post" action="">
                <div class="tip">
                      <input name="email_acc" type="text" class="inputtext"  placeholder="Email"  title="Email"   />
                </div>
                <div class="tip">
                      <input name="fname_acc"  type="text" class="inputtext"  placeholder="First name" title="First name"  />
                </div>
                <div class="tip">
                      <input name="lname_acc"  type="text" class="inputtext" placeholder="Last name" title="Last name"   />
                </div>
                <div class="tip">
                      <input name="password_acc" type="text" class="inputtext" placeholder="Password" title="Password"  />
                </div>
                <div class="tip">
                      <input name="birthday_acc"  type="text" class="inputtext"  placeholder="Date of Birth"  title="Date of Birth"  />
                </div>
                <div class="loginButton" align="center">
                        <button type="button" class="btn" id="backLogin"><i class="icon-caret-left"></i> Back </button>
                        <button type="button" class="btn btn-danger white " onClick="$('#createaccPage').submit();"><i class="icon-unlock"></i> Regester </button>
                </div>
          </form>
          </div>
        </div>
          <div class="shadow"></div>
        </div>
        
        <!--Login div-->
        <div class="clear"></div>
        <div id="versionBar" >
          <div class="copyright" > &copy; Copyright 2012  All Rights Reserved <span class="tip"><a  href="#" title="Zice Admin" >Your company</a> </span> </div>
          <!-- // copyright-->
        </div>
        
        <!-- Link JScript-->
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="components/ui/jquery.ui.min.js"></script>
        <script type="text/javascript" src="components/form/form.js"></script>
        <script type="text/javascript" src="js/login_php.js"></script>
		<script type="text/javascript" >
        $(document).ready(function () {	 
                $('#createacc').click(function(e){
                    $('#login').animate({   height: 350, 'margin-top': '-200px' }, 300);	
                    $('.formLogin').animate({   height: 240 }, 300);
                    $('#createaccPage').fadeIn();
                    $('#formLogin').hide();
                });
                $('#backLogin').click(function(e){
                    $('#login').animate({   height: 254, 'margin-top': '-148px' }, 300);	
                    $('.formLogin').animate({   height: 150 }, 300);
                    $('#formLogin').fadeIn();
                    $('#createaccPage').hide();
                });			
        });		
        </script>
        </body>
        </html>