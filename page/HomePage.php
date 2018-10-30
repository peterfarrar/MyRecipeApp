<?php
class HomePage extends Page {
    protected $form;
    protected $username;

    // This page shouldn't be called directly... redirect to login page.
    public function show_page() {
        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?page=login");
        exit();
    }

    protected function validate_add_user_fields () {
        $username  = strtolower(htmlentities($_POST["username"]));
        $password  = htmlentities($_POST["password1"]);
        $password2 = htmlentities($_POST["password2"]);

        if ( $this->user_exists( $username ) ) {
            $this->add_warning("User '$username' already exists.");
            return FALSE;
        }

        if ( ! $this->passwords_match( $password, $password2 ) ) {
            $this->add_warning("Unable to add user '$username'.");
            $this->add_warning("Passwords don't match.");
            return FALSE;
        }

        $this->username = $username;
        $this->password = $password;

        return TRUE;
    }

    protected function validate_delete_user_fields () {
        $username  = strtoloser(htmlentities($_POST["username"]));

        if ( ! $this->user_exists( $username ) ) {
            $this->add_warning("User '$username' doesn't exists.");
            return FALSE;
        }

        $this->username = $username;

        return TRUE;
    }

    protected function validate_change_user_password_fields () {
        $username  = strtolower(htmlentities($_POST["username"]));
        $password  = htmlentities($_POST["password1"]);
        $password2 = htmlentities($_POST["password2"]);

        // avoid spoofing... maybe
        if ( $username !== $_SESSION[user] ) {
            if ( $_SESSION[user] !== 'admin' ) {
                $this->add_warning("User '$username' doesn't match logged in username of {$_SESSION[user]}.");
                return FALSE;
            }
        }

        if ( ! $this->user_exists( $username ) ) {
            $this->add_warning("User '$username' doesn't exists.");
            return FALSE;
        }

        if ( ! $this->passwords_match( $password, $password2 ) ) {
            $this->add_warning("Passwords don't match.");
            return FALSE;
        }

        $this->username = $username;
        $this->password = $password;

        return TRUE;
    }
    
    protected function passwords_match ( $p1, $p2 ) {
        return $p1 === $p2;
    }

    protected function user_exists ( $username ) {
        global $db;
        $username = strtolower($username);
        $verifyString = "SELECT user, password FROM user where user = {$this->db_quote($username)}";

        try { 
            $result = $db->query($verifyString);
            $rows = $result->fetchAll();
        } catch (PDOException $e) {
            return FALSE;
        }

        if ( ! $rows[0] ) {
            return FALSE;
        }

        return TRUE;
    }

    protected function validate_password ( $username, $password ) {
        global $db;
        $username = strtolower($username);
        $verifyString = "SELECT user, password FROM user where user = {$this->db_quote($username)}";

        try { 
            $result = $db->query($verifyString);
            $rows = $result->fetchAll();
        } catch (PDOException $e) {
            $this->add_warning("Couldn't validate User account: '{$e->getMessage()}'");
            return FALSE;
        }

        if ( $rows[0] ) {
            $md5password = md5( $password );
            if ( $rows[0]['password'] === $md5password ) {
                $this->username = $username;
                $this->password = $password;
            } else {
                $this->add_warning("Invalid username or password.");
                return FALSE;
            }
        } else {
            $this->add_warning("Invalid username or password.");
        }

        return TRUE;
    }

    protected function process_add_user () {
        $result = TRUE;
        $insertString = "INSERT INTO user (user, password) VALUES (". $this->db_quote($this->username) .", '". md5($this->password) ."')"; 

        $result = $this->run_exec( $insertString );

        if ( $result ) {
            $this->add_info("User '{$this->username}' has been created.");
        } else {
            $this->add_warning("Unable to add user {$this->username}.<br/>This should not happen. Please report this to your administrator.");
        }

        return $result;
    }

    protected function process_delete_user () {
        $result = TRUE;
        $deleteString = "DELETE FROM user WHERE user = {$this->db_quote($this->username)}";

        $result = $this->run_exec( $deleteString );
        if ( $result ) {
            $this->add_info("User '{$this->username}' has been deleted.");
        } else {
            $this->add_warning("Unable to delete user {$this->username}.<br/>This should not happen. Please report this to your administrator.");
        }

        return $result;
    }
    
    protected function process_change_user_password () {
        $result = TRUE;
        $updateString = "UPDATE user SET password = {$this->db_quote(md5($this->password))} WHERE user = {$this->db_quote($this->username)}";

        $result = $this->run_exec( $updateString );
        if ( $result ) {
            $this->add_info("Password changed for user '{$this->username}'.");
        } else {
            $this->add_warning("Unable to change password for user {$this->username}.<br/>This should not happen. Please report this to your administrator.");
        }

        return $result;
    }
    
    protected function process_log_out() {
        // log out
        $_COOKIE = array();
        setcookie(session_name(), '', time - 3600, "/");
        $_SESSION = array();
        session_destroy();
    }

    protected function run_exec ( $sqlString ) {
        // used when results of sql string are either true or false,
        // such as insert user, update password... 
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

    public function validate_fields () {
    }

    public function process_data () {
    }
}
