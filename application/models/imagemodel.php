<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Imagemodel extends CI_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        
    }
    
    
    public function insert($image, $base_url, $siteID)
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
    
    
    public function crawled($imageID, $siteID, $notfound = false)
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
    
}