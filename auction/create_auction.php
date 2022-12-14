<?php include_once("header.php")?>

<?php
  //(Uncomment this block to redirect people without selling privileges away from this page)
  // If user is not logged in or not a seller, they should not be able to
  // use this page.
if (!isset($_SESSION['seller']) || $_SESSION['seller'] != 1) {
  header('Location: browse.php');
}

$sql = "SELECT category FROM Categories";
$result = mysqli_query($conn,$sql);

?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!-- Note: This form does not do any dynamic / client-side / 
      JavaScript-based validation of data. It only performs checking after 
      the form has been submitted, and only allows users to try once. You 
      can make this fancier using JavaScript to alert users of invalid data
      before they try to send it, but that kind of functionality should be
      extremely low-priority / only done after all database functions are
      complete. -->
      <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
          <div class="col-sm-10">
            <input type="text" value="<?php if(isset($_SESSION['title'])){ echo $_SESSION['title'];}?>" class="form-control" name="title" id="auctionTitle" placeholder="e.g. Black mountain bike">
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
          <div class="col-sm-10">
            <textarea class="form-control" name="details" id="auctionDetails" rows="4"><?php if(isset($_SESSION['details'])){ echo $_SESSION['details'];}?></textarea>
            <small id="detailsHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionImage" class="col-sm-2 col-form-label text-right">Upload an image</label>
          <div class="col-sm-10">
            <input type="file" class="form-control" name="image" id="auctionImage" ></br>
            <small id="imageHelp" class="form-text text-muted">Upload an image of the item you're selling. This will be displayed in listings.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" name="category" id="auctionCategory">
              <option selected>Choose...</option>
              <?php
              while($rows = mysqli_fetch_assoc($result)) {
                echo '<option value="' .$rows['category'].'">' .$rows['category'] ."</option>";
              } 
              ?>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select a category for this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
	        <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">??</span>
              </div>
              <input type="number" value="<?php if(isset($_SESSION['startingPrice'])){ echo $_SESSION['startingPrice'];}?>" name="startingPrice" class="form-control" id="auctionStartPrice">
            </div>
            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">??</span>
              </div>
              <input type="number" value="<?php if(isset($_SESSION['reservePrice'])){ echo $_SESSION['reservePrice'];}?>" name="reservePrice" class="form-control" id="auctionReservePrice">
            </div>
            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" value="<?php if(isset($_SESSION['endDate'])){ echo $_SESSION['endDate'];}?>" name="endDate" class="form-control" id="auctionEndDate">
            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Day for the auction to end.</small>
          </div>
        </div>
        <button type="submit" class="btn btn-primary form-control" name="createAuction">Create Auction</button>
      </form>
    </div>
  </div>
</div>

</div>


<?php include_once("footer.php")?>