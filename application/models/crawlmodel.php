<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Crawlmodel extends CI_Model {
	
    function __construct() {
		
        parent::__construct();
        
        $this->load->database();
		$this->load->helper('file');
        
    }
	
	
	/*
		
		Inserts ancor item into the database
	
	*/
	
    public function insertAncor($ancor, $base_url, $siteID) {
    	
    	//filter out links to images
    	if( strpos($ancor, '.jpg') !== false || strpos($ancor, '.jpeg') !== false || strpos($ancor, '.png') !== false || strpos($ancor, '.gif') !== false || strpos($ancor, '.bmp') !== false || strpos($ancor, '.pdf') !== false || strpos($ancor, '.swf') !== false ) {
    	
    	    return false;
    	    
    	}
    	
    	//filter out mailto links
    	if( strpos($ancor, "mailto") !== false ) {
    	    
    	    return false;
    	
    	}    	

    	
    	
    	//insert only doesn't exist yet
		
		//echo $ancor."<br>";
    	
    	$q = $this->db->from('ancors')->where('ancor_href', $ancor)->where('site_id', $siteID)->get();
    	    	
    	if( $q->num_rows() == 0 ) {
    	
    
    		$data = array(
    			'ancor_href' => $ancor,
    	   		'ancor_crawled' => 0,
    	   		'site_id' => $siteID
    		);
    	
    		$this->db->insert('ancors', $data);
    		
    		return true;
    	
    	} else {
    	
    		return false;
    	
    	}
    
    }
    
    
	/*
		
		Sets the "crawled" status of an ancor item
	
	*/
	
    public function ancorCrawled($ancorID, $siteID, $notfound = false) {
    
    	if( $notfound ) {
    	
    		$nf = 1;
    	
    	} else {
    	
    		$nf = 0;
    	
    	}
    	
    
    	$data = array(
   			'ancor_crawled' => '1',
   			'ancor_404' => $nf
    	);
    	
    	$this->db->where('ancor_id', $ancorID);
    	$this->db->where('site_id', $siteID);
    	$this->db->update('ancors', $data); 
    
    }
	
	
	
	/*
		
		Inserts file item into the database
	
	*/
	
    public function insertFile($file, $base_url, $siteID)
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
	
	
	/*
		
		Sets the "crawled" status of a file item
	
	*/
	
    public function fileCrawled($fileID, $siteID, $notfound = false)
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
	
	
	/*
	
		Stores files on the server
		
	*/
	
    public function store($contents, $path, $fileName, $type = '')
    {
		
		$fileName = ($type == 'HTML' && $fileName == '')? "index.html" : $fileName;
		
		if( substr($path, -1) != '/' ) {
			$path .= '/';
		}
		    	
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
	
	
	/*
		
		Insert an image item into the database
	
	*/
	
    public function insertImage($image, $base_url, $siteID)
    {	
    	
    	//make sure the href contains the URL
    	if( strpos($image, $base_url) === false ) {
    	    
    	    return false;
    	
    	}
    	
    
    	//insert only doesn't exist yet
    		
    	$q = $this->db->from('images')->where('image_src', $image)->where('site_id', $siteID)->get();
    		
    	if( $q->num_rows() == 0 && $image != '' && is_string($image) ) {
    		
    	
    		$data = array(
    			'image_src' => $image,
    			'site_id' => $siteID
    		);
    		
    		$this->db->insert('images', $data);
    		    		    		    			
    		return true;
    		
    	} else {
    		
    		return false;
    		
    	}
    
    }
	
	
	/*
	
		sets the "crawled" status of an image item
		
	*/
	
    public function imageCrawled($imageID, $siteID, $notfound = false)
    {
    	
    	if( $notfound ) {
    	
    		$nf = 1;
    	
    	} else {
    	
    		$nf = 0;
    	
    	}
    
    
    	$data = array(
    		'image_crawled' => '1',
    		'image_404' => $nf
    	);
    	
    	$this->db->where('image_id', $imageID);
    	$this->db->where('site_id', $siteID);
    	$this->db->update('images', $data); 
    
    }
	
	
	/*
		
		inserts a script item into the database
	
	*/
	
    public function insertScript($script, $base_url, $siteID)
    {	
    	
    	//remove ?v=whatever stuff
    	
    	$temp = explode("?", $script);
    	
    	if( count($temp) > 1 ) {
    		
    		//has it
    		
    		$script = $temp[0];
    	
    	}
    	
    	
    	//make sure the href contains the URL
    	if( strpos($script, $base_url) === false ) {
    	    
    	    return false;
    	
    	}
    	
    
    	//insert only doesn't exist yet
    		
    	$q = $this->db->from('scripts')->where('script_url', $script)->where('site_id', $siteID)->get();
    		
    	if( $q->num_rows() == 0 && $script != '' && is_string($script) ) {
    		
    	
    		$data = array(
    			'script_url' => $script,
    			'site_id' => $siteID
    		);
    		
    		$this->db->insert('scripts', $data);
    		    		    			
    		return true;
    		
    	} else {
    		
    		return false;
    		
    	}
    
    }
	
	
	/*
	
		
	
	*/
	
    public function scriptCrawled($scriptID, $siteID, $notfound = false)
    {
    	
    	if( $notfound ) {
    	
    		$nf = 1;
    	
    	} else {
    	
    		$nf = 0;
    	
    	}
    	
    
    	$data = array(
    		'script_crawled' => '1',
    		'script_404' => $nf
    	);
    	
    	$this->db->where('script_id', $scriptID);
    	$this->db->where('site_id', $siteID);
    	$this->db->update('scripts', $data); 
    
    }
	
	
	/*
	
		Creates a site item
	
	*/
	
    public function createSite($url)
    {
    
    	
    	//domain name retrievel
    	
    	$temp = explode("/", $url);
    	
    	if( count($temp) > 1 ) {
    	
    		$domain = $temp[0];
    	
    	} else {
    	
    		$domain = $url;
    	
    	}
    	
    
    	$data = array(
    	   'site_url' => $url,
    	   'site_domain' => $domain
    	);
    	
    	$this->db->insert('sites', $data);
    	
    	return $this->db->insert_id();
    
    }
	
	
	/*
	
		Get site details
	
	*/
	
    public function getSiteDetails($siteID)
    {
    	
    	$query = $this->db->from('sites')->where('site_id', $siteID)->get();
    	
    	if( $query->num_rows() > 0 ) {
    	
    		$res = $query->result();
    	
    		return $res[0];
    	
    	} else {
    	
    		return false;
    	
    	}
    
    }
	
	
	
    public function getDomain($siteID)
    {
    
    	$query = $this->db->from('sites')->where('site_id', $siteID)->get();
    	
    	if( $query->num_rows() > 0 ) {
    	
    		$res = $query->result();
    	
    		return $res[0]->site_url;
    	
    	} else {
    	
    		return false;
    	
    	}
    
    }
	
	
	
    public function getBaseUrl($siteID)
    {
    	
    	$query = $this->db->from('sites')->where('site_id', $siteID)->get();
    	
    	if( $query->num_rows() > 0 ) {
    	
    		$res = $query->result();
    	
    		return $res[0]->site_domain;
    	
    	} else {
    	
    		return false;
    	
    	}
    
    }
	
	
	
    public function deleteSite($siteID, $base_url)
    {
    
    	//ancors table
    	$this->db->delete('ancors', array('site_id' => $siteID));
    	
    	
    	//files table
    	$this->db->delete('files', array('site_id' => $siteID));
    	
    	
    	//images table
    	$this->db->delete('images', array('site_id' => $siteID));
    	
    	
    	//scripts table
    	$this->db->delete('scripts', array('site_id' => $siteID));
    	
    	
    	//stylesheets table
    	$this->db->delete('stylesheets', array('site_id' => $siteID));
    	
    	
    	$domain = ltrim($base_url, "http://");
    	
    	$domain = rtrim($domain, "/");
    	
    	
    	$temp = explode("/", $domain);
    	
    	if( count($temp) > 1 ) {
    	
    		$d = $temp[0];
    	
    	} else {
    	
    		$d = $domain;
    	
    	}
    	
    	
    	//delete files
    	rrmdir( "/home/mattijs/public_html/sites/".$d."/" );
    
    }
	
	
	/*
	
		Creates a new stylesheet model
	
	*/
	
    public function insertStylesheet($stylesheet, $base_url, $siteID)
    {	
    
    	//remove ?v=whatever stuff
    	
    	$temp = explode("?", $stylesheet);
    	
    	if( count($temp) > 1 ) {
    		
    		//has it
    		
    		$stylesheet = $temp[0];
    	
    	}
    	
    	
    	//make sure the href contains the URL
    	if( strpos($stylesheet, $base_url) === false ) {
    	    
    	    //return false;
    	
    	}
    	
    
    	//insert only doesn't exist yet
    		
    	$q = $this->db->from('stylesheets')->where('stylesheet_url', $stylesheet)->where('site_id', $siteID)->get();
    		
    	if( $q->num_rows() == 0 && $stylesheet != '' && is_string($stylesheet) ) {
    		    	
    		$data = array(
    			'stylesheet_url' => $stylesheet,
    			'site_id' => $siteID
    		);
    		
    		$this->db->insert('stylesheets', $data);
    		    		    		    			
    		return true;
    		
    	} else {
    		
    		return false;
    		
    	}
    
    }
	
	
	/*
	
		Sets the "crawled" status of stylesheet item
	
	*/
	
    public function stylesheetCrawled($stylesheetID, $siteID, $notfound = false)
    {
    	
    	if( $notfound ) {
    	
    		$nf = 1;
    	
    	} else {
    	
    		$nf = 0;
    	
    	}
    
    	$data = array(
    		'stylesheet_crawled' => '1',
    		'stylesheet_404' => $nf
    	);
    	
    	$this->db->where('stylesheet_id', $stylesheetID);
    	$this->db->where('site_id', $siteID);
    	$this->db->update('stylesheets', $data); 
    
    }
	
	
	/*
	
		prepares URLs for insertion into database, takes any URL and returns a proper absolute URL
	
	*/
	
	public function prepUrl($link, $url, $domain) {
		
		//basic filtering of URLs we don't need
    	
		//filter out links starting with '#'
    	if( $link[0] == '#' ) {
    	
    		return false;
    	
    	}
		
		//filter out external links
		if( (strpos($link,'http://') !== false || strpos($link,'https://') !== false) && strpos($ancor,$domain) === false ) {
			
			return false;
			
		}
		
				
		//absolute or relative URL
		
		if( 0 === strpos($link, "http") || 0 === strpos($link, "https") || 0 === strpos($link, "/") ) {//absolute
			
			if( 0 === strpos($link, "/") ) {//absolute without domain, append domain
				
				return "http://".$domain.$link;
				
			} else {//nothing to do
				
				return $link;
				
			}
			
		} else {//relative
			
			//echo rel2abs($link, $url)."<br>";
			
			return rel2abs($link, $url);
			
		}
		
	}
	
}

/* End of file Crawler.php */