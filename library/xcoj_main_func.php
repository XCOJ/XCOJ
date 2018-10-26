<?php
    function phash($pass){
        return sha1(sha1($pass,false),false);
    }