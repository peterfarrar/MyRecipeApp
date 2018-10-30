<?php
class RecipeFormPage extends Page {
    protected $recipeId;
    protected $recipeTitle;
    protected $recipeName;
    protected $recipeAuthor;
    protected $recipeDate;
    protected $descriptionList;
    protected $ingredientList;
    protected $stepList;

    // This page shouldn't be called directly... redirect to index page.
    public function show_page() {
        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=editMenu");
        exit();
    }

    protected function build_form () {
        $action = $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'];

        print '<div class="container">';
        print '<form class="form-group" method="POST" id="thisform" action="'. $action .'">';

        // add header fields
        $this->build_header_info();

        // add each of the rest of the recipe fields for descriptioin/ingredients/steps
        $tables = array('description', 'ingredients', 'steps');
        foreach ( $tables as $table ) {
            switch ($table) {
                case 'description':
                    $args = array( 'class' => "description", 'name' => "description_", 'placeholder' => "description", 'form' => "thisform", 'rows' => "3",  'cols' => "63", "maxlength" => "16383" );
                    $list = $this->descriptionList;
                    break;
                case 'ingredients':
                    $args = array( 'class' => "ingredient", 'name' => "ingredient_", 'placeholder' => "ingredient", 'form' => "thisform", 'rows' => "1",  'cols' => "63", "maxlength" => "127" );
                    $list = $this->ingredientList;
                    break;
                case 'steps':
                    $args = array( 'class' => "step", 'name' => "step_", 'placeholder' => "step", 'form' => "thisform", 'rows' => "1",  'cols' => "63", "maxlength" => "1279" );
                    $list = $this->stepList;
                    break;
            }

            $args['heading'] = $table;
            $this->build_list_fields($list, $args);
        }

        // close form

        print <<<EOF
            <!-- form method="POST" id="thisform" action="$action" -->
                <input name="headings" value="" type="hidden"/>
                <input name="descriptions" value="" type="hidden"/>
                <input name="ingredients" value="" type="hidden"/>
                <input name="steps" value="" type="hidden"/>
                <input name="submit" value="submit" type="submit" class="btn btn-info"/>
            </form>
            </br>
EOF;
        print '</div>';
    }
    
    private function build_list_fields ( $recipeList, $args ) {
        // for AddRecipe, there will be no values, so $recipeList will arrive null
        // One blank elemet will create the input field as desired.
        if ( ! $recipeList ) {
            $recipeList = array('');
        }

        $outerClass  = $args['class'] ."s";
        $innerClass  = $args['class'];
        $baseName    = $args['name'];
        $placeHolder = $args['placeholder'];
        $form        = $args['form'];
        $rows        = $args['rows'];
        $cols        = $args['cols'];
        $maxlength   = $args['maxlength'];
        $heading     = ($args['heading']?$args['heading'].":":""); 

        print <<<EOF
                <h4>$heading</h4>
                <div class="$outerClass">
EOF;

        $ctr = 0;
        foreach ( $recipeList as $listItem ) {
            $ctr ++;
            $name = $baseName . $ctr;
            print <<<EOF
                    <div class="$innerClass form-group input-group">
                        <textarea class="$innerClass form-control" name="$name" placeholder="$placeHolder" form="thisform" rows="$rows" cols="$cols" maxlenght="$maxlength">$listItem</textarea>
                        <span class="hidden-xs hidden-sm input-group-addon">
                            <button type="button" class="plus btn btn-default btn-sm">
                                <span class="glyhpicon glyphicon-plus"></span>
                            </button>
                            <button type="button" class="minus btn btn-default btn-sm">
                                <span class="glyhpicon glyphicon-minus"></span>
                            </button>
                        </span>
                        <span class="hidden-lg hidden-md input-group-addon">
                            <button type="button" class="plus btn btn-default btn-xs">
                                <span class="glyhpicon glyphicon-plus"></span>
                            </button>
                            <button type="button" class="minus btn btn-default btn-xs">
                                <span class="glyhpicon glyphicon-minus"></span>
                            </button>
                        </span>
                    </div>
EOF;

        }

        print <<<EOF
                    <br/>
                </div>
EOF;
    }

    private function build_header_info () {
        print <<<EOF
        <h3>Recipe:</h3>
        <div class="headings">
            <textarea type="text" maxlength="63" class="heading form-control" name="heading" placeholder="Title" form="thisform" rows="1" cols="63">$this->recipeTitle</textarea>
            <br/>
            <br/>

            <textarea type="text" maxlength="63" class="heading form-control" name="name" placeholder="Recipe Name" form="thisform" rows="1" cols="63">$this->recipeName</textarea>
            <br/>
            <br/>

            <textarea type="text" maxlength="127" class="heading form-control" name="author" placeholder="Author" form="thisform" rows="1" cols="63">$this->recipeAuthor</textarea>
            <br/>
            <br/>

            <textarea type="text" maxlength="10" class="heading form-control" name="date" placeholder="Date (MM/DD/YYYY)" form="thisform" rows="1" cols="63">$this->recipeDate</textarea>
            <br/>
            <br/>
        </div>
        <br/>

EOF;
    }

    // Building an array from the input values is easier than I expected!
    protected function build_result_array( $fieldName ) {
        $fields = array();

        // get the list of keys that match the pattern of the field in question and add each to return array
        $theseKeys = preg_grep("/^{$fieldName}_/", array_keys($_POST));
        foreach ( $theseKeys as $key ) {
            $fields[] = htmlentities($_POST[$key]);
        }

        return $fields;
    }

    public function validate_fields () {
        global $db;
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            // add header info to private attributes.
            $this->recipeTitle  = htmlentities($_POST["heading"]);
            $this->recipeName   = htmlentities($_POST["name"]);
            $this->recipeAuthor = htmlentities($_POST["author"]);
            $this->recipeDate   = htmlentities($_POST["date"]);

            if ( ! ( $this->recipeTitle && $this->recipeName && $this->recipeAuthor && $this->recipeDate ) ) {
                $this->add_warning("Validation Failed<br/>Please make sure all fields are populated");
                return FALSE;
            }

            $this->set_recipe_id();
            // MISSING:
            // verify $this->recipeId exists!

            $verifyString = $this->get_verify_query();

            try { 
                $result = $db->query($verifyString);
                $rows = $result->fetchAll();
            } catch (PDOException $e) {
                $this->add_warning("Couldn't query recipe: '{$e->getMessage()}'");
                return FALSE;
            }

            // if recipe exists with this name and isn't the recipe being edited...
            if ( $rows[0] ) {
                $this->add_warning("'$this->recipeName' already exists in table recipe");
                return FALSE;
            }

            // Build arrays of description/ingredient/step inputs
            $this->descriptionList = $this->build_result_array('description');
            $this->ingredientList  = $this->build_result_array('ingredient');
            $this->stepList        = $this->build_result_array('step');

            // verify at least one of each exists
            if ( ! ( $this->descriptionList[0] && $this->ingredientList[0] && $this->stepList[0] ) ) {
                $this->add_warning("Please make sure all fields are populated");
                return FALSE;
            }
        }

        return TRUE;
    }
}
