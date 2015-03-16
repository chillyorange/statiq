<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Crawler {
	
	public function __construct() {
		
		$CI =& get_instance();
		
		$CI->load->model('crawlmodel');
		$CI->load->helper('crawl');
		$CI->load->helper('htmldom');
		
	}

	public function startCrawl($siteID)
	{
		$CI =& get_instance();
	
		$siteDetails = $CI->crawlmodel->getSiteDetails($siteID);
		
		$fullUrl = $siteDetails->site_url;
		
		$domain = $siteDetails->site_domain;
						
		$restingPlace = "stattic/sites/".$fullUrl;		
		
		//create the parent directory
		
		if (!is_dir('sites/'.$fullUrl)) {
		
		    mkdir('./sites/'.$fullUrl, 0777, TRUE);
		
		}
		
		
		//insert the base_url as our first page to crawl
		$CI->crawlmodel->insertAncor("http://".$siteDetails->site_url."/", $fullUrl, $siteID);
		
		$this->crawll($siteDetails->site_url, $restingPlace, $siteID, $domain);
	
	}
	
	public function crawll($base_url, $restingPlace, $siteID, $domain)
	{
		$CI =& get_instance();
		
		error_reporting(0);
		
		//grab uncrawled URLs
		$query = $CI->db->from('ancors')->where('ancor_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
						
		while( $query->num_rows() > 0 ) {//do we have any?
		//while( $counter == 0 ) {//do we have any?
		
			//grab first uncrawled URL
			$res = $query->result();
			
			$url = $res[0]->ancor_href;
			
						
			// Create DOM from URL or file
			
			$html = file_get_html($url);
			
			if( $html ) {			
			
				//retrieve the file name
				$fileName = $CI->crawlmodel->getFileName($url);
			
			
				//create the folder structure for this URL
				$path = $CI->crawlmodel->createDirForHtml($url, $fileName);
						
				//get all links
			
				$links = $html->find('a');
			
				foreach( $links as $link ) {
			
					if( $link->href != '#' ) {
						
						$urlToInsert = $CI->crawlmodel->prepUrl($link->href, $url, $domain);
						
						if( $urlToInsert ) {
							$CI->crawlmodel->insertAncor($urlToInsert, $base_url, $siteID);
						}
																								
						//make sure we don't cross the maximum number of allowed pages
						
						if( $CI->session->userdata('pageCounter') >= 600 ) {
						
							//delete data for this site
							$CI->crawlmodel->deleteSite($siteID, $base_url);
						
							$return = array();
							$return['content'] = $CI->load->view('partials/toomany', $CI->data, true);
						
							die( json_encode($return) );
						 
						} elseif( $linkInsertResult ) {
						
							$n = $CI->session->userdata('pageCounter')+1;
						
							$CI->session->set_userdata('pageCounter', $n);
						
						}
						
					
					
						//alter the href attribute, point to the new path
						
						//check if we have the domain name at the beginning of the href
						
						if ( 0 === strpos($link->href, "http://".$base_url) || 0 === strpos($link->href, "https://".$base_url) ) {
						   
						   //absolute URL with domain in front
						   //$link->href = str_replace($base_url, $restingPlace, $link->href);
						   
						   $link->href = getRelativePath($url, $link->href);
						   
						   
						} else {
							
							//leave external domains in tact
							if ( 0 === strpos($link->href, "http://") || 0 === strpos($link->href, "https://") ) {
								
								
							} else {
							
								//absolute URL without domain
								$link->href = "http://getstatiq.com/sites/".$domain.$link->href;
							
							}
						
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
					
					$urlToInsert = $CI->crawlmodel->prepUrl($script->src, $url, $domain);
					
					if( $urlToInsert ) {
						$CI->crawlmodel->insertScript($urlToInsert, $base_url, $siteID);
					}
									
				
					if( $script->src != '' ) {
				
						//alter the src attribute, point to the new path
				
						$script->src = str_replace($base_url, $restingPlace, $script->src);
				
					}
			
				}
			
			
				//get all stylesheets
			
				$stylesheets = $html->find('link[rel=stylesheet]');
			
				foreach( $stylesheets as $stylesheet ) {
					
					$urlToInsert = $CI->crawlmodel->prepUrl($stylesheet->href, $url, $domain);
					
					if( $urlToInsert ) {
						$CI->crawlmodel->insertStylesheet($urlToInsert, $base_url, $siteID);
					}
									
				
					//alter the src attribute, point to the new path
				
					//$stylesheet->href = str_replace($base_url, $restingPlace, $stylesheet->href);
					
					if ( 0 === strpos($stylesheet->href, "http://".$base_url) || 0 === strpos($stylesheet->href, "https://".$base_url) ) {
						
						$stylesheet->href = getRelativePath($url, $stylesheet->href);
					
					} else {
						
						//leave external domains in tact
						if ( 0 === strpos($stylesheet->href, "http://") || 0 === strpos($stylesheet->href, "https://") ) {
							
							
						} else {
						
							//absolute URL without domain
							$stylesheet->href = "http://getstatiq.com/sites/".$domain.$stylesheet->href;
						
						}
						
					}
			
				}
			
			
			
				//get all images from markup
			
				$images = $html->find('img');
			
				foreach( $images as $image ) {
					
					$urlToInsert = $CI->crawlmodel->prepUrl($image->src, $url, $domain);
					
					if( $urlToInsert ) {
						$CI->crawlmodel->insertImage($urlToInsert, $base_url, $siteID);
					}
							
				
					//alter the src attribute, point to the new path
				
					$image->src = str_replace($base_url, $restingPlace, $image->src);
			
				}
						
			
				//store the file
				$theHTML = $html->save();
				
				$CI->crawlmodel->store($theHTML, $path, $fileName, "HTML");
			
				//set URL as crawled
				$CI->crawlmodel->ancorCrawled($res[0]->ancor_id, $siteID);
			
				//dump to screen
				//flush();
			
			} else {
			
				//404!
				//set URL as crawled
				$CI->crawlmodel->ancorCrawled($res[0]->ancor_id, $siteID, true);
			
			}
			
			
			//recursion baby!
			$query = $CI->db->from('ancors')->where('ancor_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		//grab uncrawled scripts
		
		$query = $CI->db->from('scripts')->where('script_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
		
		while( $query->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$res = $query->result();
			
			$scriptUrl = $res[0]->script_url;
			
			
			$jsFile = file_get_contents( $scriptUrl );
			
			if( $jsFile ) {
				
				//retrieve the file name
				$fileName = $CI->crawlmodel->getFileName($scriptUrl);
			
				//create the folder structure for this URL
				$path = $CI->crawlmodel->createDirForJs($scriptUrl, $fileName);
									
				//store the file
				$CI->crawlmodel->store($jsFile, $path."/", $fileName);
			
			
				//set script as crawled
				$CI->crawlmodel->scriptCrawled($res[0]->script_id, $siteID);
			
			} else {
			
				//4041
				$CI->crawlmodel->scriptCrawled($res[0]->script_id, $siteID, true);
			
			}
			
			
			//recursion baby!
			$query = $CI->db->from('scripts')->where('script_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled stylesheets
		
		$q = $CI->db->from('stylesheets')->where('stylesheet_crawled', '0')->where('site_id', $siteID)->get();
				
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$stylesheetUrl = $r[0]->stylesheet_url;
			
			
			$cssFile = file_get_contents( $stylesheetUrl );
			
			
			if( $cssFile ) {
			
				//create the folder structure for this URL
				
				//retrieve the file name
				$fileName = $CI->crawlmodel->getFileName( $stylesheetUrl );
			
				$path = $CI->crawlmodel->createDirForCSS( $stylesheetUrl, $fileName );
			
						
				//store the file
				$CI->crawlmodel->store($cssFile, $path."/", $fileName);
			
			
				//we'll also need all images from these stylesheets
			
				preg_match_all('~\bbackground(-image)?\s*:.*url(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $cssFile, $matches);
				$images = $matches['image'];
			
				//echo "<b>".$stylesheetUrl."</b></br>";
			
				foreach( $images as $img ) {
								
					if( strpos($img, $base_url) !== false ) {
					
						//img has absolute path, nothing else to do but add for crawling
					
						$CI->crawlmodel->insertImage($img, $base_url, $siteID);
				
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
					
						$CI->crawlmodel->insertImage($newUrl, $base_url, $siteID);
				
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
						
						$CI->crawlmodel->insertFile($file, $base_url, $siteID);
					
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
						
						$CI->crawlmodel->insertFile($newUrl, $base_url, $siteID);
					
					}
				
					
				
				}
			
			
				//set script as crawled
				$CI->crawlmodel->stylesheetCrawled($r[0]->stylesheet_id, $siteID);
			
			} else {
			
				//404
				$CI->crawlmodel->stylesheetCrawled($r[0]->stylesheet_id, $siteID, true);
			
			}
			
			
			//recursion baby!
			$q = $CI->db->from('stylesheets')->where('stylesheet_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled images
		
		$q = $CI->db->from('images')->where('image_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$imageSrc = $r[0]->image_src;
			
			
			$imageFile = file_get_contents( $imageSrc );
			
			
			if( $imageFile ) {
			
				//retrieve the file name
				$fileName = $CI->crawlmodel->getFileName( $imageSrc );
			
				//create the folder structure for this URL
				$path = $CI->crawlmodel->createDirForImage( $imageSrc, $fileName );
							
				//store the file
				$CI->crawlmodel->store($imageFile, $path."/", $fileName);
			
			
				//set script as crawled
				$CI->crawlmodel->imageCrawled($r[0]->image_id, $siteID);
			
			} else {
			
				//404
				$CI->crawlmodel->imageCrawled($r[0]->image_id, $siteID, true);
			
			}
			
			//recursion baby!
			$q = $CI->db->from('images')->where('image_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
		
		
		
		//grab uncrawled files
		$q = $CI->db->from('files')->where('file_crawled', '0')->where('site_id', $siteID)->get();
		
		$counter = 0;
		
		while( $q->num_rows() > 0 ) {
		
			//grab first uncrawled URL
			$r = $q->result();
												
			$fileSUrl = $r[0]->file_url;
			
			
			$file = file_get_contents( $fileSUrl );
			
			
			if( $file ) {
			
				//retrieve the file name
				$fileName = $CI->crawlmodel->getFileName( $fileSUrl );
			
				//create the folder structure for this URL
				$path = $CI->crawlmodel->createDirForFile( $fileSUrl, $fileName );
			
						
				//store the file
				$CI->crawlmodel->store($file, $path."/", $fileName);
			
			
				//set script as crawled
				$CI->crawlmodel->fileCrawled($r[0]->file_id, $siteID);
			
			} else {
			
				//404
				$CI->crawlmodel->fileCrawled($r[0]->file_id, $siteID, true);
			
			}
			
			//recursion baby!
			$q = $CI->db->from('files')->where('file_crawled', '0')->where('site_id', $siteID)->get();
			
			$counter++;
		
		}
	
		
		$CI->data['url'] = "http://getstatiq.com/sites/".$base_url;
		
		$return['content'] = $CI->load->view('partials/crawldone', $this->data, true);
		
		echo json_encode( $return );
		
	}
}

/* End of file Crawler.php */