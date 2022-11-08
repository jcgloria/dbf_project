<?php include "header.php"?>

<?php 
ob_start();
include 'mylistings.php';
?>

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
$_SESSION['title'] = ''; $_SESSION['category'] = ''; $_SESSION['startingPrice'] = ''; $_SESSION['reservePrice'] = ''; $_SESSION['endDate'] = ''; $_SESSION['details'];

if(isset($_POST['createAuction'])) {
    $username = $_SESSION['username'];
    
    //don't need to check details
    $_SESSION['details'] = $_POST['details'];
    
    //check endDate
    $currentDate = date('Y/m/d H:i');
    $getEndDate = date("Y/m/d H:i", strtotime($_POST["endDate"])); 
    
    if($getEndDate < $currentDate) {
        //echo '<div class="alert alert-danger"role="alert">End date should be larger than current time</div>';
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
        End date should be larger than current time.
        </div>';
        header("Location:create_auction.php");
    } else {
        $_SESSION['endDate'] = $_POST['endDate'];
        $endDate = $getEndDate;
    }
    
    //add seconds to Datetime 
    $w = $endDate .":00";

    //check starting price
    if(empty($_POST['startingPrice'])) {
        //echo '<div class="alert alert-danger"role="alert">Starting price is required;</div>';
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
        Starting price is required.
        </div>';
        header("Location:create_auction.php");
    } else {
        if($_POST['startingPrice'] < 0) {
            //echo '<div class="alert alert-danger"role="alert">Staring price should be larger than 0;</div>';
            $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            Staring price should be larger than 0.
            </div>';
            header("Location:create_auction.php");
        } else {
            $_SESSION['startingPrice'] = $_POST['startingPrice'];
        }
    }

    //check reservePrice
    if (empty($_POST['reservePrice'])) {
        $reservePrice = $_POST['startingPrice'];
    } else {
        if($_POST['reservePrice'] < $_SESSION['startingPrice']) {
            //echo '<div class="alert alert-danger"role="alert">Reserve price should be larger than starting price</div>';
            $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            Reserve price should be larger than starting price.
            </div>';
            header("Location:create_auction.php");
        } else {
            $_SESSION['reservePrice'] = $_POST['reservePrice'];
        }
    }

    //check category
    if($_POST['category'] == 'Choose...') {
        //echo '<div class="alert alert-danger"role="alert">Category is required;</div>';
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
        Category is required.
        </div>';
        header("Location:create_auction.php");
    } else {
        $SESSION['category'] = $_POST['category'];
    }

    //check title
    if(empty($_POST['title'])) {
        //echo '<div class="alert alert-danger"role="alert">Title is required;</div>';
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
        Title is required.
        </div>';
        header("Location:create_auction.php");
    } else {
        //$title = $_POST['title'];
        $_SESSION['title'] = $_POST['title'];
    }

    //check image
    if(empty($_FILES['image']['name'])) {
        $image = null;
    } else {
            $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        
        $target_dir = "images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is an actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                echo nl2br("File is not an image.\n");
                $uploadOk = 0;
            }
        }
        
        // Check file size
        if ($_FILES["image"]["size"] > 5000000) {
            echo nl2br("Sorry, your file is too large.\n");
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            echo nl2br("Sorry, only JPG, JPEG, PNG & GIF files are allowed.\n");
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
            die();
        } 
        if ($uploadOk == 1) {
            $newfilename = $target_dir . round(microtime(true)) . '.' . $imageFileType;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $newfilename)) {
            echo "The file ". htmlspecialchars( basename( $_FILES["image"]["name"])). " has been uploaded.";
            } else {
            echo "Sorry, there was an error uploading your file.";
            die();
            }
        }
    }

    /* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
    if(!empty($_SESSION['title']) && !empty($SESSION['category']) && !empty($_SESSION['startingPrice']) && !empty($_SESSION['endDate'])) {
        //set values
        $title = $_SESSION['title']; $category = $SESSION['category']; $startingPrice = $_SESSION['startingPrice']; if($reservePrice != $_POST['startingPrice']) {$reservePrice = $_SESSION['reservePrice'];}; $details = $_SESSION['details']; 
        if($image != null) {
            $sql = "INSERT INTO Auctions(username,title,details,category,startingPrice,reservePrice,endDate,auctionImage) VALUES ('$username', '$title', '$details', '$category', '$startingPrice', '$reservePrice', timestamp '$w', '$newfilename')";
        } else{
            $sql = "INSERT INTO Auctions(username,title,details,category,startingPrice,reservePrice,endDate) VALUES ('$username', '$title', '$details', '$category', '$startingPrice', '$reservePrice', timestamp '$w')";
        }

        if(mysqli_query($conn,$sql)) {
            // If all is successful, let user know.
            $fetch = " SELECT max(auctionID) FROM Auctions GROUP BY  '$username' " ;
            $item = mysqli_fetch_row(mysqli_query($conn,$fetch));
            $item_id = $item[0];
            //Erase sessions
            $_SESSION['title'] = ''; $SESSION['category'] = ''; $_SESSION['startingPrice'] = ''; $_SESSION['endDate'] = ''; $_SESSION['reservePrice'] = ''; $_SESSION['details'] = '';
            //echo "<div class='alert alert-success text-center'role='alert'>Auction sucessfully created <a href='listing.php?item_id=$item_id' class='alert-link'>View my new listing.</a> </div>";
            $_SESSION['msg'] = '<div class="alert alert-success" role="alert">
            Auction create sucessful!
            </div>';
            header("Location:mylistings.php");
        }  else {
            echo 'Error: ' .mysqli_error($conn);
            echo"<a href='create_auction.php'>Unsucessfull</a>";
        }   
    } else {
        die();
    }


}




?>

</div>


<?php include_once("footer.php")?>