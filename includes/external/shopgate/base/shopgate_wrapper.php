<?php

/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
 */
class ShopgateWrapper
{

    /**
     * Wraps for example: xtc_db_prepare_input
     *
     * @param string $input
     *
     * @return string
     */
    public static function db_prepare_input($input)
    {
        if (defined('PROJECT_MAJOR_VERSION')) {
            return xtc_db_input($input);
        } else {
            return xtc_db_prepare_input($input);
        }
    }

    /**
     * @param string $db_query
     *
     * @return mixed
     */
    public static function db_fetch_array($db_query)
    {
        return xtc_db_fetch_array($db_query);
    }

    /**
     * @param        $query
     * @param string $link
     *
     * @return mixed
     */
    public static function db_query($query, $link = 'db_link')
    {
        return xtc_db_query($query, $link);
    }

    /**
     * Checks if the column exists within the table
     *
     * @param $table
     * @param $column
     *
     * @return bool
     */
    public static function db_column_exists($table, $column)
    {
        $query  = "SHOW COLUMNS FROM {$table} LIKE '{$column}';";
        $result = xtc_db_query($query);

        return (bool)xtc_db_num_rows($result);
    }
}
