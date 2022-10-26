<?php include_once("header.php") ?>
<?php

$bidAmt = $_POST['bid'];
$auctionID = $_POST['auctionID'];

if (!isset($_SESSION['logged_in'])) {
    $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            You must login to place a bid
            </div>';
    header('Location:listing.php?item_id='.$auctionID);
    die();
}

$sqll = "select*from Bids where auctionId = '$auctionID' ORDER BY bidPrice DESC LIMIT 1";
$res = mysqli_query($conn, $sqll);
$sql_fetch = mysqli_fetch_row($res);

function placeBid($connection, $username, $aID, $bidVal) {
    $sqlUpdateRow = "INSERT INTO Bids (username, auctionId, bidPrice) VALUES ('".$username."', '".$aID."', '".$bidVal."')";
    if ($connection->query($sqlUpdateRow) === TRUE) { echo "New record created successfully"; }
    else { echo "Error: " . $sqlUpdateRow . "<br>" . $connection->error; }
    $msg = '<div class="alert alert-success" role="alert">
        Your bid has been placed!
        </div>';
    
    return $msg;
}

if($sql_fetch) {
    // Check if bid higher than highest bid
    if($bidAmt > $sql_fetch[3]) {    
        $_SESSION['msg'] = placeBid($conn, $_SESSION['username'], $auctionID, $bidAmt);
        
        $sql_fetch_prev_bidder = mysqli_fetch_row(mysqli_query($conn, "select * from Users where username = '$sql_fetch[1]'"));
        mail($sql_fetch_prev_bidder[3], "You have been outbid", "TODO: more helpful outbid notif...", "");
    } else {
        // notify user of failure
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            You must outbid the previous bidder!
            </div>';
    }
} else { // No previous bids...
    $_SESSION['msg'] = placeBid($conn, $_SESSION['username'], $auctionID, $bidAmt);
}

header('Location:listing.php?item_id='.$auctionID);
?>