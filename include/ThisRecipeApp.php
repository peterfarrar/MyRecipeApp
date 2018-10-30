<?php
/*
 * Peter Farrar 20170316
 *
 * Playing with OOP, POST, and using query strings
 * to shape an app for storing recipes.
 */

/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

class ThisRecipeApp extends ThisApp {
    public function run () {
        require_once 'config.php';
        require_once 'include/pdo.php';
        require_once 'page/Page.php';

        // check URL for which page this is (or home page)
        $queryString = $_SERVER['QUERY_STRING'];
        if ( $queryString ) {
            parse_str($queryString, $queryArgs);
            $page = $queryArgs['page'];
        } else {
            $page = 'index';
        }

        // attempt to create a class based on the page attribute from the URL (GET)
        $pageObj = Page::get_page($page);

        // validate fields 
        if ( $pageObj->validate_fields() ) {
            // process form and BD data
            $pageObj->process_data();
        }

        // display rest of page
        $pageObj->show_page();

        exit(0);
    }
}
