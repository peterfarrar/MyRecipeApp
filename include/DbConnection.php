<?php

// so this is kinda been ignored... this might be a good place to do DB stuff, like create, db_quote, db_clean... etc.
// instead of pushing that into Page class and sub classes like SetUp.
//
// I think as I clean this up, and esp. when API is added, architecture is going to change a lot!  This is doing a great
// job of being a place holder for the inevitable change.
//
// Thinking that all queries need to be passed here so that, if nothing else, memcached can be incorporated.
// 1) is it running?
// 2) if so, are the results stored?
// 3) if updating, do we need to delete a key/val from memcached?
//
/*
 * Perhaps instead of 'global $db', it's just '$db = Config::get_instance().get_config($this_env).get_val('DB');
 * ... it does seem a little wordy... I mean, isn't a Singleton just another fancy way to say 'global'?
 */
class DbConnection {
    public function connect() {
        global $db;
        global $dbHost;
        global $dbName;
        global $dbUser;
        global $dbPass;

        try {
            $db = new PDO("mysql:host=$dbHost;dbname=$dbname","$dbUser","$dbPass");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e){
                exit("<div class='failed text-warning'>Couldn't connect to the database: {$e->getMessage()}</div>\n");
            //buildConfig();
            createDatabase();
            createTables();
        }
    }

    private function db_clean( $field ) {
        // strtr and $db->quote are used to escape SQL wild card characters and to properly quote the where value
        return strtr($field, array('_' => '\_', '%' => '\%'));
    }

    private function db_quote( $field ) {
        global $db;
        // strtr and $db->quote are used to escape SQL wild card characters and to properly quote the where value
        return strtr($db->quote($field), array('_' => '\_', '%' => '\%'));
    }

    // verify that table exists
    function check_table($pdoObject, $tableName) {
        $selectStatement = "SELECT 1 from $tableName LIMIT 1";
        try {
            $result = $pdoObject->query($selectStatement);
        } catch (PDOException $e) {
            return FALSE;
        }

        return $result !== FALSE;
    }
}
