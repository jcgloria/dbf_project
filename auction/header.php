<?php
// FIXME: At the moment, I've allowed these values to be set manually.
// But eventually, with a database, these should be set automatically
// ONLY after the user's login credentials have been verified via a 
// database query.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'dbf_project');
//connect to mysql database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); //or die ('connection error!');
//check connection
if ($conn->connect_error) {
  echo "connection error: " . $conn->connect_error;
  die();
} else {
  echo '<script>console.log("connected")</script>';
}
session_start();
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap and FontAwesome CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">

  <title>DBay</title>
</head>

<body>

  <!-- Navbars -->
  <?php
  if (!empty($_SESSION['msg'])) {
    echo $_SESSION['msg'];
    $_SESSION['msg'] = "";
  }
  ?>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
    <a class="navbar-brand" href="#">DBay</a>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">

        <?php
        // Displays either login or logout on the right, depending on user's
        // current status (session).
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
          echo '<a class="nav-link" href="logout.php">Logout</a>';
        } else {
          echo '<button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>';
        }
        ?>

      </li>
    </ul>
  </nav>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <ul class="navbar-nav align-middle">
      <li class="nav-item mx-1">
        <a class="nav-link" href="browse.php">Browse</a>
      </li>
      <?php
      if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
        echo ('
	<li class="nav-item mx-1">
      <a class="nav-link" href="mybids.php">My Bids</a>
    </li>
	<li class="nav-item mx-1">
      <a class="nav-link" href="recommendations.php">Recommended</a>
    </li>');
      }
      if (isset($_SESSION['seller']) && $_SESSION['seller'] == 1) {
        echo ('
	<li class="nav-item mx-1">
      <a class="nav-link" href="mylistings.php">My Listings</a>
    </li>
	<li class="nav-item ml-3">
      <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
    </li>');
      }
      ?>
    </ul>
    <ul class="navbar-nav ml-auto">
      <a class="nav-link" href="profile.php">Profile</a>
      <a class="nav-link" href="notifications.php">Notifications</a>
      <a class="nav-link" href="reporting.php">Reporting</a>
    </ul>
  </nav>

  <!-- Login modal -->
  <div class="modal fade" id="loginModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Login</h4>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <form method="POST" action="login_result.php">
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" class="form-control" name="username" id="username" placeholder="Username">
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" class="form-control" name="password" id="password" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary form-control">Sign in</button>
          </form>
          <div class="text-center">or <a href="register.php">create an account</a></div>
        </div>

      </div>
    </div>
  </div> <!-- End modal -->