<?php

/**
 * Class BillpayDB
 * Static one-liners that simplifies DB interaction.
 */
class BillpayDB {

    /**
     * Returns single value from a DB query.
     * @param string $query
     * @return mixed
     * @static
     */
    static function DBFetchValue($query)
    {
        $arr = xtc_db_fetch_array(xtc_db_query($query));
        if (!is_array($arr)) return null;
        return array_pop($arr);
    }

    /**
     * Returns single row from DB query
     * @param string $query
     * @return array|bool|mixed
     * @static
     */
    static function DBFetchRow($query)
    {
        $arr = xtc_db_fetch_array(xtc_db_query($query));
        return $arr;
    }

    /**
     * Returns whole table from DB query
     * @param   string  $query
     * @return  array
     */
    static function DBFetchArray($query)
    {
        $return = array();
        $res = xtc_db_query($query);
        while ($arr = xtc_db_fetch_array($res)) {
            $return[] = $arr;
        }
        return $return;
    }
}