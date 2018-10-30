<?php
// Should this be, or include, an interface? For example
// for show_page/vaidateFields/process_data?
class Page {
    protected $page;
    protected $warningMessages;
    protected $infoMessages;
    protected $modalRedirect;

    // Static factory function
    public static function get_page( $page ) {
        $page = ucfirst( $page );
        $includeFile = "page/{$page}.php";
        if (is_file($includeFile)){
            try {
                include $includeFile;
            } catch ( Exception $e ) {
                print "<p>Page not found: $includeFile</p>\n";
                exit -1;
            }
            $pageObj = new $page();
            return $pageObj;
        } else {
            print "<p>File not found: $includeFile</p>\n";
            exit -1;
        }
    }

    // these next three are the virtuals for all children
    public function show_page() {
    }

    public function validate_fields () {
    }

    public function process_data() {
    }

    // these next two should be called by the children in show_page()
    protected function start_page( $args=array() ) {
        // add logic to process $args
        // $args is an array of key/val pairs
        // Example:
        // $args = array(array('tag' => 'script', 'attr' => array('src' => '/lib/angular.min.js'), array('tag' => 'meta', 'attr' => array('charset' => 'UTF-8'));
        // These tags will be added to the header
        
        print <<<EOS
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>My Recipes</title>
                <link rel="stylesheet"
                      href="/lib/bootstrap/css/bootstrap.min.css"/>

                <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
                <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
                <!--[if lt IE 9]>
                  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
                  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
                <![endif]-->

                <script src="/lib/jquery-3.2.0.min.js"></script>
                <script src="js/modal_popup.js"></script>
                <style>
                    /*
                     * Temp fix til I can bootstrap better
                     * The Nav bar is covering the top of the page, including the header and first menu item(s)
                     */
                    body {
                        padding-top: 70px;
                        padding-bottom: 70px;
                    }
                </style>
EOS;
        // process $args as tags and attributes:
        // later we can insert text between the tags.
        foreach ( $args as $arg ) {
            print "<{$arg['tag']}";
            foreach ( $arg['attr'] as $attr => $val) {
                print " {$attr}='$val'";
            }
            print ">\n";
            // put text here
            if ( isset($arg['text']) ) {
                print htmlentities($arg['text']);
            }
            // close tag
            print "\n</{$arg['tag']}>\n";
        }

        print <<<EOS
            </head>
            <body>

                <!-- Fixed navbar -->
                <div class="navbar navbar-default navbar-fixed-top" role="navigation">
                    <div class="container">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand" href="index.php">My Recipes</a>
                        </div>
                        <div class="navbar-collapse collapse">
                            <ul class="nav navbar-nav">
EOS;

        // check URL for which page this is (or home page)
        $queryString = $_SERVER['QUERY_STRING'];
        if ( $queryString ) {
            parse_str($queryString, $queryArgs);
            $page=$queryArgs['page'];
        } else {
            $page='index';
        }

        // figure out what is happening with login/user page
        if ( isset($_SESSION['user'] ) ) {
            $user = ucfirst($_SESSION['user']);
            if ($user === 'admin') {
                $homePage = 'admin';
            } else {
                $homePage = 'user';
            }
        } else {
            $user = 'Login';
            $homePage = 'login';     
        }
        
        $links = array( "showMenu" => "Main Menu", "addRecipe" => "Add Recipe", "editMenu" => "Edit Recipes", $homePage => $user );
        foreach ( $links as $key => $val ) {
            print "<li";
            print ($key === $page)?" class='active'":"";
            print "><a href='index.php?page=$key'>$val</a></li>\n";
        }

        print <<<EOS
                            </ul>
                        </div><!--/.nav-collapse -->
                    </div>
                </div>
EOS;
        /* Note: need to revisit the .nav.navbar-nav li>a
         * say, create a hashtable of pages, then check the QUERY_STRING page value... 
         * if they're the same, class=active, else just set page/text
         * (i.e.: $arr = array( "menu" => "Main Menu", "addItem" => "Add Item", "editItems" => "Edit Items" ); )
         */

        // modal messages:
        if ( isset($this->warningMessages) || isset($this->infoMessages) ) {
            // this should create pop up confirmation if a recipe has been updated
            //print $this->modalMessage;
            $this->process_modal_messages();
        }

        print <<<EOM
            <button class="hidden btn btn-primary" data-toggle="modal" data-target="#modal"></button>
            <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="myModalLabel"></h4>
                        </div>
                        <div class="modal-body">
                            
                            <h1 class="message"></h1>

                        </div>
                        <div class="modal-footer">
                            <button type="button" id="continue" class="btn btn-success">Continue</button>
                            <button type="button" id="default" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
EOM;
    }

    protected function end_page() {
        include "copyright.php";
        print <<<EOE
            <script src="/lib/bootstrap/js/bootstrap.min.js"></script>

            </body>
            </html>
EOE;
    }

    protected function add_warning( $message ) {
        $this->warningMessages[$message] = 1;
    }

    protected function add_info ( $message ) {
        $this->infoMessages[$message] = 1;
    }

    protected function add_redirect ( $redirect ) {
        $this->modalRedirect = $redirect;
    }

    private function process_modal_messages() {
        if ( isset($this->warningMessages) ) {
            foreach ( $this->warningMessages as $message => $redirect ) {
                print "<div class='modal-message hidden' type='text-warning'>$message</div>";
            }
        }
        if ( isset($this->infoMessages) ) {
            foreach ( $this->infoMessages as $message => $redirect ) {
                print "<div class='modal-message hidden' type='text-info'>$message</div>";
            }
        }
        if ( isset($this->modalRedirect) ) {
            print "<div class='modal-redirect hidden' type='redirect'>$this->modalRedirect</div>";
        }
    }

    protected function db_clean( $field ) {
        // strtr and $db->quote are used to escape SQL wild card characters and to properly quote the where value
        return strtr($field, array('_' => '\_', '%' => '\%'));
    }

    protected function db_quote( $field ) {
        global $db;
        return $db->quote($this->db_clean($field));
    }
}
