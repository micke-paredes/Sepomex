<?php
defined("ACCESS_SUCCESS") or header("location: ../error-403");
class DataBase{
    /*
        REPLACE THE CONSTANTS: 
            DB_HOST = 127.0.0.1 
            DB_USER = YOUR_USER_NAME
            DB_PASSWORD = YOUR_PASSWORD
            DB_NAME = YOUR_DATABASE 
        FOR YOUR REAL VALUES
    */
    private string $host = "DB_HOST";
    private string $user = "DB_USER";
    private string $password = "DB_PASSWORD";
    private string $database = "DB_NAME";
    private $link = null;
    static $_instance = null;

    public function __construct(){
        $this->link = mysqli_connect($this->host,$this->user,$this->password,$this->database) or die("error de conexion");
        mysqli_set_charset($this->link, 'utf8');
    }

    private function __clone(){}

    public static function getInstance(): ?DataBase
    {
        if(!(self::$_instance instanceof self)):
            self::$_instance = new self();
        endif;
        return self::$_instance;
    }

    public function getAllRow($sql){
        $dataArray = array();
        $response = mysqli_query($this->link,$sql);
        if($response!=false):
            while($row = mysqli_fetch_assoc($response)):
                $dataArray[] = $row;
            endwhile;
            return $dataArray;
        else:
            return false;
        endif;
    }

    public function getOnlyRow($sql){
        $response = mysqli_query($this->link,$sql);
        if($response!=false):
            return mysqli_fetch_assoc($response);
        else:
            return false;
        endif;
    }

    public function exec($sql){
        $response = mysqli_query($this->link,$sql);
        if($response!=false):
            return true;
        else:
            return false;
        endif;
    }

    public function __destruct(){
        $this->link = null;
    }

}
