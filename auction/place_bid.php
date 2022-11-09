<?php include_once("header.php") ?>
<?php

$bidAmt = $_POST['bid'];
$auctionID = $_POST['auctionID'];

if (!isset($_SESSION) || !$_SESSION['logged_in']) {
    $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
        You must login to place a bid
        </div>';
    header('Location:listing.php?item_id=' . $auctionID);
    die();
}

//Get the previous bid (if any)
$sqll = "SELECT * from Bids where auctionId = '$auctionID' ORDER BY bidPrice DESC LIMIT 1";
$res = mysqli_query($conn, $sqll);
$sql_fetch = mysqli_fetch_row($res);
$currPrice = $sql_fetch[3];

if (!$sql_fetch) { // no bids yet. Get the starting price instead
    $res = mysqli_query($conn, "SELECT startingPrice from Auctions where auctionId = '$auctionID'");
    $currPrice = mysqli_fetch_row($res)[0];
}

function placeBid($connection, $username, $aID, $bidVal)
{
    $sqlUpdateRow = "INSERT INTO Bids (username, auctionId, bidPrice) VALUES ('" . $username . "', '" . $aID . "', '" . $bidVal . "')";
    if ($connection->query($sqlUpdateRow) === TRUE) {
        echo "New record created successfully";
        $msg = '<div class="alert alert-success" role="alert">
            Your bid has been placed!
            </div>';
    } else {
        echo "Error: " . $sqlUpdateRow . "<br>" . $connection->error;
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            There was a problem... bid not placed.
            </div>';
    }

    return $msg;
}


// Check if bid higher than current price
if ($bidAmt > $currPrice) {
    $_SESSION['msg'] = placeBid($conn, $_SESSION['username'], $auctionID, $bidAmt);

    //get the id of the bid just created
    $sql_fetch_bid_id = mysqli_fetch_row(mysqli_query($conn, "select bidId from Bids where auctionId = '$auctionID' ORDER BY bidId DESC LIMIT 1"));

    //Add outbid notification for the prev bidder (if any)
    if ($sql_fetch) {
        $conn->query("INSERT INTO Notifications (username, auctionId, bidId, notificationType, dateTime) VALUES ('" . $sql_fetch[1] . "','" . $auctionID . "','" . $sql_fetch_bid_id[0] . "', 'Outbid', NOW())");
    }
    //Get all the people watching this auction and add notification for each of them
    $result = $conn->query("SELECT username FROM Watchlist WHERE auctionId = '$auctionID'");
    while ($row = $result->fetch_assoc()) {
        $conn->query("INSERT INTO Notifications (username, auctionId, bidId, notificationType, dateTime) VALUES ('" . $row['username'] . "','" . $auctionID . "','" . $sql_fetch_bid_id[0] . "', 'Watchlist', NOW())");
    };
} else {
    // notify user of failure
    $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            You must outbid the current bid!
            </div>';
}

header('Location:listing.php?item_id=' . $auctionID);
?>