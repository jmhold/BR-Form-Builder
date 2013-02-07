jQuery(document).ready(function(c){c(".visual-form-builder").each(function(){c(this).validate({rules:{"vfb-secret":{required:true,digits:true,maxlength:2}},onkeyup:function(h){if(h.type=="password"){this.element(h)}else{return true}},errorPlacement:function(h,i){if(i.is(":radio")||i.is(":checkbox")){h.appendTo(i.parent().parent())}else{if(i.is(":password")){h.hide()}else{h.insertAfter(i)}}}})});c(".vfb-date-picker").datepicker();if(c(".vfb-page #sendmail").is(":visible")){c(".vfb-page #sendmail").prop("disabled",false)}else{c(".vfb-page #sendmail").prop("disabled","disabled")}c(document).on("click",".vfb-page-next",function(i){i.preventDefault();var j=c(this).attr("id"),h=parseInt(j.replace(/page-/,""));c(".page-"+h).fadeIn();c("html, body").animate({scrollTop:c(".page-"+h).offset().top-50});c(this).fadeOut();if(c(".vfb-page #sendmail").is(":visible")){c(".vfb-page #sendmail").prop("disabled",false)}else{c(".vfb-page #sendmail").prop("disabled","disabled")}});c(".colorPicker").each(function(){var i=c(this),j=i.prop("id"),h=j.replace(/color-/,"");i.farbtastic("#vfb-"+h)});c(".colorPicker").hide();c(".color:input").focus(function(){var i=c(this).prop("id"),h=i.replace(/vfb-/,"");c("#color-"+h).show()}).blur(function(){var i=c(this).prop("id"),h=i.replace(/vfb-/,"");c("#color-"+h).hide()});c(".auto").each(function(){var h=c(this).closest("form").find('input[name="form_id"]').val();var i=c(this).prop("id").match(new RegExp(/(\d+)$/g),"");c("#"+c(this).prop("id")).autocomplete({delay:200,source:function(k,j){c.ajax({url:VfbAjax.ajaxurl,type:"GET",async:true,cache:false,dataType:"json",data:{action:"visual_form_builder_autocomplete",term:k.term,form:h,field:i[0]},success:function(l){j(c.map(l,function(m){return{value:m.value}}))}})}})});if(window.VfbRules){var f=c.parseJSON(VfbRules.rules);var b=[];d(f);c(f).each(function(){c.each(this.rules,function(h){b.push("[name^=vfb-"+this.field+"]")})});c(b.join(",")).change(function(){d(f)})}function e(m,l,h){var j=c("[name^=vfb-"+m+"]");if(j.length>0){for(var k=0;k<j.length;k++){if(c(j[k]).is("[type=checkbox],[type=radio]")){if(a(c(j[k]).val())==l&&c(j[k]).is(":checked")){return true}}else{if(c(j[k]).is("select")){if(a(c(j[k]).val())==l){return true}}}}}else{if(a(c("[name^=vfb-"+m+"]").val())==l){return true}}return false}function a(h){if(!h){return""}var h=h.split("|");return h[0]}function d(h){c(h).each(function(){var l=this.field_id,o=this.field_id_attr,j=this.conditional_show,n=this.rules,k=this.conditional_logic,m=0,i="";i=(j=="show")?"hide":"show";c.each(n,function(p){if((this.condition=="is"&&e(this.field,this.option,o))||(this.condition=="isnot"&&!e(this.field,this.option,o))){m++}});if((k=="all"&&m==n.length)||(k=="any"&&m>0)){c("[id$="+o+"], [class*="+o+"]")[j]()}else{c("[id$="+o+"], [class*="+o+"]")[i]()}})}c(".vfb-textarea-word-count").keyup(function(){var j={},h=this.value.match(/\b/g),i=0;j[this.id]=h?h.length/2:0;c.each(j,function(m,l){i+=l});c(this).parent().find(".vfb-word-count-total").text(i)});function g(h){return h.replace(/<.[^<>]*?>/g," ").replace(/&nbsp;|&#160;/gi," ").replace(/[.(),;:!?%#$'"_+=\/-]*/g,"")}c.validator.addMethod("username",function(l,k){var j=this,h,i;c.validator.messages.username="Please enter a valid username";if(l.match(/[&\.\+\?;\s_@-]/)&&l.length>0){i=l.match(/[&\.\+\?;\s_@-]/);i=(l.match(/\s/))?"space, tab, or linebreak":i;c.validator.messages.username="Invalid character detected: "+i;return false}if(l.length>0){c.ajax({url:VfbAjax.ajaxurl,type:"POST",async:false,cache:false,dataType:"text",data:{action:"visual_form_builder_check_username",username:l},success:function(m){h=(m=="true")?true:false;c.validator.messages.username=l+" is already in use."}})}return this.optional(k)||h},c.validator.format(c.validator.messages.username));c.validator.addMethod("phone",function(i,h){i=i.replace(/[\+\s\(\)\.\-\ ]/g,"");return this.optional(h)||i.length>9&&i.match(/^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/)},c.validator.format("Please enter a valid phone number. Most US/Canada and International formats accepted."));c.validator.addMethod("ipv4",function(i,h){return this.optional(h)||/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i.test(i)},c.validator.format("Please enter a valid IP v4 address."));c.validator.addMethod("ipv6",function(i,h){return this.optional(h)||/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(i)},c.validator.format("Please enter a valid IP v6 address."));c.validator.addMethod("maxWords",function(i,h,j){return this.optional(h)||g(i).match(/\b\w+\b/g).length<=j},c.validator.format("Please enter {0} words or less."));c.validator.addMethod("minWords",function(i,h,j){return this.optional(h)||g(i).match(/\b\w+\b/g).length>=j},c.validator.format("Please enter at least {0} words."));c.validator.addMethod("rangeWords",function(k,h,l){var j=g(k);var i=/\b\w+\b/g;return this.optional(h)||j.match(i).length>=l[0]&&j.match(i).length<=l[1]},c.validator.format("Please enter between {0} and {1} words."))});(function(f){var b=/[a-z]/,g=/[A-Z]/,e=/[0-9]/,i=/[0-9].*[0-9]/,a=/[^a-zA-Z0-9]/,h=/^(.)\1+$/;function d(j,k){return{rate:j,messageKey:k}}function c(j){return j.substring(0,1).toLowerCase()+j.substring(1)}f.validator.passwordRating=function(l,p){if(!l||l.length<8){return d(0,"too-short")}if(p&&l.toLowerCase().match(p.toLowerCase())){return d(0,"similar-to-username")}if(h.test(l)){return d(1,"very-weak")}var k=b.test(l),m=g.test(c(l)),o=e.test(l),n=i.test(l),j=a.test(l);if(k&&m&&o||k&&n||m&&n||j){return d(4,"strong")}if(k&&m||k&&o||m&&o){return d(3,"good")}return d(2,"weak")};f.validator.passwordRating.messages={"similar-to-username":"Too similar to username","too-short":"Too short","very-weak":"Very weak",weak:"Weak",good:"Good",strong:"Strong"};f.validator.addMethod("password",function(n,k,o){var j=k.value,p=f(typeof o!="boolean"?o:[]);var l=f.validator.passwordRating(j,p.val());var m=f(".password-meter",k.form);m.removeClass("similar-to-username too-short very-weak weak good strong").addClass(l.messageKey).text(f.validator.passwordRating.messages[l.messageKey]);if(this.optional(k)){m.removeClass("similar-to-username too-short very-weak weak good strong").text("Password Strength");return true}return l.rate>2},"&nbsp;");f.validator.classRuleSettings.password={password:true}})(jQuery);