<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php
#Get the session of the user
  session_start();
  if (isset($_SESSION) && $_SESSION['logged_in']) {
    $username = $_SESSION['username'];
  } else {
    echo '<p>You must login to view your recommendations</p>';
    die();
  }
?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
if (!isset($_GET['page'])) { $curr_page = 1; }
else { $curr_page = $_GET['page']; }

$results_per_page = 5; // Number of results to display per page

  // This page is for showing a buyer recommended items based on their bid 
  // history.

  // Get every user's bid history and count the auctions they have bid on in common with the current user
  $sql_rank_similar_users = "SELECT DISTINCT u1.username, COUNT(*) AS rank
      FROM Users u1, Bids b1, Bids b2
      WHERE u1.username = b1.username AND b2.username = '".$_SESSION['username']."' AND b1.auctionId = b2.auctionId AND b1.username != b2.username
      GROUP BY u1.username";

  // Get all auctionIDs that the users from the resulting query have bid on that the current user has not bid on
  // Sum the rankings for each auctionId and order by the sum
  // Add auction details to each row if the endDate is after the current date
  $sql_recommendations = "SELECT recoms.auctionId, recoms.title, recoms.details, recoms.endDate, recoms.category, recoms.rank, COUNT(b.bidID) as numBids,
        CASE WHEN COUNT(b.bidId) > 0 THEN MAX(b.bidPrice)
        ELSE recoms.startingPrice
        END AS currentPrice
      FROM (
        SELECT a.auctionId, a.title, a.details, a.endDate, a.category, a.startingPrice, SUM(rank) AS rank 
        FROM (".$sql_rank_similar_users.") AS similar_users, Auctions a
        WHERE a.auctionId IN (
            SELECT bw1.auctionId
            FROM Bids bw1
            WHERE bw1.username = similar_users.username
        ) AND a.auctionId NOT IN (
            SELECT bw2.auctionId
            FROM Bids bw2
            WHERE bw2.username = '".$_SESSION['username']."'
        )
        GROUP BY a.auctionId
      ) as recoms
      JOIN Bids as b on recoms.auctionId = b.auctionId
      WHERE recoms.endDate >= NOW()
      GROUP BY recoms.auctionId
      ORDER BY rank DESC";

  $result = mysqli_query($conn, $sql_recommendations);
  $rowcount = mysqli_num_rows($result);
  $max_page = ceil($rowcount / $results_per_page);
?>

<div class="container mt-5">

  <?php if ($rowcount == 0) {
    echo '<p class="text-center">No results found.</p>';
    die();
  }
  ?>

  <ul class="list-group">
    <?php
    $rowsShown = ($curr_page - 1) * $results_per_page;
    for($i = $rowsShown; $i < $rowsShown + $results_per_page && !($i >= $rowcount); $i++) {
      mysqli_data_seek($result, $i);
      $row = mysqli_fetch_assoc($result);
      print_listing_li($row['auctionId'], $row['title'], $row['details'], $row['currentPrice'], $row['numBids'], new DateTime($row['endDate']));
    }
    ?>
  </ul>

  <!-- Pagination for results listings -->
  <nav aria-label="Search results pages" class="mt-5">
    <ul class="pagination justify-content-center">
      <?php
      $high_page_boost = max(3 - $curr_page, 0);
      $low_page_boost = max(2 - ($max_page - $curr_page), 0);
      $low_page = max(1, $curr_page - 2 - $low_page_boost);
      $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

      if ($curr_page != 1) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="recommendations.php?page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
      }

      for ($i = $low_page; $i <= $high_page; $i++) {
        if ($i == $curr_page) {
          // Highlight the link
          echo ('
    <li class="page-item active">');
        } else {
          // Non-highlighted link
          echo ('
    <li class="page-item">');
        }

        // Do this in any case
        echo ('
      <a class="page-link" href="recommendations.php?page=' . $i . '">' . $i . '</a>
    </li>');
      }

      if ($curr_page != $max_page) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="recommendations.php?page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
      }
      ?>
    </ul>
  </nav>
</div>