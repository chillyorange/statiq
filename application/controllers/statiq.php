<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Statiq extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->load->helper(array('form', 'url', 'crawl'));
		$this->load->library('form_validation');
		$this->load->library('session');
		
		$this->load->library('crawler');
		
		
		$this->load->database();
		
				
	}

	public function index()
	{
	
		if( !isset($_POST['domain']) || $_POST['domain'] == '' ) {
		
			$this->session->set_flashdata('error', 'Please make sure you enter a domain name');
			$this->session->set_flashdata('domain', $_POST['domain']);
			
			redirect("/home", "refresh");
		
		}
		
		//remove "http://", "https://" and trailing slashes
				
		$url = str_replace("http://", "", $_POST['domain']);
		//$domain = trim( $domain, "https://" );
		$url = rtrim( $url, "/" );
		
		
		//sub folder action?
		
		
		
		$temp = explode("/", $url);
		
		if( count($temp) > 1 ) {
		
			$domain = $temp[0];
		
		} else {
		
			$domain = $url;
		
		}
								
		if( !is_valid_domain_name( $domain ) ) {
		
			$this->session->set_flashdata('error', 'Please make sure you enter a valid domain name and the domain name has a valid IP address asigned to it.');
			$this->session->set_flashdata('domain', $_POST['domain']);
			
			redirect("/home", "refresh");
		
		}
		
		//moving on, $domain now contains a crawlable domain name
		
		//counter to enforce a maximum of crawled URLs
		$this->session->set_userdata('pageCounter', 1);		
		
		$siteID = $this->crawlmodel->createSite($url);
						
		$this->data['siteID'] = $siteID;
		
		$this->data['page'] = "statiq";
		
		$this->load->view('statiq', $this->data);
		
	}
	
	
	public function startCrawl($siteID)
	{
	
		$this->crawler->startCrawl( $siteID );
	
	}
	
	public function test()
	{
	
		$cssFile = file_get_contents( "http://getstatiq.com/sites/chillyorange.com/wp-content/themes/aspect/style.css?ver=1.0" );
		
		preg_match_all('~\bbackground(-image)?\s*:.*url(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $cssFile, $matches);
			$images = $matches['image'];
		
			//echo "<b>".$stylesheetUrl."</b></br>";
		
			foreach( $images as $img ) {
			
				echo $img."<br>";
			
			}
	
	}
	
}

/* End of file statiq.php */
/* Location: ./application/controllers/statiq.php */