<?php include_once("header.php") ?>

<?php

//Check session to see if user is logged in
#Get the session of the user
session_start();
if (isset($_SESSION) && $_SESSION['logged_in']) {
    $username = $_SESSION['username'];
} else {
    echo '<p>You must login to view notifications</p>';
}
//Check to see if updateAuctionEndNotification is being called through POST
if (isset($_POST['updateAuctionEndNotification'])) {
    updateAuctionEndNotification($conn);
    echo '<p>Notifications updated</p>';
    die();
}

//This function checks which auctions have ended and it's in charge of letting the seller, buyer, and watchers know about it.
function updateAuctionEndNotification($conn)
{
    //Get all the auctions that have ended that don't have notifications sent yet. Check why they ended too (if they were sold or not).
    $sql = "SELECT a.auctionId,a.username,a.reservePrice,MAX(b.bidId) as bidId,
	case when COUNT(b.bidId) > 0 and MAX(b.bidPrice) > a.reservePrice then 1 #Sold
    else 2 #Reserve price not met
    end as auction_status
FROM
    Auctions a join Bids b on a.auctionId = b.auctionId
WHERE a.endDate <= NOW() AND a.auctionId NOT IN (
        SELECT auctionId
        FROM Notifications
        WHERE notificationType = 'Auction Ended-Seller' #If this notification exists then this auction has already been notified as ended. 
    	AND auctionId = a.auctionId) 
GROUP BY a.auctionId,a.username,a.reservePrice, a.startingPrice";

    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        //Check the auction status (sold or not)
        if ($row['auction_status'] == 1) { //Sold - has bidId
            $conn->query("INSERT INTO Notifications (username, auctionId, bidId, notificationType, dateTime) VALUES ('" . $row['username'] . "','" . $row['auctionId'] . "','" . $row['bidId'] . "', 'Auction Ended-Seller', NOW())");
            //Add notification for the buyer
            $buyer = $conn->query("SELECT username FROM Bids WHERE bidId = " . $row['bidId'])->fetch_assoc()['username'];
            $conn->query("INSERT INTO Notifications (username, auctionId, bidId, notificationType, dateTime) VALUES ('" . $buyer . "','" . $row['auctionId'] . "','" . $row['bidId'] . "', 'Auction Ended-Buyer', NOW())");
        } else { //Reserve price not met - no bidId - we only need to alert the seller (no buyer here)
            $conn->query("INSERT INTO Notifications (username, auctionId, notificationType, dateTime) VALUES ('" . $row['username'] . "','" . $row['auctionId'] . "', 'Auction Ended-Seller', NOW())");
        }

        //Notify also the people watching the auction
        //Get the users watching this auction
        $result2 = $conn->query("SELECT username FROM Watchlist WHERE auctionId = '" . $row['auctionId'] . "'");
        while ($row2 = $result2->fetch_assoc()) {
            //Add notification for the watcher
            if ($row['auction_status'] == 1) { //Sold - has bidId
                $conn->query("INSERT INTO Notifications (username, auctionId, bidId, notificationType, dateTime) VALUES ('" . $row2['username'] . "','" . $row['auctionId'] . "', '" . $row['bidId'] . "', 'Auction Ended-Watcher', NOW())");
            } else { //Reserve price not met - no bidId
                $conn->query("INSERT INTO Notifications (username, auctionId, notificationType, dateTime) VALUES ('" . $row2['username'] . "','" . $row['auctionId'] . "', 'Auction Ended-Watcher', NOW())");
            }
        }
    }
}

//updateAuctionEndNotification($conn);
?>
<?php

//Lets get the notifications for the current user. Also get relevant info about the auction and bid (if any).
$notifications = $conn->query("SELECT n.notificationType, a.title, b.bidPrice, n.dateTime, n.bidId
    FROM Notifications as n join Auctions as a on n.auctionId = a.auctionId join Bids as b on b.bidId = n.bidId 
    WHERE n.username = '" . $username . "' ORDER BY dateTime DESC");

?>
<style>
</style>
<div class="col justify-content-center">
    <br>
    <?php
    while ($row = $notifications->fetch_assoc()) {
        echo '<div class="card">
        <div class="card-header">
          ' . $row['dateTime'] . '
        </div>
        <div class="card-body">
          <h5 class="card-title">';
        switch ($row['notificationType']) {
            case 'Auction Ended-Seller':
                echo 'Your auction has ended!</h5>';
                if ($row['bidId'] != null) {
                    echo '<p class="card-text">Your auction <strong>' . $row['title'] . '</strong> has ended and it was sold for <strong>£' . $row['bidPrice'] . '</strong>.</p>';
                } else {
                    echo '<p class="card-text">Your auction <strong>' . $row['title'] . '</strong> has ended and it was not sold.</p>';
                }
                break;
            case 'Auction Ended-Buyer':
                echo 'You have won an auction!</h5>
                <p class="card-text">You have won the auction <strong>' . $row['title'] . '</strong> for <strong>£' . $row['bidPrice'] . '</strong>.</p>';
                break;
            case 'Auction Ended-Watcher':
                echo 'An auction you were watching has ended!</h5>
                <p class="card-text">The auction <strong>' . $row['title'] . '</strong> has ended.</p>';
                break;
            case 'Outbid':
                echo 'You have been outbidded!</h5>
                <p class="card-text">You have been outbidded on the auction <strong>' . $row['title'] . '</strong> for <strong>£' . $row['bidPrice'] . '</strong>.</p>';
                break;
            case 'Watchlist':
                echo 'An auction you are watching has a new bid!</h5>
                <p class="card-text">The auction <strong>' . $row['title'] . '</strong> has a new bid for <strong>£' . $row['bidPrice'] . '</strong>.</p>';
                break;
        }
        echo '</div></div><br>';
    }
    ?>
</div>