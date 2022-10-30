<?php include_once("header.php")?>
<?php include 'create_auction.php' ?>

<div class="container my-5">

<?php

// This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */


/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */
if(isset($_POST['createAuction'])) {
    $username = $_SESSION['username'];
    $title = $_POST['title'];
    $details = $_POST['details'];
    $category = $_POST['category'];
    $startingPrice = $_POST['startingPrice'];

    //check reservePrice
    if($_POST['reservePrice'] < $startingPrice) {
        echo '<div class="alert alert-danger"role="alert">Reserve price should be larger than starting price</div>'; 
        die();  
    } else {
        $reservePrice = $_POST['reservePrice'];
    }
    //check endDate
    $currentDate = date('Y/m/d H:i');
    $getEndDate = date("Y/m/d H:i", strtotime($_POST["endDate"])); 
    
    if($getEndDate < $currentDate) {
        echo '<div class="alert alert-danger"role="alert">End date should be larger than current time</div>';
        die(); 
    } else {
        $endDate = $getEndDate;
    }
    
    //add seconds to Datetime 
    $w = $endDate .":00";


    /* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
    $sql = "INSERT INTO Auctions(username,title,details,category,startingPrice,reservePrice,endDate) VALUES ('$username', '$title', '$details', '$category', '$startingPrice', '$reservePrice', timestamp '$w' )";

    if(mysqli_query($conn,$sql)) {
        // If all is successful, let user know.
        echo '<div class="alert alert-success text-center"role="alert">Auction sucessfully created <a href="listing.php" class="alert-link">View your new listing.</a></div>';
        "Location: index.php";
        die();
    }  else {
        echo 'Error: ' .mysqli_error($conn);
        echo"<a href='create_auction.php'>Unsucessfull</a>";
    }   
}




?>

</div>


<?php include_once("footer.php")?>