<?php
include 'page/HomePage.php';

class Index extends HomePage {
    private $searchString;

    public function show_page() {
        // Force redirect to HTTPS if not using HTTPS.
        if ( $_SERVER['HTTPS'] !== 'on' ) {
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
            exit();
        }

        $action = $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'];
        
        $args = array();
        $this->start_page($args);

        print <<<EOP
            <style>
                .center {
                    text-align: center;
                }
            </style>

            <div class="container">
                <div class="center">
                    <p>
                    <h1>Welcome to My Recipe App!</h1>
                    </p>
EOP;
        if ( ! isset($_SESSION['user'] ) ) {
            print <<<EOP
                    <p>
                    <h2>If you have an account, <a href="index.php?page=login">log in now!</a></h2>
                    </br>
EOP;
        } else {
            $user = ucfirst($_SESSION['user']);

            print <<<EOP
                    <p>
                    <h2>You are logged in as $user</h2>
                    </br>
EOP;
        }
        print <<<EOP

                    <form class="navbar-form" role="search" method="get" action="index.php">
                        <div class="input-group" style="width: 100%">
                            <input name="page" value="showMenu" class="search" type="hidden">
                            <input class="form-control" placeholder="Search" name="search" type="text">
                            <div class="input-group-btn">
                                <button class="btn btn-default" type="submit" style="width: 100%"><i class="glyphicon glyphicon-search" style="width: 100%"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
EOP;
        
        $this->end_page();
    }

    public function validate_fields () {
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            if ( isset($_GET["search"]) ) {
                $searchString = htmlentities($_GET["search"]);
                $this->searchString = strtolower($searchString);
            }
        }

        return TRUE;
    }

    public function process_data() {
        global $db;

        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            if ( isset($_GET["search"]) ) {
                
                header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=showMenu&search=". $this->searchString);
            }
        }

        return TRUE;
    }
}
