<?php

class Menu extends Page {
    // $action needs to be defined in the child menu as a protected item that will direct to that child's page links.
    protected $action = '';
    protected $form = '';
    protected $username = '';
    protected $searchResults;
    protected $searchString;

    public function show_page() {
        $args = array( array('tag' => 'script', 'attr' => array('src' => 'js/menupage.js')) );
        $args[] = array('tag' => 'script', 'attr' => array('src' => 'js/addSearch.js'));
        $this->start_page($args);
        // Create the item list
        if ( isset( $this->searchResults ) ) {
            $rows = $this->searchResults;
        }

        if ( $rows && ! $rows[0] ) {
            if ( isset($_SESSION['user'] ) ) {
                // so akward... would like to use the add_warning/add_redirect... but then, don't want to duplicate
                // the conditional expression above...
                // maybe there's a way to push the else into the start_page?
                print "<div class='modal-message hidden' type='text-warning'>No recipes found</div>";
                print "<div class='modal-redirect hidden' type='text-warning'>index.php?page=addRecipe</div>";
            }
        } else {
            print "<style>div.header:hover { cursor: pointer }</style>";
            print '<div class="container"><div class="list-group menu-page-menu">';
            print <<<EOH
            <div class="header row hidden-xs">
                <div class="col-sm-6 col-xs-12">
                    <span id="recipe_header">Recipe &nbsp;</span>
                    <span id="recipe_sort" class="glyphicon glyphicon-chevron-up"></span>
                </div>
                <div class="author col-sm-3 col-xs-6">
                    <span id="author_header">Author &nbsp;</span>
                    <span id="author_sort" class="glyphicon glyphicon-chevron-up"></span>
                </div>
                <div class="author col-sm-3 col-xs-6">
                    <span id="date_header">Date &nbsp;</span>
                    <span id="date_sort" class="glyphicon glyphicon-chevron-up"></span>
                </div>
            </div>
EOH;
            // print recipe name/info with link to showRecipe page
            if (isset($rows)) {
                foreach ( $rows as $row ) {
                    $date = date("m/d/Y", strtotime($row[date]));

                    print <<<EOR
                        <div class="recipe row">
                            <div class="col-sm-6 col-xs-12"><a class='recipe' href='index.php?page={$this->action}&amp;recipe={$row[id]}'>{$row[heading]}</a></div>
                            <div class="author col-sm-3 col-xs-6">$row[author]</div>
                            <div class="date col-sm-3 col-xs-3">$date</div>
                        </div>
EOR;
                }
            }
            print '</div></div>';

            print "<br/>\n";
        }
        $this->end_page();
    }

    public function validate_fields () {
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            if ( isset($_GET["search"]) ) {
                $searchString = htmlentities($_GET["search"]);

                $this->set_user();

                $this->searchString = strtolower($searchString);
            }
        }

        return TRUE;
    }

    protected function set_user() {
    }

    protected function get_searches( $searchVal ) {
        return array();
    }

    protected function get_default_menu_query () {
    }

    public function process_data() {
        global $db;

        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            $searchVal = '%'. $this->db_clean($this->searchString) .'%';
            $searches = $this->get_searches( $searchVal );
            $searchResults = array();
            foreach ( $searches as $search ) {
                try { 
                    $result = $db->query($search);
                    $rows = $result->fetchAll();
                } catch (PDOException $e) {
                    $this->add_warning("Couldn't query recipe: {$e->getMessage()}");
                    return FALSE;
                }

                if ( $rows[0] ) {
                    $searchResults = array_merge( $searchResults, $rows );
                }
            }

            $this->searchResults = $this->make_results_unique( $searchResults );
        } else {
            $menuString = $this->get_default_menu_query();

            try {
                $result = $db->query($menuString);
                $rows = $result->fetchAll();
            } catch (PDOException $e) {
                $this->add_warning("Unable to query recipes: {$e->getMessage()}");
                throw $e;
            }

            $this->searchResults = $rows;
        }

        return TRUE;
    }

    protected function make_results_unique ( $rows ) {
        $hash_it = array();
        foreach ( $rows as $row ) {
            $hash_it[$row['heading']] = $row;
        }

        $result = array();
        foreach ( $hash_it as $row ) {
            $result[] = $row;
        }

        return $result;
    }
}
