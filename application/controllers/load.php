<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Load extends CI_Controller {
    public function __construct() {
        $this->load->model('datamod');
        header('Content-Type: text/json');
    }
    
    public function class($method) {
        switch ($method) {
            case 'byId':
                $params['data'] = $this->datamod->classById($_POST['id']);
                $this->load->view('json', $params);
                break;
            case 'byInfo':
                $params['data'] = $this->datamod->classByInfo($_POST);
                $this->load->view('json', $params);
                break;
        }
    }
    
    public function person() {
        $params['data'] = $this->datamod->person($_POST['name']);
        $this->load->view('json', $params);
    }
}