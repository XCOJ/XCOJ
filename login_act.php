<?php
    require_once("./library/xcoj_main_lib.php");
    require_once("./library/xcoj_main_func.php");
    $usern=$_POST['username'];
    $pwd=$_POST['passwd'];
    $pwdh=phash($pwd);
    $pwd="";
    
    $db = new MySQLi($DB_ADDR,$DB_USER,$DB_PASS,$DB_MNAM);
    mysqli_connect_error()?die("连接失败"):"";
    $sql = "select password from login where username='{$usern}'";
    $result = $db->query($sql);
    $n = $result->fetch_row();
    if($uid !="" && $pwdh !="")
    {
        if($n[0]==$pwdh)
        {
            $_SESSION['login_b']=true;
            $_SESSION['userna']=$usern;
            header("location:main.php");
        }
        else
        {
        echo "用户名或密码错误";
        }
    }
    else
    {
    echo "用户名或密码不能为空";
    }
?>   