<?php
include_once("header.php");
ob_start();
include_once("profile.php");
ob_end_clean();
// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

if (isset($_POST['update'])) {
    //validate & sanitize email
    if ($_POST['email']) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    }
    if ($_POST['accountType'] == 'seller') {
        $setSeller = " seller = true,";
    }
    $sql = "Update Users
    SET" . $setSeller . " email = '$email'
    WHERE username = '$username'";
    echo $sql;

    // //validate & sanitize password
    // if(empty($_POST['password'])) {
    //     echo '<div class="alert alert-danger"role="alert">Password is required;</div>';
    //     die();
    // } else {
    //     $pass = filter_input(INPUT_POST,'password',FILTER_SANITIZE_EMAIL);
    // }

    // //check repeat password
    // $pass = $_POST['password'];
    // $pass_md5 = md5('DBF'.$pass);
    // if($pass != $_POST['passwordConfirmation']) {
    //     echo '<div class="alert alert-danger"role="alert">Password must be the same;</div>';
    //     die();
    // }
    
    if (mysqli_query($conn, $sql)) {
        $msg = '<div class="alert alert-success" role="alert">
            Your profile has been updated successfully.
            </div>';
    } else {
        $msg = 'Error: ' . mysqli_error($conn) . '<div class="alert alert-danger" role="alert">
            There was a problem... profile not updated.
            </div>';
    }
    $_SESSION['msg'] = $msg;
    header('Location: profile.php');
}
