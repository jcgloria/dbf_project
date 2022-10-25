<?php
require('header.php');
// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.
$user = $_POST['username'];
$password = $_POST['password'];
$hashedPass = md5('DBF' . $password);

// For now, I will just set session variables and redirect.
if (isset($_POST['username']) && isset($_POST['password'])) {
    $sqll = "select*from users where username = '$user' and pass = '$hashedPass'";
    $res = mysqli_query($conn, $sqll);
    $sql_fetch = mysqli_fetch_row($res);
    if ($sql_fetch) {
        //login succesful
        session_start();
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['seller'] = $sql_fetch[1]; //position 1 is seller boolean

        echo ('<div class="alert alert-success" role="alert">
        You are now logged in! You will be redirected shortly...
      </div>');

        // Redirect to index after 5 seconds
        header("refresh:5;url=index.php");
    } else {
        echo ('<div class="alert alert-danger" role="alert">
        There was an error in your login attempt. You will be redirected shortly...
      </div>');
      
       // Redirect to index after 5 seconds
      header("refresh:5;url=index.php");
    }
} 


// session_start();
// $_SESSION['logged_in'] = true;
// $_SESSION['username'] = "test";
// $_SESSION['account_type'] = "buyer";

// echo ('<div class="text-center">You are now logged in! You will be redirected shortly.</div>');

// // Redirect to index after 5 seconds
// header("refresh:5;url=index.php");
