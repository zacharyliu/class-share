<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Datamod extends CI_Model {
    
    public function classById($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('classes');
        return $query->row();
    }
    
    public function classByInfo($info) {
        $this->db->where($info);
        $query = $this->db->get('classes');
        return $query->row();
    }
    
    public function person($name) {
        $this->db->where('name', $name);
        $query = $this->db->get('people');
        return $query->row();
    }
    
}