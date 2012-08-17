<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {
    
    public function index() {
        $this->load->model('datamod');
        $q = $_POST['q'];
        $data = $this->datamod->search($q);
        $this->load->view('json', array('data' => $data));
    }
    
}