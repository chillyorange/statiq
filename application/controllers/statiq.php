<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Statiq extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('session');
		
		$this->load->model('sitemodel');
		
		$this->load->helper('htmldom');
		$this->load->model('ancormodel');
		$this->load->model('scriptmodel');
		$this->load->model('stylesheetmodel');
		$this->load->model('filemodel');
		$this->load->model('imagemodel');
		$this->load->database();
		
		$this->load->helper('validdomain');
				
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
		
		$siteID = $this->sitemodel->create($url);
						
		$this->data['siteID'] = $siteID;
		
		$this->data['page'] = "statiq";
		
		$this->load->view('statiq', $this->data);
		
	}
	
	
	public function startCrawl($siteID)
	{
	
		$siteDetails = $this->sitemodel->getSiteDetails($siteID);
		
		$fullUrl = $siteDetails->site_url;
		
		$domain = $siteDetails->site_domain;
						
		$restingPlace = "getstatiq.com/sites/".$fullUrl;		
		
		//create the parent directory
		
		if (!is_dir('sites/'.$fullUrl)) {
		
		    mkdir('./sites/'.$fullUrl, 0777, TRUE);
		
		}
		
		
		//insert the base_url as our first page to crawl
		$this->ancormodel->insert("http://".$siteDetails->site_url."/", $fullUrl, $siteID);
		
		$this->crawll($siteDetails->site_url, $restingPlace, $siteID, $domain);
	
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
	
	
	public function crawll($base_url, $restingPlace, $siteID, $domain)
	{
	
		error_reporting(0);
		
		//grab uncrawled URLs
		$query = $this->db->from('ancors')->where('ancor_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
						
		while( $query->num_rows() > 0 ) {//do we have any?
		
			//grab first uncrawled URL
			$res = $query->result();
			
			$url = $res[0]->ancor_href;
			
						
			// Create DOM from URL or file
			
			$html = file_get_html($url);
			
			if( $html ) {			
			
				//retrieve the file name
				$fileName = $this->filemodel->getFileName($url);
			
			
				//create the folder structure for this URL
				$path = $this->filemodel->createDirForHtml($url, $fileName);
						
				//get all links
			
				$links = $html->find('a');
			
				foreach( $links as $link ) {
			
					if( $link->href != '#' ) {
										
						//relative or absolute URL?
						
						$aLink = $link->href;
						
						if( $aLink[0] == '/' ) {
						
							//relative link, add the base_url
							
							$aLink = "http://".$domain.$aLink;
						
						}
					
						$linkInsertResult = $this->ancormodel->insert($aLink, $base_url, $siteID);
																								
						//make sure we don't cross the maximum number of allowed pages
						
						if( $this->session->userdata('pageCounter') >= 600 ) {
						
							//delete data for this site
							$this->sitemodel->delete($siteID, $base_url);
						
							$return = array();
							$return['content'] = $this->load->view('partials/toomany', $this->data, true);
						
							die( json_encode($return) );
						 
						} elseif( $linkInsertResult ) {
						
							$n = $this->session->userdata('pageCounter')+1;
						
							$this->session->set_userdata('pageCounter', $n);
						
						}
						
					
					
						//alter the href attribute, point to the new path
						
						//check if we have the domain name at the beginning of the href
						
						if ( 0 === strpos($link->href, "http://".$base_url) ) {
						   
						   //absolute URL with domain in front
						   $link->href = str_replace($base_url, $restingPlace, $link->href);
						   
						} else {
							
							//absolute URL without domain
							$link->href = "http://getstatiq.com/sites/".$domain.$link->href;
						
						}
						
						
						//some links to the home page might be without the trailing /
						
						if( $link->href == rtrim($base_url, '/') ) {
						
							$link->href = rtrim($restingPlace, '/');
						
						}
					
					
					}
			
				}
			
			
				//get all external js files
			
				$scripts = $html->find('script');
			
				foreach( $scripts as $script ) {
										
					$this->scriptmodel->insert($script->src, $base_url, $siteID);
				
				
					if( $script->src != '' ) {
				
						//alter the src attribute, point to the new path
				
						$script->src = str_replace($base_url, $restingPlace, $script->src);
				
					}
			
				}
			
			
				//get all stylesheets
			
				$stylesheets = $html->find('link[type=text/css]');
			
				foreach( $stylesheets as $stylesheet ) {
			
					$this->stylesheetmodel->insert($stylesheet->href, $base_url, $siteID);
				
				
					//alter the src attribute, point to the new path
				
					$stylesheet->href = str_replace($base_url, $restingPlace, $stylesheet->href);
			
				}
			
			
			
				//get all images from markup
			
				$images = $html->find('img');
			
				foreach( $images as $image ) {
			
					$this->imagemodel->insert($image->src, $base_url, $siteID);
				
				
					//alter the src attribute, point to the new path
				
					$image->src = str_replace($base_url, $restingPlace, $image->src);
			
				}
						
			
				//store the file
				$this->filemodel->store($html->save(), $path, $fileName);
			
				//set URL as crawled
				$this->ancormodel->crawled($res[0]->ancor_id, $siteID);
			
				//dump to screen
				//flush();
			
			} else {
			
				//404!
				//set URL as crawled
				$this->ancormodel->crawled($res[0]->ancor_id, $siteID, true);
			
			}
			
			
			//recursion baby!
			$query = $this->db->from('ancors')->where('ancor_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled scripts
		
		$query = $this->db->from('scripts')->where('script_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
		
		while( $query->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$res = $query->result();
			
			$scriptUrl = $res[0]->script_url;
			
			
			$jsFile = file_get_contents( $scriptUrl );
			
			
			if( $jsFile ) {
			
				//retrieve the file name
				$fileName = $this->filemodel->getFileName($scriptUrl);
			
				//create the folder structure for this URL
				$path = $this->filemodel->createDirForJs($scriptUrl, $fileName);
									
				//store the file
				$this->filemodel->store($jsFile, $path."/", $fileName);
			
			
				//set script as crawled
				$this->scriptmodel->crawled($res[0]->script_id, $siteID);
			
			} else {
			
				//4041
				$this->scriptmodel->crawled($res[0]->script_id, $siteID, true);
			
			}
			
			//recursion baby!
			$query = $this->db->from('scripts')->where('script_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled stylesheets
		
		$q = $this->db->from('stylesheets')->where('stylesheet_crawled', '0')->where('site_id', $siteID)->get();
				
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$stylesheetUrl = $r[0]->stylesheet_url;
			
			
			$cssFile = file_get_contents( $stylesheetUrl );
			
			
			if( $cssFile ) {
			
				//create the folder structure for this URL
				
				//retrieve the file name
				$fileName = $this->filemodel->getFileName( $stylesheetUrl );
			
				$path = $this->filemodel->createDirForCSS( $stylesheetUrl, $fileName );
			
						
				//store the file
				$this->filemodel->store($cssFile, $path."/", $fileName);
			
			
				//we'll also need all images from these stylesheets
			
				preg_match_all('~\bbackground(-image)?\s*:.*url(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $cssFile, $matches);
				$images = $matches['image'];
			
				//echo "<b>".$stylesheetUrl."</b></br>";
			
				foreach( $images as $img ) {
								
					if( strpos($img, $base_url) !== false ) {
					
						//img has absolute path, nothing else to do but add for crawling
					
						$this->imagemodel->insert($img, $base_url, $siteID);
				
					} else {
				
						//image has relative path, ouch! change url and add for crawling
					
						$levels = substr_count($img, "../");
										
						$imgExploded = explode('/', $stylesheetUrl);
					
						//popup off the file name
					
						array_pop( $imgExploded );
					
						for( $x=1; $x <= $levels; $x++ ) {
										
							array_pop( $imgExploded );
					
						}
					
					
						$newUrl = implode("/", $imgExploded)."/".str_replace("../", "", $img);
					
						//echo $newUrl;
					
						$this->imagemodel->insert($newUrl, $base_url, $siteID);
				
					}
			
				}
				
				
				//we'll also need @font-face fonts from the stylesheets
				
				preg_match_all("/\([\.'a-z\/-]*\.(eot|woff|ttf|svg){1}/", $cssFile, $output_array);
				
				foreach( $output_array[0] as $file ) {
				
					//remove crap from the front
					
					$file = ltrim($file, "('");
					$file = ltrim($file, '("');
					$file = ltrim($file, "(");
					
					if( strpos($file, $base_url) !== false ) {
						
						//img has absolute path, nothing else to do but add for crawling
						
						$this->filemodel->insert($file, $base_url, $siteID);
					
					} else {
				
						//image has relative path, ouch! change url and add for crawling
						
						$levels = substr_count($file, "../");
											
						$fileExploded = explode('/', $stylesheetUrl);
						
						//popup off the file name
						
						array_pop( $fileExploded );
						
						for( $x=1; $x <= $levels; $x++ ) {
											
							array_pop( $fileExploded );
						
						}
						
						
						$newUrl = implode("/", $fileExploded)."/".str_replace("../", "", $file);
						
						//echo $newUrl;
						
						$this->filemodel->insert($newUrl, $base_url, $siteID);
					
					}
				
					
				
				}
			
			
				//set script as crawled
				$this->stylesheetmodel->crawled($r[0]->stylesheet_id, $siteID);
			
			} else {
			
				//404
				$this->stylesheetmodel->crawled($r[0]->stylesheet_id, $siteID, true);
			
			}
			
			
			//recursion baby!
			$q = $this->db->from('stylesheets')->where('stylesheet_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled images
		
		$q = $this->db->from('images')->where('image_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$imageSrc = $r[0]->image_src;
			
			
			$imageFile = file_get_contents( $imageSrc );
			
			
			if( $imageFile ) {
			
				//retrieve the file name
				$fileName = $this->filemodel->getFileName( $imageSrc );
			
				//create the folder structure for this URL
				$path = $this->filemodel->createDirForImage( $imageSrc, $fileName );
							
				//store the file
				$this->filemodel->store($imageFile, $path."/", $fileName);
			
			
				//set script as crawled
				$this->imagemodel->crawled($r[0]->image_id, $siteID);
			
			} else {
			
				//404
				$this->imagemodel->crawled($r[0]->image_id, $siteID, true);
			
			}
			
			//recursion baby!
			$q = $this->db->from('images')->where('image_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled files
		
		$q = $this->db->from('files')->where('file_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$fileSUrl = $r[0]->file_url;
			
			
			$file = file_get_contents( $fileSUrl );
			
			
			if( $file ) {
			
				//retrieve the file name
				$fileName = $this->filemodel->getFileName( $fileSUrl );
			
				//create the folder structure for this URL
				$path = $this->filemodel->createDirForFile( $fileSUrl, $fileName );
			
						
				//store the file
				$this->filemodel->store($file, $path."/", $fileName);
			
			
				//set script as crawled
				$this->filemodel->crawled($r[0]->file_id, $siteID);
			
			} else {
			
				//404
				$this->filemodel->crawled($r[0]->file_id, $siteID, true);
			
			}
			
			//recursion baby!
			$q = $this->db->from('files')->where('file_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		$this->data['url'] = "http://getstatiq.com/sites/".$base_url;
		
		$return['content'] = $this->load->view('partials/crawldone', $this->data, true);
		
		echo json_encode( $return );
		
	}
	
}

/* End of file statiq.php */
/* Location: ./application/controllers/statiq.php */