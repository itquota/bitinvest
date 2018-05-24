<script type="text/javascript">
	function addKeepMeUpdated(){

		if(document.getElementById('user_agree').checked==true){

			name = document.getElementById("user_name").value;
			email = document.getElementById("email").value;
			mobile = document.getElementById("mobile").value;
			regexname = /^[a-zA-Z0-9 ]{1,30}$/;
			regexmail = /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
			regexmobile = /^[0-9]{10,13}$/;
			if(!regexname.test(name)){
				document.getElementById('message').innerHTML="Invalid value for Name";
				return false;
			}
			if(!regexmail.test(email)){
				document.getElementById('message').innerHTML="Invalid value for Email";
				return false;
			}
			if(!regexmobile.test(mobile)){
				document.getElementById('message').innerHTML="Invalid value for Mobile";
				return false;
			}
			if(regexname.test(name) && regexmail.test(email) && regexmobile.test(mobile)){

				$.ajax({

					url: "<?php echo BASEPATH;?>/index/keepmeupdatedinsert",
					type: "post",
					data: "user_name="+name+"&email="+email+"&mobile="+mobile,
					success: function(response){
						document.getElementById("user_name").value = "";
						document.getElementById("email").value = "";
						document.getElementById("mobile").value = "";
						document.getElementById('user_agree').checked = false;
						document.getElementById('message').innerHTML=response;
					},
					error: function(){
						document.getElementById('message').innerHTML="The operation was not completed successfully. Please try after some time.";
					}
				});
			}
		}
		else{
			document.getElementById('message').innerHTML="Please tick the box to receive emails and sms";
			return false;
		}
	}
</script>
<div class="namearea" style="height:10px;">
	<div class="nametxt">&nbsp;</div>
	<div class="nametxt" style="width:330px;color:red;text-align:left;font-size:10px;padding-top:8px;" id="message">&nbsp;</div>
</div>
<div class="dividerleftside"></div>
<div class="namearea">
	<div class="nametxt">Name</div>
	<div class="nametextboxarea"><input type="text" name="user_name" id="user_name" class="nametextbox" /></div>
</div>
<div class="dividerleftside"></div>
<div class="namearea"><div class="nametxt">Email</div>
	<div class="nametextboxarea"><input type="text" name="email" id="email" class="nametextbox" /></div>
	
</div>
<div class="dividerleftside"></div>
<div class="namearea"><div class="nametxt">Mobile</div>
	<div class="nametextboxarea"><input type="text" name="mobile" id="mobile" class="nametextbox" /></div>
	<div class="dividerleftside"></div>
	<div>
		<div class="checkboxarea"><input type="checkbox" name="user_agree" id="user_agree" class="checkboxcss" /></div>
		<div class="checkboxtxt">Yes, I wish to recieve Bonus Points member updates.<br />
		   I agree to the Terms &amp; Conditions.
		</div>
	</div>
	<div class="dividerleftside"></div>
	<div class="subscriblebutton" style="cursor:pointer;"><img src="<?php echo BASEPATH;?>/images/home/subscriblebutton.png" alt="subscriblebutton" width="88" height="32" border="0" onclick="addKeepMeUpdated();"/></div>
</div>
			