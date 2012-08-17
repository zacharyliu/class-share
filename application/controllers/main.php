<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
    
    public function index() {
        if ($this->session->userdata('auth') == 'true') {
            $this->load->view('main');
        } else {
            $this->load->view('login');
        }
    }
    
}