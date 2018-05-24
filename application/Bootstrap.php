<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap{
	
	public function _initAutoloader(){
		
		$autoloader = new Zend_Application_Module_Autoloader(array('namespace'=>'Gbc_','basePath'=>dirname(__FILE__)));
        $dir = sys_get_temp_dir();
        session_save_path($dir);
        Zend_Session::start();
        $_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // Return to bootstrap resource registry
		return $autoloader;
	}
	
	protected function _initDoctype(){
		
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
	}
}