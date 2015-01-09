<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sitemodel extends CI_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        
        $this->load->helper('directory');
        
    }
    
    
    public function create($url)
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
    
    
    public function delete($siteID, $base_url)
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
           
}