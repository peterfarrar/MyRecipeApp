<?php
class SetUp extends Page {
    private $mysqlRootUser;
    private $mysqlRootPass;
    private $adminUser;
    private $adminPass;
    private $dbName = 'recipes'; // default database name is 'recipes'

    // This page should only be open to logged in users.
    public function show_page() {
        global $db;
        $action = $_SERVER['PHP_SELF'] ."?". $_SERVER['QUERY_STRING'];

        $this->start_page();

        if ( file_exists('config.php') ) {
            print "<h1 align='center'>Set Up Complete!</h1>";
        } else {
        // create input form
            print <<<EOF
                <p>
                <h1 align='center'>Set Up Page</h1>
                </p>
                <p>
                <h3 align='center'>It doesn't appear that there is a config file, and probably not a database or tables.</h3>
                </p>
                <p>
                <h3 align='center'>Please fill out the forms below to set up and create the config file and the admin user.</h3>
                </p>
                
                <form name="initForm" method="POST" action="$action">
                    <table border="0" cellpadding="10" cellspacing="1" width="500" align="center" class="tblLogin">
                        <tr class="tableheader">
                        <td align="left" colspan="2">MySQL Root User</td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="text" name="mysqlRootUser" placeholder="Add MySQL Root User Name" class="login-input"></td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="password" name="mysqlRootPass" placeholder="Enter MySQL Root Password" class="login-input"></td>
                        </tr>
                    </table>
                    <p/>
                    <table border="0" cellpadding="10" cellspacing="1" width="500" align="center" class="tblLogin">
                        <tr class="tableheader">
                        <td align="left" colspan="2">ThisApplication MySql User</td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="text" name="mysqlUser" placeholder="Add Admin User Name" class="login-input"></td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="password" name="mysqlPass1" placeholder="Enter Admin Password" class="login-input"></td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="password" name="mysqlPass2" placeholder="Re-enter Admin Password" class="login-input"></td>
                        </tr>
                    </table>
                    <p/>
                    <table border="0" cellpadding="10" cellspacing="1" width="500" align="center" class="tblLogin">
                        <tr class="tableheader">
                        <td align="left" colspan="2">Create Admin User</td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="text" name="adminUser" placeholder="Add Admin User Name" class="login-input"></td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="password" name="adminPass1" placeholder="Enter Admin Password" class="login-input"></td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="password" name="adminPass2" placeholder="Re-enter Admin Password" class="login-input"></td>
                        </tr>
                    </table>
                    <p/>
                    <table border="0" cellpadding="10" cellspacing="1" width="500" align="center" class="tblLogin">
                        <tr class="tableheader">
                        <td align="left" colspan="2">Choose Database Name (Optional)</td>
                        </tr>
                        <tr class="tablerow">
                        <td>
                        <input type="text" name="dbName" placeholder="Database Name (Optional, default is '{$this->dbName}')" class="login-input"></td>
                        </tr>
                        <tr class="tableheader">
                        <td align="left" colspan="2"></br><input type="submit" name="submit" value="Submit" class="btnSubmit"></td>
                        </tr>
                    </table>
                </form>
EOF;
        }
        $this->end_page();
    }

    public function validate_fields () {
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            global $db;
            $mysqlRootUser = htmlentities($_POST["mysqlRootUser"]);
            $mysqlRootPass = htmlentities($_POST["mysqlRootPass"]);
            $mysqlUser     = htmlentities($_POST["mysqlUser"]);
            $mysqlPass1    = htmlentities($_POST["mysqlPass1"]);
            $mysqlPass2    = htmlentities($_POST["mysqlPass2"]);
            $adminUser     = htmlentities($_POST["adminUser"]);
            $adminPass1    = htmlentities($_POST["adminPass1"]);
            $adminPass2    = htmlentities($_POST["adminPass2"]);
            $dbName        = htmlentities($_POST["dbName"]);

            if ( ! ( $mysqlRootUser && $mysqlRootPass &&
                $mysqlUser && $mysqlPass1 && $mysqlPass2 &&
                $adminUser && $adminPass1 && $adminPass2 ) ) {
                $this->add_warning("Validation Failed<br/>Please make sure all required fields are populated.");
                return FALSE;
            }

            if ( $mysqlPass1 !== $mysqlPass2 ) {
                $this->add_warning("Validation Failed<br/>Mysql User passwords don't match.");
                return FALSE;
            }

            if ( $adminPass1 !== $adminPass2 ) {
                $this->add_warning("Validation Failed<br/>Admin passwords don't match.");
                return FALSE;
            }

            if ( ! $dbName ) {
                $dbName = $this->dbName;
            }

            try {
                $db = new PDO("mysql:host=localhost","$mysqlRootUser","$mysqlRootPass");
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
            } catch (PDOException $e){
                $this->add_warning("Couldn't connect to the database: '{$e->getMessage()}'");
                return FALSE;
            }

            $this->mysqlRootUser = $mysqlRootUser;
            $this->mysqlRootPass = $mysqlRootPass;
            $this->mysqlUser     = $mysqlUser;
            $this->mysqlPass     = $mysqlPass1;
            $this->adminUser     = $adminUser;
            $this->adminPass     = $adminPass1;
            $this->dbName        = $dbName;

            // Need to validate mysql username.  If MySQL already has a user by that name, 
            // it will not work and we will have a DB and table, but no admin user
            if ( $this->validate_mysql_user() === FALSE ) {
                return FALSE;
            }
        }

