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
        $query = $this->db->get();
        return $query->result();
    }
    
    public function classEnrollment($id) {
        $this->db->where('class_id', $id);
        $this->db->from('enrollment');
        $this->db->join('people', 'people.id = enrollment.user_id', 'inner');
        $query = $this->db->get();
        
        $result = $query->result();
        
        $people = array();
        foreach ($result as $item) {
            array_push($people, $item->name);
        }
        
        return $people;
    }
    
    public function relatedClasses($info) {
        $this->db->from('classes');
        $this->db->where(array('id !=' => $info->id, 'day' => $info->day, 'period' => $info->period, 'teacher' => $info->teacher));
        $this->db->or_where(array('id !=' => $info->id, 'name' => $info->name, 'teacher' => $info->teacher));
        $this->db->or_where(array('id !=' => $info->id, 'name' => $info->name, 'day' => $info->day, 'period' => $info->period));
        $query = $this->db->get();
        
        $result = $query->result();
        
        $related_names = array();
        $related_periods = array();
        $related_teachers = array();
        
        foreach ($result as $item) {
            if ($item->name != $info->name) {
                array_push($related_names, array('name' => $item->name, 'id' => $item->id));
            } else if ($item->day != $info->day || $item->period != $info->period) {
                array_push($related_periods, array('day' => $item->day, 'period' => $item->period, 'id' => $item->id));
            } else if ($item->teacher != $info->teacher) {
                array_push($related_teachers, array('teacher' => $item->teacher, 'id' => $item->id));
            }
        }
        
        $related = array('names' => $related_names, 'periods' => $related_periods, 'teachers' => $related_teachers);
        
        return $related;
    }
    
}