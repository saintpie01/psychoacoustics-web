
<?php   

    function connectdb(){ 
        include "config.php";
        $conn = new mysqli($host, $user, $password, $dbname);
        if ($conn->connect_errno)
            throw new Exception('DB connection failed');
        mysqli_set_charset($conn, "utf8"); 
        return $conn;
    }
?>