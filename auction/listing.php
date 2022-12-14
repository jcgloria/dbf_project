<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<?php
// Get info from the URL:
if(empty($item_id)){
  $item_id = $_GET['item_id'];
}

// TODO: Use item_id to make a query to the database.

$sql = "select a.title, a.details, a.endDate, a.reservePrice, a.auctionImage, a.startingPrice, COUNT(b.bidId) as numBids, MAX(b.bidPrice) as bidPrice, a.username
from Auctions as a left join Bids as b on a.auctionId = b.auctionId
where a.auctionId = '$item_id'
group by a.title, a.details, a.endDate, a.reservePrice, a.auctionImage, a.startingPrice";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
  $title = $row['title'];
  $description = $row['details'];
  $current_price = $row['numBids'] > 0 ? $row['bidPrice'] : $row['startingPrice'];
  $num_bids = $row['numBids'];
  $end_time = new DateTime($row['endDate']);
  $image = $row['auctionImage'];
  $seller = $row['username'];
  if ($image == null) {
    $image = "https://st4.depositphotos.com/14953852/24787/v/600/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg";
  }
  $reserve_price = $row['reservePrice'];
  //Check if there's at least one bid and the reserve price has been met. 
  $current_price >= $reserve_price && $num_bids != 0  ? $minBid = true : $minBid = false;
} else {
  $_SESSION['msg'] = '<div class="alert alert-danger" role="alert">
        There was an error picking up the data from the server
      </div>';
  header("Location: browse.php");
}

// TODO: Note: Auctions that have ended may pull a different set of data,
//       like whether the auction ended in a sale or was cancelled due
//       to lack of high-enough bids. Or maybe not.

// Calculate time to auction end:
$now = new DateTime();

if ($now < $end_time) {
  $time_to_end = date_diff($now, $end_time);
  $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
}

// TODO: If the user has a session, use it to make a query to the database
//       to determine if the user is already watching this item.
//       For now, this is hardcoded.
$has_session = false;
$watching = false;
$user = "guest";
if (isset($_SESSION) && $_SESSION['logged_in']) {
  $has_session = true;
  $user = $_SESSION['username'];
  $sql_fetch_watchlist = mysqli_fetch_row(mysqli_query($conn, "select * from Watchlist where auctionId = '$item_id' and username = '$user'"));
  $sql_fetch_watchlist ? $watching = true : $watching = false;
}

?>

<div class="container">

  <div class="row">
    <!-- Row #1 with auction title + watch button -->
    <div class="col-sm-8">
      <!-- Left col -->
      <h2 class="my-3"><?php echo ($title); ?></h2>
    </div>
    <div class="col-sm-4 align-self-center">
      <!-- Right col -->
      <?php
      /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
      if ($now < $end_time) :
      ?>
        <div id="watch_nowatch" <?php if ($has_session && $watching) echo ('style="display: none"'); ?>>
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
        </div>
        <div id="watch_watching" <?php if (!$has_session || !$watching) echo ('style="display: none"'); ?>>
          <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
        </div>
      <?php endif /* Print nothing otherwise */ ?>
    </div>
  </div>

  <div class="row">
    <!-- Row #2 with auction description + bidding info -->
    <div class="col-sm-8">
      <!-- Left col with item info -->

      <div class="itemDescription">
        <?php echo ($description); ?>
      </div>
      <br>
      <div class = "itemImage">
        <img src="<?php echo ($image) ?>" alt="Auction Image" class="img-thumbnail"  width="500" height="500">	
    </div>
    <div class = "col-sm-4"></br>
      <!-- show reserve price if it is set -->
      <?php if ($reserve_price != 0) {
          echo "<p class = text-muted   > Reserve price: $$reserve_price </p>";
      }
      ?>
    </div>
    <div class="col-sm-4">
      <!-- Right col with bidding info -->
      <p>
        <?php if ($now > $end_time) : ?>
          This auction ended <?php echo (date_format($end_time, 'j M H:i')) ?>
          <!-- TODO: Print the result of the auction here? -->
          <?php
          echo '<br>';
          if ($minBid) {
            echo 'Sold!';
          } else {
            echo 'Minimum bid not met.';
          } ?>
        <?php else : ?>
          Auction ends <?php echo (date_format($end_time, 'j M H:i') . $time_remaining) ?>
      </p>
      <p class="lead">Current bid: ??<?php echo (number_format($current_price, 2)) ?></p>

      <!-- Bidding form -->
      <form method="POST" action="place_bid.php">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">??</span>
          </div>
          <input type="number" class="form-control" name="bid" id="bid">
          <input type="hidden" name="auctionID" value="<?php echo $item_id; ?>">
        </div>
        <button type="submit" class="btn btn-primary form-control" <?php if ($user == $seller) echo ("disabled")?>>Place bid</button>
      </form>
    <?php endif ?>


    </div> <!-- End of right col with bidding info -->

  </div> <!-- End of row #2 -->



  <?php include_once("footer.php") ?>


  <script>
    // JavaScript functions: addToWatchlist and removeFromWatchlist.

    function addToWatchlist(button) {

      // This performs an asynchronous call to a PHP function using POST method.
      // Sends item ID as an argument to that function.
      $.ajax('watchlist_funcs.php', {
        type: "POST",
        data: {
          functionname: 'add_to_watchlist',
          arguments: [<?php echo ($item_id); ?>, <?php echo "'$user'"; ?>],
        },

        success: function(obj, textstatus) {
          // Callback function for when call is successful and returns obj
          var objT = obj.trim();
          if (objT == "success") {
            $("#watch_nowatch").hide();
            $("#watch_watching").show();
          } else {
            var mydiv = document.getElementById("watch_nowatch");
            mydiv.appendChild(document.createElement("br"));
            mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
          }
        },

        error: function(obj, textstatus) {
          console.log(obj);
        }
      }); // End of AJAX call

    } // End of addToWatchlist func

    function removeFromWatchlist(button) {
      // This performs an asynchronous call to a PHP function using POST method.
      // Sends item ID as an argument to that function.
      $.ajax('watchlist_funcs.php', {
        type: "POST",
        data: {
          functionname: 'remove_from_watchlist',
          arguments: [<?php echo ($item_id); ?>, <?php echo "'$user'"; ?>]
        },

        success: function(obj, textstatus) {
          // Callback function for when call is successful and returns obj
          var objT = obj.trim();

          if (objT == "success") {
            $("#watch_watching").hide();
            $("#watch_nowatch").show();
          } else {
            var mydiv = document.getElementById("watch_watching");
            mydiv.appendChild(document.createElement("br"));
            mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
          }
        },

        error: function(obj, textstatus) {
          console.log("Error");
        }
      }); // End of AJAX call

    } // End of addToWatchlist func
  </script>