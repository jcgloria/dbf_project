<?php include_once("header.php")?>
<?php include 'create_auction.php' ?>

<div class="container my-5">

<?php
ob_start();
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
    //check title
    if(empty($_POST['title'])) {
        echo '<div class="alert alert-danger"role="alert">Title is required;</div>';
        die();
    } else {
        $title = $_POST['title'];
    }
    
    //don't need to check details
    $details = $_POST['details'];
    
    //check category
    if(empty($_POST['category'])) {
        echo '<div class="alert alert-danger"role="alert">Category is required;</div>';
        die();
    } else {
        $category = $_POST['category'];
    }

    //check starting price
    if(empty($_POST['startingPrice'])) {
        echo '<div class="alert alert-danger"role="alert">Starting price is required;</div>';
        die();
    } else {
        if($_POST['startingPrice'] < 0) {
            echo '<div class="alert alert-danger"role="alert">Staring price should be larger than 0;</div>';
            die();
        } else {
            $startingPrice = $_POST['startingPrice'];
        }
    }


    //check reservePrice
    if (empty($_POST['reservePrice'])) {
        $reservePrice = $_POST['startingPrice'];
    } else {
        if($_POST['reservePrice'] < $startingPrice) {
            echo '<div class="alert alert-danger"role="alert">Reserve price should be larger than starting price</div>';
            die();   
        } else {
            $reservePrice = $_POST['reservePrice'];
        }
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

    //check image
    if(empty($_FILES['image']['name'])) {
        $image = null;
    } else {
        $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
    }
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
    // if everything is ok, try to upload file
    } 
    if ($uploadOk == 1) {
        $newfilename = $target_dir . round(microtime(true)) . '.' . $imageFileType;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $newfilename)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["image"]["name"])). " has been uploaded.";
        } else {
        echo "Sorry, there was an error uploading your file.";
        }
    }

    /* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */

    $sql = "INSERT INTO Auctions(username,title,details,category,startingPrice,reservePrice,endDate,auctionImage) VALUES ('$username', '$title', '$details', '$category', '$startingPrice', '$reservePrice', timestamp '$w', '$newfilename')";


    if(mysqli_query($conn,$sql)) {
        // If all is successful, let user know.
        //echo '<div class="alert alert-success text-center"role="alert">Auction sucessfully created <a href="mylisting.php" class="alert-link">View your new listing.</a></div>';
        //"Location: index.php";
        $fetch = " SELECT max(auctionID) FROM Auctions GROUP BY  '$username' " ;
        $item = mysqli_fetch_row(mysqli_query($conn,$fetch));
        $item_id = $item[0];
        echo "<div class='alert alert-success text-center'role='alert'>Auction sucessfully created <a href='listing.php?item_id=$item_id' class='alert-link'>View my new listing.</a> </div>";
        header('Location:mylistings.php');
    }  else {
        echo 'Error: ' .mysqli_error($conn);
        //echo"<a href='create_auction.php'>Unsucessfull</a>";
        $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
            Auction create unsucessful.
        </div>';
    }   

}




?>

</div>


<?php include_once("footer.php")?>