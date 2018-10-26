<?php
    @session_start();
    ini_set("display_errors","Off");
	ini_set("session.cookie_httponly", 1);   
	header('X-Frame-Options:SAMEORIGIN');
    
    static $DB_ADDR="localhost";
    static $DB_MNAM="XCOJ";
    static $DB_USER="";
    static $DB_PASS="";
    
    static $OJ_MAIN="XCOJ";
    static $OJ_HOME="./";
    static $OJ_ADMIN="root@localhost";
    static $OJ_DATA="/home/xcoj/data";
    static $OJ_JDGIP="127.0.0.1";
    static $OJ_RQ_Mail=true;
    static $OJ_RQ_PHONEN=false;
    
    static $OJ_LGIN_QQ=false;
    static $OJ_LGIN_WEIBO=false;
    
    static $OJ_KILL=false;
?>