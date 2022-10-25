<?php
include_once('register.php');
// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

//initialize varaibles
$accountType = $name = $email = $pass = '';
$nameErr = $emailErr = $passErr = $repassErr = '';


//
if(isset($_POST['register'])) {
    //validate & sanitize name
    if(empty($_POST['username'])) {
        die('username is required');
    } else {
        $name = filter_input(INPUT_POST,'username',FILTER_SANITIZE_SPECIAL_CHARS);
        
    }
    //validate & sanitize email
    if(empty($_POST['email'])) {
        die('Email is required');
    } else {
        $email = filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
    }
    //validate & sanitize password
    if(empty($_POST['password'])) {
        die('Email is required');
    } else {
        $pass = filter_input(INPUT_POST,'password',FILTER_SANITIZE_EMAIL);
    }
    
    //check repeat password
    $pass = $_POST['password'];
    $pass_md5 = md5('DBF'.$pass);
    if($pass != $_POST['passwordConfirmation']) {
        die('password must be the same');
    }

    //validate whether there exist a same username in database
    $sqll = "select*from users where username = '$name'";
    $res = mysqli_query($conn, $sqll);
    $sql_fetch = mysqli_fetch_row($res);
    if($sql_fetch) {
        die('username already exist');
    }



    if($_POST['accountType']='seller') {
        $sql = "INSERT INTO Users(username,seller,pass,email) VALUES ('$name', 1 , '$pass_md5', '$email')";
    } else {
        $sql = "INSERT INTO Users(username,seller,pass,email) VALUES ('$name', 0 , '$pass_md5', '$email')";
    }
    if(mysqli_query($conn,$sql)) {
        //success
        echo "<script type='text/javascript'>alert('account sucessfully created');</script>";
        header("Location: index.php");
        die();
    }  else {
        echo 'Error: ' .mysqli_error($conn);
        echo"<a href='register.php'>Unsucessfull</a>";
    }   
}


?>