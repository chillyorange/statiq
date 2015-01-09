<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Scriptmodel extends CI_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        
    }
    
    
    public function insert($script, $base_url, $siteID)
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
    
    
    public function crawled($scriptID, $siteID, $notfound = false)
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
    
}