<?php
include 'page/HomePage.php';

class Login extends HomePage {
    public function show_page() {
        // Force redirect to HTTPS if not using HTTPS.
        if ( $_SERVER['HTTPS'] !== 'on' ) {
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
            exit();
        }

        // Redirect to appropriate page if user is logged in.
        if ( isset($_SESSION['user']) ) {
            if( $_SESSION['user'] === 'admin' ) {
                header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=admin");
                exit();
            } else {
                header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=user");
                exit();
            }
        }

        $action = $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'];
        
        $this->start_page();

        print <<<EOP
            <div class="container">
                <div class="panel">
                    <div class="panel-body">
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-1"></div>
                        <div class="login col-lg-6 col-md-6 col-sm-6 col-xs-10">
                            <p align="left">User Login:</p>
                            <form name="userLogin" method="POST" action="$action" class="form-horizontal">
                                <div class="input-group col-xs-12">
                                    <input maxlength="63" name="username" placeholder="Username" class="login-input form-control" type="text"></textarea>
                                    <input maxlength="32" name="password" placeholder="Password" class="login-input form-control" type="password"></textarea>
                                </div>
                                <br/>
                                <input name="submit" value="Submit" class="btnSubmit btn btn-info" type="submit">
                            </form>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-1"></div>
                    </div>
                </div><!-- End of panel -->
            </div>
EOP;
        
        $this->end_page();
    }

    public function validate_fields () {
        $return = TRUE;

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $username = htmlentities($_POST["username"]);
            $password = htmlentities($_POST["password"]);

            $return = $this->validate_password( $username, $password );
        }

        return $return;
    }

    public function process_data () {
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if ( $this->username ) {
                // log in
                $_SESSION['user'] = $this->username;
            }
        }

        return TRUE;
    }
}
