<script type="text/javascript">
function getSearchCategories(city_id){

	document.getElementById('search_row3').style.display = 'none';

	if(city_id=='all'){
		options_ele = document.getElementById('search_category');
		for(k=options_ele.length-1;k>0;k--){
			options_ele.remove(k);
		}
	}
	else if(city_id!="" && city_id!=null){

		document.getElementById('cat_loader').style.display = "block";
		
		$.ajax({

			url:'<?php echo BASEPATH;?>/index/getsearchcategories',
			type: 'post',
			data:'city_id='+city_id,
			success:function(response){

				if(response!="" && response!=null){

				
					 var jsonObject=	JSON.parse(response);
					
					options_ele = document.getElementById('search_category');
					for(k=options_ele.length-1;k>0;k--){
						options_ele.remove(k);
					}
					var j=0;
					if(jsonObject[j]['id']=='null'){
						var option_element = new Option("No Category for this city",jsonObject[j]['id']);
						document.getElementById('search_category').options[++j] = option_element;
					}
					else{
						var j=0;
						for(key in jsonObject){

							var option_element = new Option(jsonObject[key]['name'],jsonObject[key]['id']);
							document.getElementById('search_category').options[++j] = option_element;
						}
					}

					document.getElementById('cat_loader').style.display = "none";
				}
			},
			error: function(){
				jAlert('The operation was not completed successfully.Please try again later.');
			}
		});
	}
}

function getSubCategories(cat_id){

	if(cat_id == 9 || cat_id == 1){

		document.getElementById('search_row3').style.display = 'block';
		
		if(cat_id == 9){

			document.getElementById('sub_cat_loader').style.display = "block";
			
			$.ajax({

				url:'<?php echo BASEPATH;?>/index/getcuisines',
				type: 'post',
				data:'cat_id='+cat_id,
				success:function(response){

					if(response!="" && response!=null){

						 var jsonObject=	JSON.parse(response);
					
						options_ele = document.getElementById('sub_category');
						for(k=options_ele.length-1;k>0;k--){
							options_ele.remove(k);
						}

						var j=0;
						for(key in jsonObject){

							var option_element = new Option(jsonObject[key]['name'],jsonObject[key]['id']);
							document.getElementById('sub_category').options[++j] = option_element;
						}

						document.getElementById('sub_cat_loader').style.display = "none";
					}
				},
				error: function(){
					jAlert('The operation was not completed successfully.Please try again later.');
				}
			});
		}
		if(cat_id == 1){

			document.getElementById('sub_cat_loader').style.display = "block";
			
			options_ele = document.getElementById('sub_category');
			for(k=options_ele.length-1;k>0;k--){
				options_ele.remove(k);
			}
			
			for(j=1;j<=5;j++){
				
				var option_element = new Option('Rating-'+j,j);
				document.getElementById('sub_category').options[j] = option_element;
			}
			document.getElementById('sub_cat_loader').style.display = "none";
		}
	}
	else{
		
		document.getElementById('search_row3').style.display = 'none';
	}
}
</script>
<form id="form4" name="form4" method="post" action="<?php echo BASEPATH;?>/index/bonuspartners">
	<div class="middletline">
		<div class="selectcityarea" style="float:none;">
			<div class="selectcitytxt">Select City</div>
			<div class="selectcitydrapdownbg">
			  <select name="search_cities" id="search_cities" onchange="getSearchCategories(this.value);" class="selectcitydrapdownmenu">
			    <option value="all">All</option>
			    <?php
					
					if(isset($this->cities) && $this->cities!=""){
						
						foreach ($this->cities as $city){
				?>
				<option value="<?php echo $city->id;?>" ><?php echo $city->city_name;?></option>
				<?php
						}
					}
				?>
			  </select>
			</div>
		</div>
		<div class="rightsidedivider"></div>
		<div style="clear:both;height:0px;">&nbsp;</div>
		<div class="selectcattxt">Select Category</div>
		<div class="selectcatbg">
		  <select name="search_category" id="search_category" onchange="getSubCategories(this.value);" class="selectcatdrapdownmenu">
		    <option value="all">All</option>
		  </select>
		</div><span id="cat_loader" class="fl" style="display:none;padding-left:5px;"><img src="<?php echo BASEPATH;?>/images/loading19.gif" /></span>
		<div class="rightsidedivider"></div>
		
		<div class="selectsubcatarea" style="height:45px;width:300px;">
			<div id="search_row3" style="display:none;">
				<div class="subcatdrapdownleft">
				<div class="subcatdrapdowntxt">Select Sub Category</div>
				<div class="subcatdrapdownbg">
				  <select name="sub_category" id="sub_category" class="selectsubcatdrapdownmenu">
				    <option value="all">All</option>
				  </select>
				</div>
				<div class="subcatdivider1" style="float:left;"><span id="sub_cat_loader" style="display:none;padding-left:5px;;"><img src="<?php echo BASEPATH;?>/images/loading19.gif" /></span></div>
				<div style="clear:both;height:0px;">&nbsp;</div>
				</div>
			</div>
		</div>
		<div class="rightsidedivider"></div>
		<div class="searchbuttonarea">
			<div class="searchbutton"><input type="image" src="<?php echo BASEPATH;?>/images/home/searchbutton.gif" alt="Submit" /></div>
			<div class="searchbuttondivider"></div>
			<div class="browsealltxt"><a href="<?php echo BASEPATH;?>/index/bonuspartners/search_cities/all/search_category/all/sub_category/all" class="browsealltxt">Browse all Bonus Points Partners</a></div>
		</div>
	</div>
 </form>