        return TRUE;
    }

    private function validate_mysql_user () {
        global $db;

        $verifyString = "SELECT User, authentication_string FROM mysql.user where User = {$this->db_quote($this->mysqlUser)}";
        
        try { 
            $result = $db->query($verifyString);
            $rows = $result->fetchAll();
        } catch (PDOException $e) {
            $this->add_warning("Couldn't validate MySQL User: '{$e->getMessage()}'");
            return FALSE;
        }

        if ( $rows[0] ) {
            if ( $row[0]['authentication_string'] === md5( $this->mysqlPass1 ) ) {
                return TRUE;
            }
            return FALSE;
        }

        return TRUE;
    }

    private function create_db () {
        global $db;
        $dbName    = $this->dbName;
        $mysqlUser = $this->mysqlUser;
        $mysqlPass = $this->mysqlPass;

        $createStrings = array(
            "CREATE DATABASE $dbName",
            "CREATE USER '$mysqlUser'@'localhost' IDENTIFIED BY '$mysqlPass'",
            "GRANT ALL PRIVILEGES ON $dbName.* TO '$mysqlUser'@'localhost'");

        // create DB and admin user
        foreach ( $createStrings as $createString ) {
            try {
                $result = $db->exec($createString);
            } catch (PDOException $e) {
                return FALSE;
            }
        }

        if ( $db->query("use $dbName") === FALSE ) {
            $this->add_warning("Failed to create database '$dbName'");
            return FALSE;
        }

        return TRUE;
    }

    private function create_tables() {
        global $db;
        // create array of table create statements to cycle through
        $tableList = array();
        $tableList[] = "CREATE TABLE recipe (id MEDIUMINT NOT NULL AUTO_INCREMENT, title CHAR(63) NOT NULL, heading CHAR(63) NOT NULL, author CHAR(127) NOT NULL, date DATE, user CHAR(63), PRIMARY KEY (id));";
        $tableList[] = "CREATE TABLE description (recipe_id MEDIUMINT NOT NULL, text_no MEDIUMINT NOT NULL, text VARCHAR(16383) NOT NULL, PRIMARY KEY (recipe_id, text_no));";
        $tableList[] = "CREATE TABLE ingredients (recipe_id MEDIUMINT NOT NULL, ingredient_no MEDIUMINT NOT NULL, ingredient CHAR(127) NOT NULL, PRIMARY KEY (recipe_id, ingredient_no));";
        $tableList[] = "CREATE TABLE steps (recipe_id MEDIUMINT NOT NULL, step_no MEDIUMINT NOT NULL ,step VARCHAR(1279) NOT NULL, PRIMARY KEY (recipe_id, step_no));";
        $tableList[] = "CREATE TABLE user (user CHAR(63) NOT NULL UNIQUE, password CHAR(32) NOT NULL, PRIMARY KEY (user))";

        $tableList[] = "CREATE TABLE categories (id MEDIUMINT NOT NULL AUTO_INCREMENT, category CHAR(63) NOT NULL, PRIMARY KEY (id));";
        $tableList[] = "CREATE TABLE recipe_categories (recipe_id MEDIUMINT NOT NULL, categories_id MEDIUMINT NOT NULL);";
        // cycle through tableList to create tables.  Exit with FALSE on error

        foreach ( $tableList as $createTable ) {
            try {
                $result = $db->exec($createTable);
            } catch (PDOException $e) {
                return FALSE;
            }
        }

        return TRUE;
    }

    private function create_config_file ( $configFilename ) {
        $configContents = <<<EOF
            <?php
            \$dbHost='localhost';
            \$dbName='{$this->dbName}';
            \$dbUser='{$this->mysqlUser}';
            \$dbPass='{$this->mysqlPass}';
EOF;
        preg_replace('/^\s+', '', $configContents);

        $result = file_put_contents($configFilename, $configContents);
        if ( $result === FALSE ) {
            // Rather than die here, It might be nice to create the file for the user to download for themselves, 
            // in case they'd like to push it to the root directory themselves.
            $this->add_warning("Unable to create config file.  This is most likely a permissions problem.  Check file system permissions, delete database and user, and try again");
            return FALSE;
        }
    }

    private function add_admin_to_user_table() {
        $insertString = "INSERT INTO user (user, password) VALUES (". $this->db_quote($this->adminUser) .", '". md5($this->adminPass) ."')"; 

        $result = $this->run_exec( $insertString );

        return $result !== FALSE;
    }

    private function run_exec( $sqlString ) {
        global $db;

        try {
            $affectedRows = $db->exec($sqlString);
        } catch (PDOException $e) {
            $this->add_warning("Call to database failed: '{$e->getMessage()}'");
            throw $e;
        }
        if ( $affectedRows === 0 ) {
            return FALSE;
        }

        return TRUE;
    }

    public function process_data() {
        global $db;

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            // Check to see if DB exists, and create it and tables and admin user if not
            $dbName = $this->dbName;
            $results = $db->query("use $dbName");

            if ( ! $results ) {
                $this->create_db();
                $this->create_tables();
            }

            // populate user table
            $this->add_admin_to_user_table();

            // create config file
            $this->create_config_file('config.php');
            $this->setup = TRUE;
        }

        return TRUE;
    }
}
