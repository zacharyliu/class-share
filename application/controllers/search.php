<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {
    
    public function index() {
        $this->load->model('datamod');
        isset($_POST['q']) ? $q = $_POST['q'] : $q = $_GET['q'];
        $data = $this->datamod->search($q);
        $this->load->view('json', array('data' => $data));
    }
    
}