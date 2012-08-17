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
        $row = $query->row();
        
        return $row->id;
    }
    
    public function import($name, $email, $data) {
        // Add the person to the database if they do not already exist
        $this->addPerson($name, $email);
        $user_id = $this->getPersonId($name, $email);
        
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
        $query = $this->db->query('SELECT * FROM (SELECT id, CONCAT(name, ", ", teacher) AS body, "class" AS type FROM classes UNION ALL SELECT id, name AS body, "person" AS type FROM people) AS search WHERE body LIKE "%' . $q . '%" GROUP BY body');
        $result = $query->result();
        $result_chunk = array_chunk($result, 5);
        if (count($result_chunk) > 0) {
            return $result_chunk[0];
        } else {
            return array();
        }
    }
    
}