<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->load->library('session');
		
	}

	public function index()
	{
			
		$this->data['page'] = "home";
		
		$this->load->view('home', $this->data);
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */