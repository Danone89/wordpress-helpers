<?php
/**
 * Allow access to every endpoint by zabbix IP
 */


add_filter('rest_authentication_errors', function ($result) {
    if (defined('ZABBIX_IP') && $_SERVER['REMOTE_ADDR'] === ZABBIX_IP) {
        return true;
    }
    return $result;
}, PHP_INT_MAX);