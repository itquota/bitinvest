<?php

	/**
	 * @author Rajan Rawal <rajan.rwl@gmail.com>
	 * @version 1.0
	 * @Desc
	 * A custom function to Limit the words in the string so that not allowing word to break abruptly
	 *
	 * @link http://rajan-rawal@blogspot.com
	 * @param str - The string from which we want proper substring
	 * @param limit - The total number of the words from the beginning of the string
	 *
	 * @return boolean - returns string containing the number of words passes as limit from beginning of the string
	 */

class Gbc_Model_Custom_StringLimit{

	
		public function __construct(array $options = null){
		
		if(is_array($options)){
			$this->setOptions($options);
		}
	}
	
    /*
     * @var        : error message
     * @description: Give your special error message.
     */
    public static $err = "invalidInput";
    
    /*
     * @function   : setEncoding
     * @return     : String
     * @parameters : str: Content you want to change the character encoding
     *               newEncoding: Character encoding you want set
     * @description: Convert the character encoding of the string
     *               to newEncoding from currentEncoding. currentEncoding
     *               detecting by function so you only need give str and
     *               newEncoding to the setEncoding function.
     */
    public static function setEncoding($str, $newEncoding) {
       if(is_string($str)){
        	$encodingList = mb_list_encodings();
        	$currentEncoding = mb_detect_encoding($str, $encodingList);
        	$changeEncoding = mb_convert_encoding($str, $newEncoding, $currentEncoding);
        	return $changeEncoding;
    	}else return $str;
    }

    /*
     * @function   : blacklistFilter
     * @return     : String
     * @parameters : str: Content you want to filter with blacklist
     * @description: Filter the content by blacklist method. Library use
     *               RSnake's XSS attack vectors. To add new attack vectors
     *               I'm continue to research.
     */
    public static function blacklistFilter($str, $blackFilterPattern) {
    	//echo "String" . $str ."\n";
        //if (preg_match("/<(.*)s(.*)c(.*)r(.*)i(.*)p(.*)t(.*)>(.*)/i", $str) > 0) {
        $hasBlackListChar = false;
    	
		if (is_string($str) && $blackFilterPattern=="string"){
	        // Search for tags, namespaced elements, 
	        if (preg_match('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', $str) > 0 || 
	    	preg_match('#</*\w+\w[^>]*+>#i', $str) > 0 || 
	    //	preg_match('/[~`\!#\$\^\*\+=\{\}\[\]\|;"\<\>\?\]]/', $str) > 0 ||	
		//	preg_match('/\b(insert|delete|drop|char|alter|begin|cast|convert|cursor|declare|end|exec|fetch|kill|open|sys|table)\b/i', $str) > 0
			preg_match('/\b(insert|delete|drop|char|alter|begin|cast|convert|cursor|declare|exec|fetch|kill|sys|table)\b/i', $str) > 0
	    	){
	    		//echo $str;
				$hasBlackListChar = true;
	    	}	
			
			if($hasBlackListChar){    		
	            return self::$err;
	        } else {
	            return $str;
	        }
		}
		elseif(is_string($str) && $blackFilterPattern=="meeting"){
			// Search for tags, namespaced elements, 
	        if (preg_match('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', $str) > 0 || 
	    	preg_match('#</*\w+:\w[^>]*+>#i', $str) > 0 || 
	    	preg_match('/[\~`\!#\$\^\*\+\{\}\[\]\|\<\>\?\]]/', $str) > 0 ||	
			preg_match('/\b(insert|delete|drop|char|alter|begin|cast|convert|cursor|declare|end|exec|fetch|kill|open|sys|table)\b/i', $str) > 0
	    	){
	    		//echo $str;
				$hasBlackListChar = true;
	    	}	
			if($hasBlackListChar){    		
	            return self::$err;
	        } else {
	            return $str;
	        }	
		}
		elseif(is_string($str) && $blackFilterPattern=="pwd"){
			// Search for tags, namespaced elements, 
	        if (preg_match('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', $str) > 0 || 
	    	preg_match('#</*\w+:\w[^>]*+>#i', $str) > 0 || 
	    	preg_match('/[\~`\$\^\*\+\{\}\[\]\|\<\>\?\]]/', $str) > 0 ||	
			preg_match('/\b(insert|delete|drop|char|alter|begin|cast|convert|cursor|declare|end|exec|fetch|kill|open|sys|table)\b/i', $str) > 0
	    	){
	    		//echo $str;
				$hasBlackListChar = true;
	    	}	
			if($hasBlackListChar){    		
	            return self::$err;
	        } else {
	            return $str;
	        }	
		}
		else return $str;
    }

    /*
     * @function   : whitelistFilter
     * @return     : String
     * @parameters : str: Content you want to filter with blacklist
     *               whiteFilterPattern: Some patterns for filter the
     *               data types.
     * @description: Filter the content by whitelist method. To add
     *               new data types, I'm continue to research.
     */
    public static function whitelistFilter($str, $whiteFilterPattern) {
		if(is_string($str)){

        switch ($whiteFilterPattern) {
            case "string":
                $pattern = "([a-zA-Z]+)";
            break;
            case "number":
                $pattern = "([0-9]+)";
            break;
            case "everything":
                $pattern = "(.*)";
            break;
            default:
                $pattern = "([0-9a-zA-Z]+)";
            break;
        }

        if(preg_match("/^$pattern $/i", $str) > 0) {
            return $str;
        } else {
            return self::$err;
        }
		}else return $str;

    }

    /*
     * @function   : setFilter
     * @return     : String
     * @parameters : str: Content you want to filter with blacklist
     *               filterMethod: Library have 3 method.
     *                  -Black Method
     *                  -White Method
     *                  -Gray Method
     *               filterPattern: Some patterns for filter the
     *               data types. (You can only use with whitelist filter)
     *               noHTMLTag: Use PHP's strip_tags function to
     *               remove HTML tags from content.
     * @description: Filter the content by method.
     */
    public static function setFilter($str, $filterMethod, $filterPattern = NULL, $noHTMLTag = NULL) {

       /* if (urldecode($str) > 0) {
            $str = urldecode($str);
        }
*/
      if (is_string($str)){ 
	   if ($noHTMLTag == 1) {
            $str = strip_tags($str);
        }
        
        $str = strtolower($str);
        $str = addslashes($str);
		$str = htmlspecialchars(trim($str));

		
        switch($filterMethod) {
            case "black":
                $str = self::blacklistFilter($str,$filterPattern);
            break;
            case "white":
                $str = self::whitelistFilter($str, $filterPattern);
            break;
            default:
            break;
        }
	  }

        return $str;
    }
	
	 public static function setFilters($str, $filterMethod, $filterPattern = NULL, $noHTMLTag = NULL) {

       /* if (urldecode($str) > 0) {
            $str = urldecode($str);
        }
*/
      if (is_string($str)){ 
	   if ($noHTMLTag == 1) {
            $str = strip_tags($str);
        }
        
        //$str = strtolower($str);
       // $str = addslashes($str);
	//	$str = htmlspecialchars(trim($str));

		
        switch($filterMethod) {
            case "black":
                $str = self::blacklistFilter($str,$filterPattern);
            break;
            case "white":
                $str = self::whitelistFilter($str, $filterPattern);
            break;
            default:
            break;
        }
	  }

        return $str;
    }
	
	}
?>
