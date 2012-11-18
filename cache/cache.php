<?php
/**
 * @Author: Kenyon Haliwell
 * @URL: http://battleborndevelopment.com/
 * @Date Created: 3/6/11
 * @Date Modified: 3/6/11
 * @Purpose: Used to extend & simplify the abilities of APC (chaching)
 * @Version: 1.0
 */

/**
 * @Purpose: Purpose of the class/function (unless it's a variable, then omit)
 *
 * You MUST install the APC extension for this plugin to work (http://php.net/manual/en/apc.installation.php)pec!
 *
 * USAGE:
 *  Front controller (framework caching):
 *      Sets the cache to be prefixed with framework_ and expire every 24 hours
 *      $system_di->cache = new cache($system_di, 'framework_', '86400');
 *
 *  Extended Caching
 *      $var_to_hold_cache = new cache(<dependency injector>, <prefix>, <time to live>);
 */
class cache
{
    /**
    * @Var: Object
    * @Access: Private
    */
    private $system_di;
    
    /**
     * @Var: String
     * @Access: Private
     */
    private $_prefix;
    
    /**
     * @Var: Integer
     * @Access: Private
     */
    private $_ttl;
    
    /**
     * @Purpose: Load dependencyInjector into scope; Also set prefix and time to live
     * @Param: object $system_di
     * @Param: string $prefix
     * @Param: integer $ttl
     * @Access: Public
     */
    public function __construct($system_di, $prefix, $ttl) {
        $this->system_di = $system_di;
        $this->_prefix = $prefix;
        $this->_ttl = $ttl;
    }//End __construct;
    
    /**
     * @Purpose: Allow object style variable (cache) setting/updating
     * @Param: string $key
     * @Param: string $value
     * @Access: Public
     */
    public function __set($key, $value) {
        apc_store($this->_prefix . $key, $value, $this->_ttl);
    }//End __set
    
    /**
     * @Purpose: Allow object style variable (cache) access
     * @Param: string $key
     * @Access: Public
     * @Return: Returns the value of the cache referenced by $key
     */
    public function __get($key) {
        $apc_query = apc_fetch($this->_prefix . $key);
        return $apc_query;
    }//End __get
    
    /**
     * @Purpose: Allow checking if the variable (cache) is set
     * @Param: string $key
     * @Access: Public
     * @Return: Boolean
     */
    public function __isset($key) {
        if (apc_exists($this->_prefix . $key)) {
            return true;
        } else {
            return false;
        }
    }//End __isset
    
    /**
     * @Purpose: Allow unsetting of variable (cache)
     * @Param: string $key
     * @Access: Public
     */
    public function __unset($key) {
        if (apc_exists($this->_prefix . $key)) {
            apc_delete($this->_prefix . $key);
        }
    }//End __unset
    
    /**
     * @Purpose: Used for debugging, print out everything stored in cache
     * @Access: Public
     */
    public function dump() {
        if ('dev' === __PROJECT_ENVIRONMENT) {
            echo '<fieldset class="system_alert"><legend>Current Cache Values</legend><pre>' . print_r(apc_cache_info('user'), true) . '</pre></fieldset>';
        }
    }//End dump
}//End cache
//End File
