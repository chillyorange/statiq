<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crawl extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->load->helper('htmldom');
		$this->load->model('ancormodel');
		$this->load->model('scriptmodel');
		$this->load->model('stylesheetmodel');
		$this->load->model('filemodel');
		$this->load->model('imagemodel');
		$this->load->database();
				
	}
	
	public function index()
	{
		
		$base_url = 'http://www.roadtripstravel.com/';
		
		$restingPlace = "http://getstatiq.com/sites/www.roadtripstravel.com/";
		
		$url = $base_url;
		
		//create the parent directory
		$dirName = str_replace("http://", '', $base_url);
		
		if (!is_dir('sites/'.$dirName)) {
		
		    mkdir('./sites/'.$dirName, 0777, TRUE);
		
		}
				
		//insert the base_url as our first page to crawl
		$this->ancormodel->insert($url, $base_url);
		
		$this->crawll($base_url, $restingPlace);
		
	}
	
	
	public function crawll($base_url, $restingPlace)
	{
		
		//grab uncrawled URLs
		$query = $this->db->from('ancors')->where('ancor_crawled', '0')->get();
		
		$counter = 0;
						
		while( $query->num_rows() > 0 ) {//do we have any?
		
			//grab first uncrawled URL
			$res = $query->result();
			
			$url = $res[0]->ancor_href;
			
						
			// Create DOM from URL or file
			$html = file_get_html($url);
			
			
			//retrieve the file name
			$fileName = $this->filemodel->getFileName($url);
			
			
			//create the folder structure for this URL
			$path = $this->filemodel->createDirForHtml($url, $base_url);
			
			
			//get all links
			
			$links = $html->find('a');
			
			foreach( $links as $link ) {
			
				if( $link->href != '#' ) {
				
					//echo $link->href."<br>";
					
					
						$this->ancormodel->insert($link->href, $base_url);
					
					
					//alter the href attribute, point to the new path
					
					$link->href = str_replace($base_url, $restingPlace, $link->href);
					
					
				}
			
			}
			
			
			//get all external js files
			
			$scripts = $html->find('script');
			
			foreach( $scripts as $script ) {
						
				$this->scriptmodel->insert($script->src, $base_url);
				
				
				if( $script->src != '' ) {
				
					//alter the src attribute, point to the new path
				
					$script->src = str_replace($base_url, $restingPlace, $script->src);
				
				}
			
			}
			
			
			//get all stylesheets
			
			$stylesheets = $html->find('link[type=text/css]');
			
			foreach( $stylesheets as $stylesheet ) {
			
				$this->stylesheetmodel->insert($stylesheet->href, $base_url);
				
				
				//alter the src attribute, point to the new path
				
				$stylesheet->href = str_replace($base_url, $restingPlace, $stylesheet->href);
			
			}
			
			
			
			//get all images from markup
			
			$images = $html->find('img');
			
			foreach( $images as $image ) {
			
				$this->imagemodel->insert($image->src, $base_url);
				
				
				//alter the src attribute, point to the new path
				
				$image->src = str_replace($base_url, $restingPlace, $image->src);
			
			}
						
			
			//store the file
			$this->filemodel->store($html->save(), $path, $fileName);
			
			//set URL as crawled
			$this->ancormodel->crawled($res[0]->ancor_id);
			
			//dump to screen
			flush();
			
			//recursion baby!
			$query = $this->db->from('ancors')->where('ancor_crawled', '0')->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled scripts
		
		$query = $this->db->from('scripts')->where('script_crawled', '0')->get();
		
		$counter = 0;
		
		while( $query->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$res = $query->result();
			
			$scriptUrl = $res[0]->script_url;
			
			
			//create the folder structure for this URL
			$path = $this->filemodel->createDirForJs($scriptUrl, $base_url);
			
					
			$jsFile = file_get_contents( $scriptUrl );
			
			
			//retrieve the file name
			$fileName = $this->filemodel->getFileName($scriptUrl);
						
			//store the file
			$this->filemodel->store($jsFile, $path."/", $fileName);
			
			
			//set script as crawled
			$this->scriptmodel->crawled($res[0]->script_id);
			
			
			//recursion baby!
			$query = $this->db->from('scripts')->where('script_crawled', '0')->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled stylesheets
		
		$q = $this->db->from('stylesheets')->where('stylesheet_crawled', '0')->get();
				
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$stylesheetUrl = $r[0]->stylesheet_url;
			
			
			//create the folder structure for this URL
			$path = $this->filemodel->createDirForCSS( $stylesheetUrl, $base_url );
			
			$cssFile = file_get_contents( $stylesheetUrl );
			
			
			//retrieve the file name
			$fileName = $this->filemodel->getFileName( $stylesheetUrl );
						
			//store the file
			$this->filemodel->store($cssFile, $path."/", $fileName);
			
			
			//we'll also need all images from these stylesheets
			
			preg_match_all('~\bbackground(-image)?\s*:\s*url(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $cssFile, $matches);
			$images = $matches['image'];
			
			echo "<b>".$stylesheetUrl."</b></br>";
			
			foreach( $images as $img ) {
								
				if( strpos($img, $base_url) !== false ) {
					
					//img has absolute path, nothing else to do but add for crawling
					
					$this->imagemodel->insert($img, $base_url);
				
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
					
					$this->imagemodel->insert($newUrl, $base_url);
				
				}
			
			}
			
			
			//set script as crawled
			$this->stylesheetmodel->crawled($r[0]->stylesheet_id);
			
			
			//recursion baby!
			$q = $this->db->from('stylesheets')->where('stylesheet_crawled', '0')->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled images
		
		$q = $this->db->from('images')->where('image_crawled', '0')->get('');
		
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$imageSrc = $r[0]->image_src;
			
			
			//create the folder structure for this URL
			$path = $this->filemodel->createDirForImage( $imageSrc, $base_url );
			
			$imageFile = file_get_contents( $imageSrc );
			
			
			//retrieve the file name
			$fileName = $this->filemodel->getFileName( $imageSrc );
						
			//store the file
			$this->filemodel->store($imageFile, $path."/", $fileName);
			
			
			//set script as crawled
			$this->imagemodel->crawled($r[0]->image_id);
			
			
			//recursion baby!
			$q = $this->db->from('images')->where('image_crawled', '0')->get();
			
			$counter++;
		
		}
		
	}
	
}

/* End of file crawl.php */
/* Location: ./application/controllers/crawl.php */