<?php
/**
 * Glossword - glossary compiler (http://glossword.biz/)
 * � 2008-2021 Glossword.biz team <team at glossword dot biz>
 * � 2002-2008 Dmitry N. Shilnikov
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  (see `http://creativecommons.org/licenses/GPL/2.0/' for details)
 */
// --------------------------------------------------------
if ( ! defined('IN_GW')) {
    die('<!-- Not in App  -->');
}
// --------------------------------------------------------
if ( ! class_exists('gw_query_storage')) {

    class gw_query_storage
    {

        public $str_suffix = '';
        public $is_loaded = 0;
        public $arQ = array();

        /* */
        public function set_suffix($v)
        {
            $this->str_suffix = $v;
        }

        /* */
        public function q_import($ar = array())
        {
            global $sys;
            $arSql = array();
            if ($this->is_loaded) {
                return $this->arQ;
            }
            foreach ($ar as $k => $v) {
                $tmp = array();
                if (file_exists($sys['path_include'] . '/' . $v . $this->str_suffix . '.php')) {
                    include($sys['path_include'] . '/' . $v . $this->str_suffix . '.php');
                    $arSql = array_merge($arSql, $tmp['ar_queries']);
                }
            }
            $this->is_loaded = 1;
            $this->arQ       =& $arSql;

            return $arSql;
        }

        /* */
        public function setCustomQ()
        {
            return array();
        }

        /* */
        public function setQ()
        {
            return $this->q_import(array('query_storage_global'));
        }

        /* */
        public function getQ()
        {
            $args = func_get_args();
            $ar   = array();
            /* 8 parameters allowed */
            /* See also `return sprintf' at the end of the function */
            for ($i = 0; $i <= 8; $i++) {
                $ar[] = isset($args[$i]) ? $args[$i] : '';
            }
            $arSql = array_merge($this->setQ(), $this->setCustomQ());
            if (isset($arSql[$ar[0]])) {
                $arSql[$ar[0]] = str_replace(array("\n", "\r", "\t", "  "), ' ', $arSql[$ar[0]]);

                // Count format specifiers in the query
                $specifier_count = preg_match_all('/%[sd]/', $arSql[$ar[0]], $matches);
                // Build args array based on actual specifiers needed
                $args = array();
                for ($i = 1; $i <= $specifier_count && $i < 9; $i++) {
                    $args[] = $ar[$i];
                }
                return vsprintf($arSql[$ar[0]], $args);
            }

            return '';
        }
    } /* end of class */
}
?>