<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stylesheetmodel extends CI_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        
    }
    
    
    public function insert($stylesheet, $base_url, $siteID)
    {	
    
    	//remove ?v=whatever stuff
    	
    	$temp = explode("?", $stylesheet);
    	
    	if( count($temp) > 1 ) {
    		
    		//has it
    		
    		$stylesheet = $temp[0];
    	
    	}
    	
    	
    	//make sure the href contains the URL
    	if( strpos($stylesheet, $base_url) === false ) {
    	    
    	    return false;
    	
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
    
    
    public function crawled($stylesheetID, $siteID, $notfound = false)
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
    
}