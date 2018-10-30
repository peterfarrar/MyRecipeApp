<?php
include 'page/RecipeFormPage.php';
class EditRecipe extends RecipeFormPage {
    private $recipeUser;

    // This page should only be open to logged in users.
    public function show_page() {
        $args[] = array('tag' => 'script', 'attr' => array( 'src' => "js/recipeForm.js"));
        $this->start_page( $args );

        // need to validate $this->username equals $this->recipeAuthor
        if ( isset($this->username) && isset($this->recipeUser) && ( $this->username === 'admin' || $this->username === $this->recipeUser ) ) {
            $this->build_form();
        }

        $this->end_page();
    }

    protected function set_recipe_id () {
        // check URL for which recipe this is (or if there is not one speficied)
        $queryString = $_SERVER['QUERY_STRING'];
        if ( $queryString ) {
            parse_str($queryString, $queryArgs);
            $this->recipeId = $queryArgs['recipe'];
        } else {
            $this->add_warning("No query string found.");
            return FALSE;
        }
        if ( ! $this->recipeId ) {
            $this->add_warning("No recipe ID found.");
            return FALSE;
        }

        return TRUE;
    }

    protected function get_verify_query () {
        $verifyString = "SELECT * FROM recipe WHERE heading = ". $this->db_quote($this->recipeName) ." and id <> ". $this->recipeId;
        return $verifyString;
    }

    public function validate_fields () {
        if ( ! isset($_SESSION['user'] ) ) {
            $this->add_warning("you must be logged in to edit recipes.");
            $this->add_redirect("index.php?page=login");
            return FALSE;
        } else {
            $this->username = $_SESSION['user'];
        }

        return parent::validate_fields();
    }

    public function process_data() {
        global $db;

        if ( ! $this->recipeId ) {
            $this->set_recipe_id();
        }

        // verify that author is recipeAuthor
        $headerInfo = $this->get_query("select user from recipe where id = $this->recipeId");
        $this->recipeUser = strtolower($headerInfo[0][user]);
        if ( ( isset($this->username) && isset($this->recipeUser) )
            && ( $this->username !== "admin" && $this->username !== $this->recipeUser ) ) {
            $this->add_warning("You can only edit recipes that you have authored.");
            $this->add_redirect("index.php?page=editMenu");
            return FALSE;
        }

        // update header fields in recipe table if updated
        if ($_POST['headings'] == 'edited' ) { 
            $updateString = "UPDATE recipe SET "
                          . "title = ". $this->db_quote($this->recipeTitle) .", "
                          . "heading = ". $this->db_quote($this->recipeName) .", "
                          . "author = ". $this->db_quote($this->recipeAuthor) .", "
                          . "date = str_to_date('$this->recipeDate', '%m/%d/%Y') "
                          . "where id = $this->recipeId";

            // validate and submit
            try {
                $affectedRows = $db->exec($updateString);
            } catch (PDOException $e) {
                $this->add_warning("Couldn't update a row: {$e->getMessage()}");
                throw $e;
            }
            if ( $affectedRows == 0 ) {
                $this->add_warning("No rows updated in table recipe</div><br/>$updateString");
            } else {
                $this->add_info("Recipe updated: '{$this->recipeName}'");
            }
        }

        // load up description/ingredients/steps table info for recipe if edited
        if ($_POST['descriptions'] == 'edited' ) { 
            $this->update_list( array('table' => 'description', 'set_field' => 'text', 'cnt_field' => 'text_no', 'field_list' => $this->descriptionList) );
            $this->add_info("Recipe updated: '{$this->recipeName}'");
        }
        if ($_POST['ingredients'] == 'edited' ) { 
        $this->update_list( array('table' => 'ingredients', 'set_field' => 'ingredient', 'cnt_field' => 'ingredient_no', 'field_list' => $this->ingredientList) );
            $this->add_info("Recipe updated: '{$this->recipeName}'");
        }
        if ($_POST['steps'] == 'edited' ) { 
            $this->update_list( array('table' => 'steps', 'set_field' => 'step', 'cnt_field' => 'step_no', 'field_list' => $this->stepList) );
            $this->add_info("Recipe updated: '{$this->recipeName}'");
        }

        // This has to come after the above edited update, or it overwrites the sumbitted info.
        // Performance wise, it might make sense to load only those that aren't marked edited
        $this->set_recipe_info();

        return TRUE;
    }

    private function update_list( $args ) {
        global $db;
        $cnt = 0;

        $table    = $args['table'];
        $cntField = $args['cnt_field'];
        $setField = $args['set_field'];

        $deleteString = "Delete from $table where recipe_id = $this->recipeId";
        $insertString = "Insert into $table (recipe_id, $cntField, $setField) VALUES ( $this->recipeId, ?, ? )";

        // delete old values
        if ( $db->query($deleteString) === FALSE ) {
            $this->add_warning("No rows updated in table recipe. Unable to delete previous rows</div><br/><div>$deleteString");
            return FALSE;
        }

        // insert new values
        foreach ( $args['field_list'] as $value ) {
            $cnt ++;

            try {
                $insertion = $db->prepare($insertString);
                $result = $insertion->execute(array( $cnt, $this->db_clean($value) ));
            } catch (PDOException $e) {
                $this->add_warning("Couldn''t update a row: {$e->getMessage()}");
                throw $e;
            }
        }
    }

    private function set_recipe_info() {
        // Performance wise, it might make sense to load only those that aren't marked edited
        // fill up the recipe attributes
        // Decided that date should be pulled raw and converted in PHP,
        // because PHP conversion will allow future development for region specific formats.
        $headerInfo = $this->get_query("select title, heading, author, date from recipe where id = $this->recipeId");
        $date               = $headerInfo[0][date];
        $this->recipeDate   = date("m/d/Y", strtotime($date));
        $this->recipeTitle  = $headerInfo[0][title];
        $this->recipeName   = $headerInfo[0][heading];
        $this->recipeAuthor = $headerInfo[0][author];

        $tables = array('description', 'ingredients', 'steps');
        foreach ( $tables as $table ) {
            switch ($table) {
                case 'description':
                    $result = $this->get_query("select text from description where recipe_id = $this->recipeId order by text_no");
                    $this->descriptionList = $this->query_to_list($result, 'text');
                    break;
                case 'ingredients':
                    $result = $this->get_query("select ingredient from ingredients where recipe_id = $this->recipeId order by ingredient_no"); 
                    $this->ingredientList = $this->query_to_list($result, 'ingredient');
                    break;
                case 'steps':
                    $result = $this->get_query("select step from steps where recipe_id = $this->recipeId order by step_no");
                    $this->stepList = $this->query_to_list($result, 'step');
                    break;
            }
        }
    }

    private function query_to_list($result, $field) {
        foreach ( $result as $record ) {
            $row[] = $record[$field];
        }

        return $row;
    }
    
    public function get_query($select) {
        global $db;

        try {
            $result = $db->query($select);
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->add_warning("Unable to query database: {$e->getMessage()}");
            throw $e;
        }

        return $rows;
    }
}

