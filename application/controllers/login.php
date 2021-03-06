<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Login extends CI_Controller {
    
    public function index() {
        require(APPPATH . 'classes/openid.php');
        $openid = new LightOpenID($_SERVER['HTTP_HOST']);
        if (!$openid->mode) {
            // Didn't get login info from the OpenID provider yet / came from the login link
            $openid->identity = 'https://www.google.com/accounts/o8/id';
            $openid->required = array('namePerson/first', 'namePerson/last', 'contact/email');
            header('Location: ' . $openid->authUrl());
        } else if ($openid->mode == 'cancel') {
            // The user decided to cancel logging in, so we'll redirect to the home page instead
            redirect('/');
        } else {
            // The user has logged in and the user's info is ready
            if (!$openid->validate()) {
                // Authentication failed, try logging in again
                $this->login_failure('Authentication failed, try logging in again.');
            } else {
                // Authentication was successful
                
                // Get user attributes:
                $user_data = $openid->getAttributes();
                
                // Check to make sure that the user is logging in using a @ctemc.org account:
                if (preg_match('/^[^@]+@ctemc\.org$/', $user_data['contact/email'])) {
                    //echo "Welcome, " . " " . $user_data['namePerson/first'] . ' ' . $user_data['namePerson/last'];
                    
                    $name = $user_data['namePerson/first'] . ' ' . $user_data['namePerson/last'];
                    $email = $user_data['contact/email'];
                    
                    // Load user ID if it exists
                    $this->load->model('datamod');
                    $user_id = $this->datamod->getPersonId($name, $email);
                    
                    if ($user_id == false) {
                        $this->datamod->addPerson($name, $email);
                        $user_id = $this->datamod->getPersonId($name, $email);
                    }
                    
                    $this->session->set_userdata(array('auth' => 'true', 'name' => $name, 'email' => $email, 'id' => $user_id));
                    
                    redirect('/');
                } else {
                    $this->login_failure('Please log in using a @ctemc.org account.');
                }
                
            }
        }
    }
    
    private function login_failure($message = 'Login failure') {
        echo $message;
        exit();
    }
    
    public function logout() {
        $this->session->sess_destroy();
        redirect('/');
    }
    
}
