<?php
include 'page/Menu.php';

class EditMenu extends Menu {
    protected $action = "editRecipe";

    protected function set_user() {
            if ( isset($_SESSION['user'] ) ) {
                $this->username = $_SESSION['user'];
            } else {
                $this->add_warning("You must be logged in to edit recipes.");
                $this->add_redirect("index.php?page=login");
                return FALSE;
            }
    }

    protected function get_searches( $searchVal ) {
        global $db;

        if ( $this->username === 'admin' ) {
            $searches = array(
                "select id, heading, author, date from recipe where heading like {$db->quote($searchVal)}",
                "select a.id id, a.heading heading, a.author author, a.date date from recipe a, ingredients b where a.id = b.recipe_id and lower(ingredient) like {$db->quote($searchVal)}"
            );
        } else {
            $searches = array(
                "select id, heading, author, date from recipe where heading like {$db->quote($searchVal)} and user = {$this->db_quote($this->username)}",
                "select a.id id, a.heading heading, a.author author, a.date date from recipe a, ingredients b where a.id = b.recipe_id and lower(ingredient) like {$db->quote($searchVal)} and a.user = {$this->db_quote($this->username)}"
            );
        }
        
        return $searches;
    }

    protected function get_default_menu_query () {
            if ( $this->username === 'admin' ) {
                $menuString = "SELECT * from recipe";
            } else {
                $menuString = "SELECT * from recipe where user = {$this->db_quote($this->username)}";
            }
            return $menuString;
    }

    public function process_data() {

        if ( isset($_SESSION['user'] ) ) {
            $this->username = $_SESSION['user'];
        } else {
            $this->add_warning("You must be logged in to edit recipes.");
            $this->add_redirect("index.php?page=login");
            return FALSE;
        }

        parent::process_data();
    }
}
