<?php

/**
 * Class BillpayDB
 * Static one-liners that simplifies DB interaction.
 */
class BillpayDB {

    /**
     * Used to execute query we don't expect answer.
     * @param $query
     * @return bool|resource
     */
    public static function DBQuery($query)
    {
        return xtc_db_query($query);
    }

    /**
     * Returns single value from a DB query.
     * @param string $query
     * @return mixed
     * @static
     */
    public static function DBFetchValue($query)
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
    public static function DBFetchRow($query)
    {
        $arr = xtc_db_fetch_array(xtc_db_query($query));
        return $arr;
    }

    /**
     * Executes query without caching results.
     * @param $query
     * @return array|bool|mixed
     */
    public static function DBFetchRowNonCached($query)
    {
        $arr = xtc_db_fetch_array(xtc_db_query($query, 'db_link', false));
        return $arr;
    }

    /**
     * Returns whole table from DB query
     * @param   string  $query
     * @return  array
     */
    public static function DBFetchArray($query)
    {
        $return = array();
        $res = xtc_db_query($query);
        while ($arr = xtc_db_fetch_array($res)) {
            $return[] = $arr;
        }
        return $return;
    }

    public static function DBCount($query)
    {
        return xtc_db_num_rows(xtc_db_query($query));
    }
}