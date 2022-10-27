<?php
ob_start();
 include_once("header.php");
ob_end_clean(); //this function cleans the echo calls that are in header.php so they don't clash with this file
?>
 <?php
  if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
    return;
  }

  // Extract arguments from the POST variables:
  $item_id = $_POST['arguments'][0];
  $user = $_POST['arguments'][1];

  if ($_POST['functionname'] == "add_to_watchlist") {
    // TODO: Update database and return success/failure.
    $sql = "INSERT INTO Watchlist(auctionId, username) VALUES ($item_id, '$user')";
    $sql_res = mysqli_query($conn, $sql);
    if ($sql_res) {
      $res = "success";
    } else {
      $res = "failure";
    }
  } else if ($_POST['functionname'] == "remove_from_watchlist") {
    // TODO: Update database and return success/failure.
    $sql = "DELETE FROM Watchlist where auctionId = $item_id and username = '$user'";
    $sql_res = mysqli_query($conn, $sql);
    if ($sql_res) {
      $res = "success";
    } else {
      $res = "failure";
    }
  }

  // Note: Echoing from this PHP function will return the value as a string.
  // If multiple echo's in this file exist, they will concatenate together,
  // so be careful. You can also return JSON objects (in string form) using
  // echo json_encode($res).
  echo $res;

  ?>