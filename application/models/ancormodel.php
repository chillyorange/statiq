<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ancormodel extends CI_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        
    }
    
    
    public function insert($ancor, $base_url, $siteID)
    {
    	
    	//fix up URLs starting with '/'
    	if( $ancor[0] == '/' ) {
    	
    		$ancor = $base_url.trim($ancor, '/');
    		
    	}
    	
    	//filter out links starting with '#'
    	if( $ancor[0] == '#' ) {
    	
    		return false;
    	
    	}
    	
    	
    	//filter out links to images
    	if( strpos($ancor, '.jpg') !== false || strpos($ancor, '.jpeg') !== false || strpos($ancor, '.png') !== false || strpos($ancor, '.gif') !== false || strpos($ancor, '.bmp') !== false || strpos($ancor, '.pdf') !== false || strpos($ancor, '.swf') !== false ) {
    	
    	    return false;
    	    
    	}
    	
    	//filter out mailto links
    	if( strpos($ancor, "mailto") !== false ) {
    	    
    	    return false;
    	
    	}
    	
    	
    	//make sure the href contains the URL (at the beginning)
    	
    	if (0 !== strpos($ancor, "http://".$base_url)) {
    	       	   
    	   return false;
    	   
    	}
    	
    	/*if( strpos($ancor, $base_url) === false ) {
    	    
    	    return false;
    	
    	}*/
    	
    	
    	//insert only doesn't exist yet
    	
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
    
    
    public function crawled($ancorID, $siteID, $notfound = false)
    {
    
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
           
}