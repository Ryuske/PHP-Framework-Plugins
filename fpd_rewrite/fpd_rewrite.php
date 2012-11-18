<?php
/**
 * @Purpose: Used to rewrite Full Path Disclosure errors to relative paths (i.e. more secrecy/security)
 * @Param: mixed $full_path
 * @Static
 * @Return: Rewritten path
 */
function fpd_rewrite(&$full_path)
{
    if (is_array($full_path)) {
        foreach(array_keys($full_path) as $key) {
            if ((is_array($full_path[$key]) || is_object($full_path[$key])) && 0 < count($full_path[$key])) {
                self::fpd_rewrite($full_path[$key]);
            } else {
                $full_path[$key] = str_replace(__BASE_PATH, '/', $full_path[$key]);
            }
        }
    } elseif (is_object($full_path)) {
        foreach ($full_path as $key => $value) {
            $is_recursive = true;
            $loop_identifier = $value;
            $loop_key = $key;
            $loop_value = $value;
            if (is_object($loop_identifier)) {
                while ($is_recursive) {
                    foreach ($loop_identifier as $loop_key => $loop_value) {
                        if (is_object($loop_identifier->$loop_key) && 1 < count(get_class_vars($loop_key))) {
                            $loop_identifier = $value;
                            $key = $loop_key;
                            $value = $loop_value;
                        } elseif(is_object($loop_identifier->$loop_key) && 2 < count(get_class_vars($loop_key))) {
                            $is_recursive = false;
                            self::fpd_rewrite($loop_key);
                        } else {
                            $is_recursive = false;
                            self::fpd_rewrite($loop_value);
                        }
                    }
                }
            } else {
                self::fpd_rewrite($loop_identifier);
            }
        }
    } else {
        $full_path = str_replace(__BASE_PATH, '/', $full_path);
    }
    return $full_path;
}//End fpd_rewrite
