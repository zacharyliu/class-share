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
    
    public function personByName($name) {
        $this->db->where('name', $name);
        $query = $this->db->get('people');
        return $query->row();
    }
    
    public function personById($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('people');
        return $query->row();
    }
    
    public function personEnrollment($id) {
        $this->db->where('user_id', $id);
        $this->db->from('enrollment');
        $this->db->join('classes', 'classes.id = enrollment.class_id');
        $this->db->select(array('classes.id', 'name', 'day', 'period', 'teacher'));
        $this->db->order_by('day', 'asc');
        $this->db->order_by('period', 'asc');
        $query = $this->db->get();
        return $query->result();
    }
    
    public function classEnrollment($id) {
        $this->db->where('class_id', $id);
        $this->db->from('enrollment');
        $this->db->join('people', 'people.id = enrollment.user_id', 'inner');
        $this->db->select(array('people.id', 'people.name', 'people.email'));
        $query = $this->db->get();
        
        $result = $query->result();
        
        return $result;
    }
    
    public function relatedClasses($info) {
        $this->db->from('classes');
        $this->db->where(array('id !=' => $info->id, 'day' => $info->day, 'period' => $info->period, 'teacher' => $info->teacher));
        $query = $this->db->get();
        $related_names = $query->result();
        
        $this->db->from('classes');
        $this->db->where(array('id !=' => $info->id, 'name' => $info->name, 'teacher' => $info->teacher));
        $query = $this->db->get();
        $related_periods = $query->result();
        
        $this->db->from('classes');
        $this->db->where(array('id !=' => $info->id, 'name' => $info->name, 'day' => $info->day, 'period' => $info->period));
        $query = $this->db->get();
        $related_teachers = $query->result();
        
        $related = array('names' => $related_names, 'periods' => $related_periods, 'teachers' => $related_teachers);
        
        return $related;
    }
    
    public function addPerson($name, $email) {
        $data = array('name' => $name, 'email' => $email);
        // Check if the user is already in the database
        $this->db->where($data);
        $query = $this->db->get('people');
        if ($query->num_rows() == 0) {
            // Not yet in the database, insert them
            $this->db->insert('people', $data);
            return true;
        } else {
            return false;
        }
    }
    
    public function getPersonId($name, $email) {
        $data = array('name' => $name, 'email' => $email);
        $this->db->where($data);
        $query = $this->db->get('people');
        if ($query->num_rows() == 0) {
            return false;
        } else {
            $row = $query->row();
            return $row->id;
        }
    }
    
    public function import($user_id, $data) {        
        // Add the classes to the database if they do not exist, then update the enrollment table
        foreach ($data as $class) {
            $this->db->where($class);
            $query = $this->db->get('classes');
            if ($query->num_rows() == 0) {
                $this->db->insert('classes', $class);
                $class_id = $this->db->insert_id();
            } else {
                $class_id = $query->row()->id;
            }
            $this->db->where(array('user_id' => $user_id, 'class_id' => $class_id));
            $query = $this->db->get('enrollment');
            if ($query->num_rows() == 0) {
                $this->db->insert('enrollment', array('user_id' => $user_id, 'class_id' => $class_id));
            }
        }
    }
    
    public function search($q) {
        $q = $this->db->escape_like_str($q);
        $user_id = $this->db->escape($this->session->userdata('id'));
        $query = $this->db->query("SELECT * FROM (SELECT id, CONCAT(name, ', ', teacher) AS body, 'class' AS type FROM classes UNION ALL SELECT id, name AS body, 'person' AS type FROM people WHERE id != $user_id) AS search WHERE body LIKE '%$q%' GROUP BY body");
        $result = $query->result();
        $result_chunk = array_chunk($result, 5);
        if (count($result_chunk) > 0) {
            return $result_chunk[0];
        } else {
            return array();
        }
    }
    
}
