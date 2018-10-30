<?php
class ShowRecipe extends Page {
    private $action;
    private $recipeId;
    private $descriptionList;
    private $ingredientList;
    private $stepList;
    private $title;
    private $heading;
    private $author;
    private $date;

    public function validate_fields() {
        // check URL for which recipe this is (or if there is not one speficied)
        $queryString = $_SERVER['QUERY_STRING'];
        if ( $queryString ) {
            parse_str($queryString, $queryArgs);
            $recipeId = $queryArgs['recipe'];
            $action = $queryArgs['action'];
        } else {
            // redirect to menu page
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=menu");
            exit();
        }

        if ( ! $recipeId ) {
            // redirect to menu page
            header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=menu");
            exit();
        }

        $this->recipeId = $recipeId;

        if ( $action ) {
            $this->action = $action;
        }

        return TRUE;
    }

    public function process_data() {
        global $db;

        // load up header info
        $selectString = "SELECT * FROM recipe WHERE id = '$this->recipeId'";
        $headerInfo   = $this->unload_list( $selectString );
        $row = $headerInfo[0];
        if ( ! $row ) {
            $this->add_warning("No recipe string found for recipe_id of $this->recipeId.");
            return FALSE;
        }
        $this->title   = html_entity_decode($row['title']);
        $this->heading = $row['heading'];
        $this->author  = $row['author'];
        $this->date    = $row['date'];

        // Convert date format from MySQL default of yyyy-mm-dd to mm/dd/yyyy
        // Could easily have this tie to a config or region code to display regionally relevant format.
        $this->date = date("m/d/Y", strtotime($this->date));
        // load up description/ingredients/steps table info for recipe
        $selectString = "SELECT text FROM description where recipe_id = '$this->recipeId' order by text_no";
        $this->descriptionList = $this->unload_list( $selectString );

        $selectString = "SELECT ingredient FROM ingredients where recipe_id = '$this->recipeId' order by ingredient_no";
        $this->ingredientList  = $this->unload_list( $selectString );

        $insertString = "SELECT step FROM steps where recipe_id = '$this->recipeId' order by step_no";
        $this->stepList = $this->unload_list( $insertString );

    }

    public function show_page() {
        // open body
        $args = array(
            array('tag' => 'link', 'attr' => array('rel' => 'stylesheet', 'href' => 'css/recipe.css')),
            array('tag' => 'script', 'attr' => array('src' => 'js/recipe.js'))
        ); 
        $this->start_page($args);

        print <<<EOS
        <script>
        $( function(){
            $('head > title').text("{$this->title}");
        });
        </script>
EOS;
        print '<div class="recipe"><div class="container">';
        // add edit and print buttons
        // only show edit and print buttons if action=print is not in play
        if ( isset($this->action) && $this->action === 'print' ) {
            print "<script>$(function(){window.print();});</script>";
        } else {
            print "<button class='btn btn-default btn-md' onclick='location.href=\"index.php?page=editRecipe&recipe=$this->recipeId\";'>Edit <span class='glyphicon glyphicon-edit'></span></button> ";
            print "<button class='btn btn-default btn-md' onclick='location.href=\"index.php?page=showRecipe&recipe=$this->recipeId&action=print\";'>Print <span class='glyphicon glyphicon-print'></span></button>";
        }


        // header
        print <<<EOH
        <div class="header col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <h1>$this->heading</h1>
            <strong class="hidden-xs">Created by $this->author - $this->date</strong>
            <strong class="hidden-sm hidden-md hidden-lg">Created by $this->author<br/>$this->date</strong>
        </div>
EOH;
        // description
        print '<div class="description col-lg-7 col-md-7 col-sm-7 col-xs-12">';
        foreach ( $this->descriptionList as $row ) {
            print '<p class="description">'. $row[text] ."</p>\n";
        }
        print '</div>';

        // ingredients
        print'<div class="ingredients col-lg-5 col-md-5 col-sm-5 col-xs-12"><h3>Ingredients:</h3><ul id="ingredients-list" type="list-group">';
        foreach ( $this->ingredientList as $row ) {
            print "<li>". $row[ingredient] ."</li>\n";
        }
        print '</ul></div>';

        // steps
        print '<div class="steps col-lg-12 col-md-12 col-sm-12 col-xs-12"><ol type="steps list-group square">';
        foreach ( $this->stepList as $row ) {
            print "<li>". $row[step] ."</li>\n";
        }
        print '</ol></div>';

        // close body
        print '</div></div>';
        include "copyright.php";
        $this->end_page();
    }

    private function unload_list( $selectString ) {
        global $db;
        
        try { 
            $result = $db->query($selectString);
            $rows = $result->fetchAll();
        } catch (PDOException $e) {
            $this->add_warning("Couldn't query recipe: '{$e->getMessage()}'");
            return FALSE;
        }
        if ( ! $rows[0] ) {
            $this->add_warning("could not retrieve info from database.");
            return FALSE;
        }

        return $rows;
    }
}
