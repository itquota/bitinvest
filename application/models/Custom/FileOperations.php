<?php

class Bankoffercms_Model_Custom_FileOperations{
	
	public function deleteDirectory($dirname){
		
		if(is_dir($dirname)){
			
			if($dir_handle = opendir($dirname)){
				
				while(($entry = readdir($dir_handle))!== false){
					
					if($entry!="." && $entry!=".."){
						
						unlink($dirname."/".$entry);
					}
				}
				
				closedir($dir_handle);
				rmdir($dirname);
				
				return true;
			}
			
			else return false;
		}
	}
}