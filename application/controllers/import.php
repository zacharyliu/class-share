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
    
    public $fileData = '';
    
    //In $fileData, copy from $startPos to an ending char, removing everything up to the ending char (inclusive).
    private function copyToCharMain($startPos,$char) {
        $output = "";
        while ($this->fileData[$startPos] != $char){
            $output = $output . $this->fileData[$startPos];
            $startPos +=1;
        }
        $this->fileData = substr($this->fileData,$startPos);
        return $output;
    }
    
    public function submit() {
        $allowedExts = array("htm","html");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        
       if (($_FILES["file"]["type"] == "text/html") //only allow html
          &&($_FILES["file"]["size"] > 35000)  //size >35kb
          &&($_FILES["file"]["size"] < 65000) //size <65kb
          && in_array($extension, $allowedExts)) {//make sure extension matches
            if ($_FILES["file"]["error"] > 0) {
                throw new ErrorException("Error: " . $_FILES["file"]["error"] . "<br />");
            } else {
                if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                    $this->fileData = file_get_contents($_FILES['file']['tmp_name']);
                    //perform a basic check to make sure this is a powerschool html
                    if ((strpos($this->fileData,'-- start logo, school, term,') != FALSE)
                      && (strpos($this->fileData,'Weighted Percent GPA (Y1)') != FALSE)
                      && (strpos($this->fileData,'Render list of associated students') != FALSE)) {
                        $this->fileData = substr($this->fileData,strpos($this->fileData,'<tr class="center" bgcolor="#edf3fe">'));
                        $n = 0; //array number
                        while (strpos($this->fileData,">P",0) != FALSE) {
                            $pd[$n] = $this->copyToCharMain(strpos($this->fileData,">P",0)+1,"<");
                            $clss[$n] = $this->copyToCharMain(strpos($this->fileData,'n="left">',0)+9,"<");
                            $teacher[$n] = $this->copyToCharMain(strpos($this->fileData,'title="Details about ',0)+21,'"'); //$this->copyToCharMain(strpos($this->fileData,'org">',0)+5,"<");
                            $n+=1;
                        }
                    
                        $data = array();
                        for ($i=0; $i<$n; $i++) {
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
                        $this->datamod->import($this->session->userdata('id'), $data);
                        
                        redirect('/');
                    }
                    else throw new ErrorException("Verify integrity of powerschool html file.");
                }
            }
        }
        else {
            throw new ErrorException("Invalid file.");
        }
    }
}
