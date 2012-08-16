<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Load extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('datamod');
    }
    
    public function classData($method) {
        switch ($method) {
            case 'byId':
                $data = $this->datamod->classById($_POST['id']);
                break;
            case 'byInfo':
                $data = $this->datamod->classByInfo($_POST);
                break;
            default:
                throw "No method specified for class()";
        }
        
        $related = $this->datamod->relatedClasses($data);
        $people = $this->datamod->classEnrollment($data->id);
        
        $data->related = $related;
        $data->people = $people;
        
        $this->load->view('json', array('data' => $data));
    }
    
    public function person($method) {
        switch ($method) {
            case 'byName':
                $data = $this->datamod->personByName($_POST['name']);
                break;
            case 'byId':
                $data = $this->datamod->personById($_POST['id']);
                break;
            default:
                throw "No method specified for person()";
        }
        
        $data->classes = $this->datamod->personEnrollment($data->id);
        
        $this->load->view('json', array('data' => $data));
    }
    
    public function me() {
        $_POST['id'] = 1;
        $this->person('byId');
    }
}