<?php
class MySendMail {
    protected $_userName;
    protected $_password;
    protected $_sendServer;
    protected $_port;
    protected $_from;
    protected $_to = array();
    protected $_cc = array();
    protected $_bcc = array();
    protected $_subject;
    protected $_body;
    protected $_attachment = array();
    protected $_socket;
    protected $_isSecurity;
    protected $_errorMessage;
    public function setServer($server, $username="", $password="", $port=25, $isSecurity=false) {
        $this->_sendServer = $server;
        $this->_port = $port;
        $this->_isSecurity = $isSecurity;
        $this->_userName = empty($username) ? "" : base64_encode($username);
        $this->_password = empty($password) ? "" : base64_encode($password);
        return true;
    }
    public function setFrom($from) {
        $this->_from = $from;
        return true;
    }
    public function setReceiver($to) {
        $this->_to[] = $to;
        return true;
    }
    public function setCc($cc) {
        $this->_cc[] = $cc;
        return true;
    }
    public function setBcc($bcc) {
        $this->_bcc[] = $bcc;
        return true;
    }
    public function addAttachment($file) {
        if(!file_exists($file)) {
            $this->_errorMessage = "file " . $file . " does not exist.";
            return false;
        }
        $this->_attachment[] = $file;
        return true;
    }
    public function setMail($subject, $body) {
        $this->_subject = base64_encode($subject);
        $this->_body = base64_encode($body);
        return true;
    }
    public function sendMail() {
        $command = $this->getCommand();
        $this->_isSecurity ? $this->socketSecurity() : $this->socket();
        foreach ($command as $value) {
            $result = $this->_isSecurity ? $this->sendCommandSecurity($value[0], $value[1]) : $this->sendCommand($value[0], $value[1]);
            if($result) {
                continue;
            }
            else{
                return false;
            }
        }
        $this->_isSecurity ? $this->closeSecutity() : $this->close();
        return true;
    }
    public function error(){
        if(!isset($this->_errorMessage)) {
            $this->_errorMessage = "";
        }
        return $this->_errorMessage;
    }
    protected function getCommand() {
        $separator = "----=_Part_" . md5($this->_from . time()) . uniqid(); 
        $command = array(
                array("HELO sendmail\r\n", 250)
            );
        if(!empty($this->_userName)){
            $command[] = array("AUTH LOGIN\r\n", 334);
            $command[] = array($this->_userName . "\r\n", 334);
            $command[] = array($this->_password . "\r\n", 235);
        }
        $command[] = array("MAIL FROM: <" . $this->_from . ">\r\n", 250);
        $header = "FROM: <" . $this->_from . ">\r\n";
        if(!empty($this->_to)) {
            $count = count($this->_to);
            if($count == 1){
                $command[] = array("RCPT TO: <" . $this->_to[0] . ">\r\n", 250);
                $header .= "TO: <" . $this->_to[0] .">\r\n";
            }
            else{
                for($i=0; $i<$count; $i++){
                    $command[] = array("RCPT TO: <" . $this->_to[$i] . ">\r\n", 250);
                    if($i == 0){
                        $header .= "TO: <" . $this->_to[$i] .">";
                    }
                    elseif($i + 1 == $count){
                        $header .= ",<" . $this->_to[$i] .">\r\n";
                    }
                    else{
                        $header .= ",<" . $this->_to[$i] .">";
                    }
                }
            }
        }
        if(!empty($this->_cc)) {
            $count = count($this->_cc);
            if($count == 1){
                $command[] = array("RCPT TO: <" . $this->_cc[0] . ">\r\n", 250);
                $header .= "CC: <" . $this->_cc[0] .">\r\n";
            }
            else{
                for($i=0; $i<$count; $i++){
                    $command[] = array("RCPT TO: <" . $this->_cc[$i] . ">\r\n", 250);
                    if($i == 0){
                    $header .= "CC: <" . $this->_cc[$i] .">";
                    }
                    elseif($i + 1 == $count){
                        $header .= ",<" . $this->_cc[$i] .">\r\n";
                    }
                    else{
                        $header .= ",<" . $this->_cc[$i] .">";
                    }
                }
            }
        }
        if(!empty($this->_bcc)) {
            $count = count($this->_bcc);
            if($count == 1) {
                $command[] = array("RCPT TO: <" . $this->_bcc[0] . ">\r\n", 250);
                $header .= "BCC: <" . $this->_bcc[0] .">\r\n";
            }
            else{
                for($i=0; $i<$count; $i++){
                    $command[] = array("RCPT TO: <" . $this->_bcc[$i] . ">\r\n", 250);
                    if($i == 0){
                    $header .= "BCC: <" . $this->_bcc[$i] .">";
                    }
                    elseif($i + 1 == $count){
                        $header .= ",<" . $this->_bcc[$i] .">\r\n";
                    }
                    else{
                        $header .= ",<" . $this->_bcc[$i] .">";
                    }
                }
            }
        }
        $header .= "Subject: =?UTF-8?B?" . $this->_subject ."?=\r\n";
        if(isset($this->_attachment)) {
            $header .= "Content-Type: multipart/mixed;\r\n";
        }
        elseif(false){
            $header .= "Content-Type: multipart/related;\r\n";
        }
        else{
            $header .= "Content-Type: multipart/alternative;\r\n";
        }
        $header .= "\t" . 'boundary="' . $separator . '"';
        $header .= "\r\nMIME-Version: 1.0\r\n";
        $header .= "\r\n--" . $separator . "\r\n";
        $header .= "Content-Type:text/html; charset=utf-8\r\n";
        $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $header .= $this->_body . "\r\n";
        $header .= "--" . $separator . "\r\n";
        if(!empty($this->_attachment)){
            $count = count($this->_attachment);
            for($i=0; $i<$count; $i++){
                $header .= "\r\n--" . $separator . "\r\n";
                $header .= "Content-Type: " . $this->getMIMEType($this->_attachment[$i]) . '; name="=?UTF-8?B?' . base64_encode( basename($this->_attachment[$i]) ) . '?="' . "\r\n";
                $header .= "Content-Transfer-Encoding: base64\r\n";
                $header .= 'Content-Disposition: attachment; filename="=?UTF-8?B?' . base64_encode( basename($this->_attachment[$i]) ) . '?="' . "\r\n";
                $header .= "\r\n";
                $header .= $this->readFile($this->_attachment[$i]);
                $header .= "\r\n--" . $separator . "\r\n";
            }
        }
        $header .= "\r\n.\r\n";
 
        $command[] = array("DATA\r\n", 354);
        $command[] = array($header, 250);
        $command[] = array("QUIT\r\n", 221);
        return $command;
    }
    protected function sendCommand($command, $code) {
        echo 'Send command:' . $command . ',expected code:' . $code . '<br />';
        try{
            if(socket_write($this->_socket, $command, strlen($command))){
                if(empty($code))  {
                    return true;
                }
                $data = trim(socket_read($this->_socket, 1024));
                echo 'response:' . $data . '<br /><br />';
                if($data) {
                    $pattern = "/^".$code."+?/";
                    if(preg_match($pattern, $data)) {
                        return true;
                    }
                    else{
                        $this->_errorMessage = "Error:" . $data . "|**| command:";
                        return false;
                    }
                }
                else{
                    $this->_errorMessage = "Error:" . socket_strerror(socket_last_error());
                    return false;
                }
            }
            else{
                $this->_errorMessage = "Error:" . socket_strerror(socket_last_error());
                return false;
            }
        }catch(Exception $e) {
            $this->_errorMessage = "Error:" . $e->getMessage();
        }
    }
    protected function sendCommandSecurity($command, $code) {
        echo 'Send command:' . $command . ',expected code:' . $code . '<br />';
        try {
            if(fwrite($this->_socket, $command)){
                if(empty($code))  {
                    return true;
                }
                $data = trim(fread($this->_socket, 1024));
                echo 'response:' . $data . '<br /><br />';
                if($data) {
                    $pattern = "/^".$code."+?/";
                    if(preg_match($pattern, $data)) {
                        return true;
                    }
                    else{
                        $this->_errorMessage = "Error:" . $data . "|**| command:";
                        return false;
                    }
                }
                else{
                    return false;
                }
            }
            else{
                $this->_errorMessage = "Error: " . $command . " send failed";
                return false;
            }
        }catch(Exception $e) {
            $this->_errorMessage = "Error:" . $e->getMessage();
        }
    } 
    protected function readFile($file) {
        if(file_exists($file)) {
            $file_obj = file_get_contents($file);
            return base64_encode($file_obj);
        }
        else {
            $this->_errorMessage = "file " . $file . " dose not exist";
            return false;
        }
    }
    protected function getMIMEType($file) {
        if(file_exists($file)) {
            $mime = mime_content_type($file);
            return $mime;
        }
        else {
            return false;
        }
    }
    protected function socket() {
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
        if(!$this->_socket) {
            $this->_errorMessage = socket_strerror(socket_last_error());
            return false;
        }
        socket_set_block($this->_socket);
        if(!socket_connect($this->_socket, $this->_sendServer, $this->_port)) {
            $this->_errorMessage = socket_strerror(socket_last_error());
            return false;
        }
        $str = socket_read($this->_socket, 1024);
        if(!preg_match("/220+?/", $str)){
            $this->_errorMessage = $str;
            return false;
        }
        return true;
    }
    protected function socketSecurity() {
        $remoteAddr = "tcp://" . $this->_sendServer . ":" . $this->_port;
        $this->_socket = stream_socket_client($remoteAddr, $errno, $errstr, 30);
        if(!$this->_socket){
            $this->_errorMessage = $errstr;
            return false;
        }
        stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
        stream_set_blocking($this->_socket, 1);
        $str = fread($this->_socket, 1024);
        if(!preg_match("/220+?/", $str)){
            $this->_errorMessage = $str;
            return false;
        }
        return true;
    }
    protected function close() {
        if(isset($this->_socket) && is_object($this->_socket)) {
            $this->_socket->close();
            return true;
        }
        $this->_errorMessage = "No resource can to be close";
        return false;
    }
    protected function closeSecutity() {
        if(isset($this->_socket) && is_object($this->_socket)) {
            stream_socket_shutdown($this->_socket, STREAM_SHUT_WR);
            return true;
        }
        $this->_errorMessage = "No resource can to be close";
        return false;
    }
}
?>