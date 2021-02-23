<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author kenny
 */
class Config {

    public static function get($path = NULL) {
        if ($path) {
            $config = $GLOBALS['config'];
            $path = explode('/', $path);// explode convert an string to array while implode does the oppsite
            foreach ($path as $value) {
                if (isset($config[$value])) {
                    $config = $config[$value];
                }
            }
            return $config;
        }
        return FALSE;
    }

}
