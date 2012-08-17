<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        if ($this->session->userdata('auth') != 'true') {
            redirect('/');
        }
    }
    
    public function index() {
        $this->load->view('import');
    }
    
    private function copyToChar(&$string,$startPos,$char) {
        $output = "";
        while ($string[$startPos] != $char) {
            $output = $output . $string[$startPos];
            $startPos +=1;
        }
        $string = substr($string,$startPos);
        return $output;
    }
    
    public function submit() {
        $allowedExts = array("htm");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        
        if (($_FILES["file"]["size"] < 100000) && in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                throw "Error: " . $_FILES["file"]["error"] . "<br />";
            } else {
                if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                    $fileData = file_get_contents($_FILES['file']['tmp_name']);
                    $fileData = substr($fileData,strpos($fileData,'<tr class="center" bgcolor="#edf3fe">'));
                    $num = 0; //array number
                    while (strpos($fileData,">P",0) != FALSE) {
                        $pd[$num] = $this->copyToChar($fileData,strpos($fileData,">P",0)+1,"<");
                        $clss[$num] = $this->copyToChar($fileData,strpos($fileData,'n="left">',0)+9,"<");
                        $teacher[$num] = $this->copyToChar($fileData,strpos($fileData,'org">',0)+5,"<");
                        $num+=1;
                    }
                    
                    $data = array();
                    for ($i=0; $i<$num; $i++) {
                        $item_class = $clss[$i];
                        $item_teacher = $teacher[$i];
                        $item_period = $pd[$i];
                        
                        $item_class = str_replace(' (Sched. Only)', '', $item_class);
                        
                        $item_teacher = implode(' ', array_reverse(explode(', ', $item_teacher)));
                        
                        $item_period = explode(' ', $item_period);
                        foreach ($item_period as $blob) {
                            $blob = str_replace(')', '', $blob);
                            $blob = explode('(', $blob);
                            
                            $blob[0] = str_replace(array('P1', 'P2', 'P3', 'P4', 'P5', 'AM', 'PM', 'L/A'), array('1', '2', '3', '4', '5', '0', '6', '3.5'), $blob[0]);
                            $blob[0] = explode(',', $blob[0]);
                            $blob_periods = array();
                            foreach ($blob[0] as $blob2) {
                                if (strpos($blob2, '-') != false) {
                                    $blob2 = explode('-', $blob2);
                                    $blob2 = range(intval($blob2[0]), intval($blob2[1]));
                                    $blob_periods = array_merge($blob_periods, $blob2);
                                } else {
                                    $blob_periods[] = $blob2;
                                }
                            }
                            
                            $blob[1] = str_replace(array('M', 'T', 'W', 'H', 'F'), array(1, 2, 3, 4, 5), $blob[1]);
                            $blob[1] = explode(',', $blob[1]);
                            $blob_days = array();
                            foreach ($blob[1] as $blob2) {
                                if (strpos($blob2, '-') != false) {
                                    $blob2 = explode('-', $blob2);
                                    $blob2 = range(intval($blob2[0]), intval($blob2[1]));
                                    $blob_days = array_merge($blob_days, $blob2);
                                } else {
                                    $blob_days[] = $blob2;
                                }
                            }
                            
                            foreach ($blob_days as $day) {
                                foreach ($blob_periods as $period) {
                                    $data[] = array('name' => $item_class, 'teacher' => $item_teacher, 'day' => $day, 'period' => $period);
                                }
                            }
                        }
                    }
                    // Import into database now
                    $this->load->model('datamod');
                    $this->datamod->import($this->session->userdata('name'), $this->session->userdata('email'), $data);
                    
                    redirect('/');
                }
            }
        } else {
            throw "Invalid file";
        }
    }
    
}