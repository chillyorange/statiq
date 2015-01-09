<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Filemodel extends CI_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->helper('file');
        
    }
    
    
    public function insert($file, $base_url, $siteID)
    {
    	
    	//insert only doesn't exist yet
    		
    	$q = $this->db->from('files')->where('file_url', $file)->where('site_id', $siteID)->get();
    		
    	if( $q->num_rows() == 0 ) {
    		
    	
    		$data = array(
    			'file_url' => $file,
    		   	'file_crawled' => 0,
    		   	'site_id' => $siteID
    		);
    		
    		$this->db->insert('files', $data);
    			
    		return true;
    		
    	} else {
    		
    		return false;
    		
    	}
    	
    }
    
    
    public function crawled($fileID, $siteID, $notfound = false)
    {
    	
    	if( $notfound ) {
    	
    		$nf = 1;
    	
    	} else {
    	
    		$nf = 0;
    	
    	}
    
    
    	$data = array(
    		'file_crawled' => '1',
    		'file_404' => $nf
    	);
    	
    	$this->db->where('file_id', $fileID);
    	$this->db->where('site_id', $siteID);
    	$this->db->update('files', $data); 
    
    }
    
    
    public function store($contents, $path, $fileName)
    {
    
    	write_file('./sites/'.$path.$fileName, $contents);
    
    }
    
    
    
    /*
    	receives a full url and returns the file name (somefile.html, somefile.php, etc)
    */
    
    public function getFileName($url)
    {
    
    	if( substr($url, -1) == '/' ) {
  			
  			//there's no file in the url, pointing to folder
  			return "index.html";  	
    	
    	} elseif( strpos($url, '.html') !== false || strpos($url, '.htm') !== false || strpos($url, '.xhtml') !== false ) {
    		
    		//HTML file
    		
    		$temp = explode("/", $url);
    		
    		return end($temp);
    		
    	
    	} elseif( strpos($url, '.php') !== false || strpos($url, 'php4') !== false || strpos($url, 'php5') !== false ) {
    	
    		//PHP file
    		
    		$temp = explode("/", $url);
    		
    		return end($temp);
    		
    	
    	} elseif( strpos($url, '.asp') !== false ) {
    	
    		//ASP file
    		
    		$temp = explode("/", $url);
    		
    		return end($temp);
    		
    	
    	} elseif( strpos($url, '.js') !== false ) {
    	
    		//JS file
    		
    		$temp = explode("/", $url);
    		
    		return end($temp);
    	
    	} elseif( strpos($url, '.css') !== false ) {
    	
    		//CSS file
    		
    		$temp = explode("/", $url);
    		
    		return end($temp);
    	
    	} elseif( strpos($url, '.jpg') !== false || strpos($url, '.jpeg') !== false || strpos($url, '.png') !== false || strpos($url, '.gif') !== false ) {
    	
    		//IMAGE file
    		
    		$temp = explode("/", $url);
    		
    		return end($temp);
    	
    	} elseif( strpos($url, '.eot') !== false || strpos($url, '.woff') !== false || strpos($url, '.ttf') !== false || strpos($url, '.svg') !== false ) {
    	
    		//@font-face files
    		$temp = explode("/", $url);
    		
    		return end($temp);
    	
    	}
    
    	return false;
    
    }
    
    
    public function createDirForHtml($ancor, $fileName)
    {
    	
    	$dir = str_replace("http://", "", $ancor);
    	
    	$dir = str_replace($fileName, "", $dir);
    	    	
    	if (!is_dir('sites/'.$dir)) {
    	
    	    mkdir('./sites/'.$dir, 0777, TRUE);
    	
    	}
    	
    	return $dir;
    	    
    }
    
    
    public function createDirForJs($scriptUrl, $fileName)
    {
    
    	$dir = str_replace("http://", "", $scriptUrl);
    	
    	$dir = str_replace($fileName, "", $dir);
    	
    	if (!is_dir('sites/'.$dir)) {
    	
    	    mkdir('./sites/'.$dir, 0777, TRUE);
    	
    	}
    	
    	return $dir;
    
    }
    
    
    public function createDirForCSS($stylesheetUrl, $fileName)
    {
    
    	$dir = str_replace("http://", "", $stylesheetUrl);
    	
    	$dir = str_replace($fileName, "", $dir);
    	
    	if (!is_dir('sites/'.$dir)) {
    	
    	    mkdir('./sites/'.$dir, 0777, TRUE);
    	
    	}
    	
    	return $dir;
    
    }
    
    
    public function createDirForImage($imageUrl, $fileName)
    {
    
    	$dir = str_replace("http://", "", $imageUrl);
    	
    	$dir = str_replace($fileName, "", $dir);
    	
    	if (!is_dir('sites/'.$dir)) {
    	
    	    mkdir('./sites/'.$dir, 0777, TRUE);
    	
    	}
    	
    	return $dir;
    
    }
    
    
    public function createDirForFile($fileUrl, $fileName)
    {
    
    	$dir = str_replace("http://", "", $fileUrl);
    	
    	$dir = str_replace($fileName, "", $dir);
    	
    	if (!is_dir('sites/'.$dir)) {
    	
    	    mkdir('./sites/'.$dir, 0777, TRUE);
    	
    	}
    	
    	return $dir;
    
    }
    
           
}