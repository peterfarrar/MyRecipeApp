<?php
include 'page/Menu.php';

class ShowMenu extends Menu {
    protected $action = "showRecipe";

    protected function get_searches( $searchVal ) {
        global $db;
        $searches = array(
            "select id, heading, author, date from recipe where heading like {$db->quote($searchVal)}",
            "select a.id id, a.heading heading, a.author author, a.date date from recipe a, ingredients b where a.id = b.recipe_id and lower(ingredient) like {$db->quote($searchVal)}"
        );

        return $searches;
    }

    protected function get_default_menu_query () {
        $menuString = "SELECT * from recipe";
        return $menuString;
    }
}
