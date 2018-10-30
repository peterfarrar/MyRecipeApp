<?php
include 'page/HomePage.php';

class Admin extends HomePage {
    public function show_page() {
        // Force redirect to HTTPS if not using HTTPS.
        if ( $_SERVER['HTTPS'] !== 'on' ) {
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
            exit();
        }

        // redirect to page=user if user is not 'admin'
        // redirect to page=login if user is not logged in
        if ( isset($_SESSION['user']) ) {
            if ( $_SESSION['user'] !== 'admin' ) {
                header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=user");
                exit();
            }
        } else {
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=login");
            exit();
        }
        
        $action = $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'];

        $this->start_page();

        print <<<EOP
            <div class="container">
                <p>
                    <h1>You are logged in as admin</h1>
                </p>

                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <p align="left">Add User:</p>
                            <form name="addUser" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group col-xs-12">
                                    <input type="hidden" name="form" value='addUser' class="login-input"></td>
                                    <input type="text" maxlength="63" name="username" placeholder="Username" class="login-input form-control">
                                    <input type="password" maxlength="32" name="password1" placeholder="Enter New Password" class="login-input form-control">
                                    <input type="password" maxlength="32" name="password2" placeholder="Re-enter New Password" class="login-input form-control">
                                </div><!-- End of input-group -->
                                <br/>
                                <button type="button" value="Submit" class="login-btn btn btn-info">Submit</button>
                                <input name="submit" value="" class="hidden btnSubmit btn btn-info" type="submit">
                                <div class="hidden submit-message">Are you sure you want to add this user?</div>
                                <!-- div class="hidden submit-message">Are you sure you want add user %username%?</div -->
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div>
                </div><!-- End of panel -->
                <p>
                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <p align="left">Delete User:</p>
                            <form name="deleteUser" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group col-xs-12">
                                    <input type="hidden" name="form" value='deleteUser' class="login-input">
                                    <input type="text" maxlength="63" name="username" placeholder="Username" class="login-input form-control">
                                </div><!-- End of input-group -->
                                <br/>
                                <button type="button" value="Submit" class="login-btn btn btn-info">Submit</button>
                                <input name="submit" value="" class="hidden btnSubmit btn btn-info" type="submit">
                                <div class="hidden submit-message">Are you sure you want to delete this user?</div>
                                <!-- div class="hidden submit-message">Are you sure you want to delete user %username%?</div -->
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div>
                </div><!-- End of panel -->

                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <p align="left">Change User Password:</p>
                            <form name="changeUserPassword" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group col-xs-12">
                                    <input type="hidden" name="form" value='changeUserPassword' class="login-input form-control">
                                    <input type="text" maxlength="63" name="username" placeholder="Username" class="login-input form-control">
                                    <input type="password" maxlength="32" name="password1" placeholder="Enter New Password" class="login-input form-control">
                                    <input type="password" maxlength="32" name="password2" placeholder="Re-enter New Password" class="login-input form-control">
                                </div><!-- End of input-group -->
                                <br/>
                                <button type="button" value="Submit" class="login-btn btn btn-info">Submit</button>
                                <input name="submit" value="" class="hidden btnSubmit btn btn-info" type="submit">
                                <div class="hidden submit-message">Are you sure you want to change this user's password?</div>
                                <!-- div class="hidden submit-message">Are you sure you want to change user %username% password?</div -->
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div>
                </div><!-- End of panel -->
                <p>
                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <p align="left">Log Out:</p>
                            <form name="userLogout" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group">
                                    <input type="hidden" name="form" value='userLogout' class="login-input">
                                </div><!-- End of input-group -->
                                <input name="submit" value="Log Out" class="btnSubmit btn btn-info" type="submit">
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div>
                </div><!-- End of panel -->

                <!-- p>
                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <form name="userLogout" method="POST" action="$action" class="form-horizontal">
                                <input type="hidden" name="form" value='userLogout' class="login-input"></td>
                                <table border="0" cellpadding="10" cellspacing="1" width="500" align="center" class="tblLogin">
                                    <th><td colspan="2">Admin Log Out</td></th>
                                    <tr class="tableheader">
                                    <td>
                                    <input type="hidden" name="logout" value='true' class="login-input form-control"></td>
                                    </tr>
                                    <tr class="tableheader">
                                    <td align="left" colspan="2"></br><input type="submit" name="submit" value="Log Out" class="btn btn-info btnSubmit"></td>
                                    </tr>
                                </table>
                                <p/>
                            </form>
                        </div>
                        <div class="hidden-sm hidden-xs col-lg-3 col-md-3"></div>
                    </div>
                </div><!-- End of panel -->
            </div>
EOP;
        
        $this->end_page();
    }

    public function validate_fields () {
        $return = TRUE;

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $form = htmlentities($_POST["form"]);

            if ( $form ) {
                $this->form = $form;
            } else {
                return FALSE;
            }

            switch ($form) {
                case 'addUser':
                    $return = $this->validate_add_user_fields();
                    break;
                case 'deleteUser':
                    $return = $this->validate_delete_user_fields();
                    break;
                case 'changeUserPassword':
                    $return = $this->validate_change_user_password_fields();
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
                case 'addUser':
                    $return = $this->process_add_user();
                    break;
                case 'deleteUser':
                    $return = $this->process_delete_user();
                    break;
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
