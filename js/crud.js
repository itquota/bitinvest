function saveForm(url,form_id,container_id,params,button_id){
	
	// validation of input arguments
	if(url==null || form_id==null || container_id==null)jAlert("Invalid or Insufficient Arguments");
	if(params==null)params="";
	if(button_id==null)button_id="";
	var containerObject = document.getElementById(container_id);
	var elementList = containerObject.getElementsByTagName('*');
	var postData = "";
	
	// loop for concatenating all the html elements in the postData string
	for(var i=0;i<elementList.length; i++){
		
		if(elementList[i].id!=null && elementList[i].id!="" && elementList[i].value!=null){
			
			if(elementList[i].type=="checkbox"){
				if(elementList[i].checked){
					if(postData=="")postData = elementList[i].id+"="+elementList[i].value;
					else postData += "&"+elementList[i].id+"="+elementList[i].value;
				}
			}else if(elementList[i].getAttribute("texttype")=="ckeditor_desc"){
				if(postData=="")postData = elementList[i].id+"="+CKEDITOR.instances.PageDescription.getData();
				else postData += "&"+elementList[i].id+"="+CKEDITOR.instances.PageDescription.getData();
			}else if(elementList[i].getAttribute("texttype")=="ckeditor_termscond"){
				if(postData=="")postData = elementList[i].id+"="+CKEDITOR.instances.TermsConditions.getData();
				else postData += "&"+elementList[i].id+"="+CKEDITOR.instances.TermsConditions.getData();
			}else if(elementList[i].getAttribute("texttype")=="ckeditor_longdesc"){
				if(postData=="")postData = elementList[i].id+"="+CKEDITOR.instances.LongDescription.getData();
				else postData += "&"+elementList[i].id+"="+CKEDITOR.instances.LongDescription.getData();
			}else if(elementList[i].getAttribute("texttype")=="ckeditor_termspecial"){
				if(postData=="")postData = elementList[i].id+"="+CKEDITOR.instances.TermsConditions.getData();
				else postData += "&"+elementList[i].id+"="+CKEDITOR.instances.TermsConditions.getData();
			}else if(elementList[i].getAttribute("texttype")=="ckeditor_longspecial"){
				if(postData=="")postData = elementList[i].id+"="+CKEDITOR.instances.LongDescription.getData();
				else postData += "&"+elementList[i].id+"="+CKEDITOR.instances.LongDescription.getData();
			}else if(elementList[i].getAttribute("texttype")=="ckeditor_overviewdesc"){
				if(postData=="")postData = elementList[i].id+"="+CKEDITOR.instances.OverviewDescription.getData();
				else postData += "&"+elementList[i].id+"="+CKEDITOR.instances.OverviewDescription.getData();
			}
			else{
				if(postData=="")postData = elementList[i].id+"="+elementList[i].value;
				else postData += "&"+elementList[i].id+"="+elementList[i].value;
			}
		}
	}
	
	// loop for concatenating any additional parameters in postData string
	if(params instanceof Array){
		
		for(key in params){
			if(postData=="")postData = key+"="+params[key];
			else postData += "&"+key+"="+params[key];
		}
	}
	
	if(button_id!=""){
		document.getElementById(button_id).disabled = true;
		document.getElementById(button_id).value = "Please Wait";
	}
	
	if(params!=""){
		document.getElementById(params).style.display = "block";
	}
	
	// ajax call for form validation
	$.ajax({
		url: url,
		type: "post",
		data: postData,
		success: function(response){
			
			if(button_id!=""){
				document.getElementById(button_id).disabled = false;
				document.getElementById(button_id).value = "Save";
			}
			
			if(params!=""){
				document.getElementById(params).style.display = "none";
			}
			
			var jsonObject = JSON.parse(response);
			if(jsonObject!=null && jsonObject!=""){
			
			if(jsonObject['returnvalue']=="success"){
				//alert(jsonObject['data']["filepc"]);
				document.getElementById(form_id).submit();
			}
			else if(jsonObject['returnvalue']=="validation"){
				
				var validationData = jsonObject['data'];
				//alert("ccc"+jsonObject['data']["filepc"]);
				for(key in validationData){

					var currentObject = document.getElementById(key);
					
					if(currentObject!=null){
						
						var currentErrorObject = document.getElementById(key+"_error");
						if(validationData[key]=="null")currentErrorObject.innerHTML = currentObject.getAttribute("errortag") + " cannot be left Empty";
						else if(validationData[key]=="already exists")currentErrorObject.innerHTML = currentObject.getAttribute("errortag") + " already exists";
						else if(validationData[key]=="invalid")currentErrorObject.innerHTML = "Invalid value for " + currentObject.getAttribute("errortag");
						else if(validationData[key]=="invalid_taskdate")currentErrorObject.innerHTML = "Please select the Correct Date";
						else if(validationData[key]=="notmatch")currentErrorObject.innerHTML = currentObject.getAttribute("errortag") + "entered not matching";
						else if(validationData[key]=="wrong")currentErrorObject.innerHTML =  "Wrong " + currentObject.getAttribute("errortag");
						else if(validationData[key]=="duplicate")currentErrorObject.innerHTML = "This " + currentObject.getAttribute("errortag") + " is already registered";
						else if(validationData[key]=="Zip length")currentErrorObject.innerHTML =  currentObject.getAttribute("errortag") + " must be  atleast 6 characters";
						else if(validationData[key]=="Contact length")currentErrorObject.innerHTML =  currentObject.getAttribute("errortag") + " must be atleast 9 digits";
						else if(validationData[key]=="already exists")currentErrorObject.innerHTML = currentObject.getAttribute("errortag") + " already exists";
						else if(validationData[key]=="either")currentErrorObject.innerHTML = "Either enter value for " + currentObject.getAttribute("errortag") + " or Special Offer Banner Content";
						else if(validationData[key]=="duplicate_combination")currentErrorObject.innerHTML = "This " + currentObject.getAttribute("errortag2") + " is already registered";
						else if(validationData[key]=="notcombination")currentErrorObject.innerHTML = "This " + currentObject.getAttribute("errortag2") + " is not registered";
						else if(validationData[key]=="valid")currentErrorObject.innerHTML = "&nbsp;";
						else{
							currentValidationData = jsonObject['data'][key];
							if(currentValidationData['code']!=null){
								if(currentValidationData['code']=="nomatch")currentErrorObject.innerHTML = currentObject.getAttribute("errortag") + " does not match the " + document.getElementById(currentValidationData['field']).getAttribute("errortag") + " field";
								if(currentValidationData['code']=="match")currentErrorObject.innerHTML = currentObject.getAttribute("errortag") + " cannot be same as " + document.getElementById(currentValidationData['field']).getAttribute("errortag") + " field";
							}
							else currentErrorObject.innerHTML = "unknown response code";
						}
					}
				}
				}
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			jAlert("This operation could not be completed, Please try again. Contact system admin if problem persists");
		}	
	});
}

function deleteRecord(url,id_list,params){
	
	// validation of input arguments
	if(params==null)params="";
	if(url==null||id_list==""||id_list==null)jAlert("Invalid or Insufficient Arguments");
	var postData = "";
	
	$.alerts.okButton = "&nbsp;&nbsp;yes&nbsp;&nbsp;";
	$.alerts.cancelButton = "&nbsp;&nbsp;no&nbsp;&nbsp;";
	
	jConfirm("Are you sure you want to delete this record?","Confirmation",function(r){
		
		$.alerts.okButton = "&nbsp;OK&nbsp;";
		$.alerts.cancelButton = "&nbsp;Cancel&nbsp;";
		
		if(r){
			id_string="";
			if(id_list instanceof Array ){			
				for(var i=0;i<id_list.length;i++){
					if(id_string=="")
						id_string+=id_list[i];
					else
						id_string+=","+id_list[i];					
				}
			}
			else id_string=id_list;
			
			postData="id="+id_string;

			// loop for concatenating any additional parameters in postData string
			if(params instanceof Array){
				
				for(key in params){
					if(postData=="")postData = key+"="+params[key];
					else postData += "&"+key+"="+params[key];
				}
			}
			
			$.ajax({
				
				url: url,
				data: postData,
				type: "post",
				async: 'false',
				success: function(response){
				
					if(response!="" && response!=null){
						
						var jsonObject = JSON.parse(response);
						
						if(jsonObject !="" && jsonObject!=null){	
							if( jsonObject.returnvalue=="error"){
								jAlert(jsonObject.errormsg);
							}
							else if(jsonObject.returnvalue=="success"){
								jAlert("Value deleted successfully");
								window.location.reload();
							}
							else
								jAlert("This operation could not be completed, Please try again. Contact system admin if problem persists");
						}
					}
					else{
						window.location.reload();
					}	
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					jAlert("This operation could not be completed, Please try again. Contact system admin if problem persists");
				}
			});
		}
	});
}