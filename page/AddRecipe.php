<?php
include 'page/RecipeFormPage.php';
class AddRecipe extends RecipeFormPage {
    // This page should only be open to logged in users.
    public function show_page() {
        // need to unset these so that the form will not return after a POST
        // with the fields filled out with the added recipe.
        unset($this->recipeId);
        unset($this->recipeTitle);
        unset($this->recipeName);
        unset($this->recipeAuthor);
        unset($this->recipeDate);
        unset($this->descriptionList);
        unset($this->ingredientList);
        unset($this->stepList);

        $args[] = array('tag' => 'script', 'attr' => array( 'src' => "js/recipeForm.js"));
        $this->start_page( $args );

        if ( isset($_SESSION['user'] ) ) {
            $this->username = $_SESSION['user'];
            $this->build_form();
        } else {
            // unfortunately this comes after start_page()... would be nice to use the add_warning/add_info methods...
            print "<div class='modal-message hidden' type='text-warning'>You must be logged in to add recipes.</div>";
            print "<div class='modal-redirect hidden' type='text-warning'>index.php?page=login</div>";
        }

        $this->end_page();
    }

    protected function set_recipe_id () {
        // this is just a blank function to allow validate_fields to be part of the parent class
    }

    protected function get_verify_query () {
        $verifyString = "SELECT * FROM recipe WHERE heading = ". $this->db_quote($this->recipeName);
        return $verifyString;
    }

    public function process_data() {
        global $db;

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $user = '';
            if ( isset($_SESSION['user']) ) {
                $user = $_SESSION['user'];
            }

            $insertString = "INSERT INTO recipe (title, heading, author, date, user) VALUES ("
                          . $this->db_quote($this->recipeTitle) .", "
                          . $this->db_quote($this->recipeName) .", "
                          . $this->db_quote($this->recipeAuthor) .", "
                          . "str_to_date('$this->recipeDate', '%m/%d/%Y'), "
                          . $this->db_quote($user)
                          . ")";

            // validate and submit
            try {
                $affectedRows = $db->exec($insertString);
            } catch (PDOException $e) {
                $this->add_warning("Couldn't insert a row: {$e->getMessage()}");
                throw $e;
            }
            $this->add_info("Recipe added: '{$this->recipeName}'");

            // get recipe header ID
            $verifyString = "SELECT * FROM recipe WHERE heading = ". $this->db_quote($this->recipeName);

            try { 
                $result = $db->query($verifyString);
                $rows = $result->fetchAll();
            } catch (PDOException $e) {
                $this->add_warning("Couldn't query recipe: '{$e->getMessage()}'");
                return FALSE;
            }
            if ( ! $rows[0] ) {
                $this->add_warning("'$this->recipeName' could not be added to the database.");
                return FALSE;
            }

            // check that there is only one row returned?
            if ( $rows[1] ) {
                $this->add_warning("'$this->recipeName' exists more than once in table recipe<br/>This shouldn't happen!");
                return FALSE;
            }

            $row = $rows[0];
            $recipeId = $row[id];

            // load up description/ingredients/steps table info for recipe
            $insertString = "INSERT INTO description (recipe_id, text_no, text) ";
            $insertString .= "VALUES (?,?,?)";
            $this->load_list( $recipeId, $insertString, $this->descriptionList );

            $insertString = "INSERT INTO ingredients (recipe_id, ingredient_no, ingredient) ";
            $insertString .= "VALUES (?,?,?)";
            $this->load_list( $recipeId, $insertString, $this->ingredientList );

            $insertString = "INSERT INTO steps (recipe_id, step_no, step) ";
            $insertString .= "VALUES (?,?,?)";
            $this->load_list( $recipeId, $insertString, $this->stepList );
        }

        return TRUE;
    }

    private function load_list( $recipeId, $insertString, $loadList ) {
        global $db;
        $cnt = 0;
        
        foreach ( $loadList as $value ) {
            $cnt ++;
            try {
                $insertion = $db->prepare($insertString);
                $result = $insertion->execute(array( $recipeId, $cnt, $this->db_clean($value) ));
            } catch (PDOException $e) {
                $this->add_warning("Couldn't insert a row: '{$e->getMessage()}'");
                throw $e;
            }
        }
    }
}
