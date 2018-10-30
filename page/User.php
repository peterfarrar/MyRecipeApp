<?php
include 'page/HomePage.php';

class User extends HomePage {
    public function show_page() {
        // So, what I'd like to do here is, force redirect to HTTPS if not using HTTPS.
        if ( $_SERVER['HTTPS'] !== 'on' ) {
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
            exit();
        }

        // redirect to page=admin if user is 'admin'
        // redirect to page=login if user is not logged in
        if ( isset($_SESSION['user']) ) {
            if ( $_SESSION['user'] === 'admin' ) {
                header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=admin");
                exit();
            }
        } else {
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=login");
            exit();
        }

        $action = $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'];
        $username = strtolower($_SESSION['user']);

        $this->start_page();

        print <<<EOP
            <div class="container">
                <p>
                    <h4>You are logged in as "$username"</h4>
                </p>
                <br/>

                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <p align="left">Change Password:</p>
                            <form name="changeUserPassword" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group col-xs-12">
                                    <input type="hidden" name="form" value='changeUserPassword' class="login-input">
                                    <input type="hidden" name="username" value="$username" class="login-input">
                                    <input type="password" maxlength="32" name="password" placeholder="Enter Current Password" class="login-input form-control">
                                    <input type="password" maxlength="32" name="password1" placeholder="Enter New Password" class="login-input form-control">
                                    <input type="password" maxlength="32" name="password2" placeholder="Re-enter New Password" class="login-input form-control">
                                </div><!-- End of input-group -->
                                <br/>
                                <button type="button" value="Submit" class="login-btn btn btn-info">Submit</button>
                                <input name="submit" value="" class="hidden btnSubmit btn btn-info" type="submit">
                                <div class="hidden submit-message">Are you sure you want to change your password?</div>
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div><!-- End of panel-body -->
                </div><!-- End of panel -->
                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <p align="left">Log Out:</p>
                            <form name="userLogout" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group">
                                    <input type="hidden" name="form" value='userLogout' class="login-input">
                                </div><!-- End of input-group -->
                                <!-- input name="submit" value="Log Out" class="btnSubmit btn btn-info" type="submit" -->
                                <!-- button type="button" value="Submit" class="login-btn btn btn-info">Log Out</button -->
                                <input name="submit" value="Submit" class="btnSubmit btn btn-info" type="submit">
                                <!-- div class="hidden submit-message">Are you sure you want to log out?</div -->
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div>
                </div><!-- End of panel -->

            </div>
EOP;
        
        $this->end_page();
    }

    private function change_user_password () {
        $return = $this->validate_password( $this->username, htmlentities($_POST['password']));
        if ( $return ) {
            $return = $this->validate_change_user_password_fields();
        }
        return $return;
    }

    public function validate_fields () {
        $return = TRUE;

        if ( isset($_SESSION['user']) ) {
            $this->username = strtolower($_SESSION['user']);
        } 
        
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $form = htmlentities($_POST['form']);

            if ( $form ) {
                $this->form = $form;
            } else {
                return FALSE;
            }

            switch ($form) {
                case 'changeUserPassword':
                    $return = $this->change_user_password();
                    break;
                case 'userLogout':
                    break;
            }
        }

        return $return;
    }
    
    public function process_data () {
        $return = TRUE;

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $form = $this->form;

            switch ($form) {
                case 'changeUserPassword':
                    $return = $this->process_change_user_password();
                    break;
                case 'userLogout':
                    $return = $this->process_log_out();
                    break;
            }
        }

        return $return;
    }
}
