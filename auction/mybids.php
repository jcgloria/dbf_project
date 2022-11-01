<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">

  <h2 class="my-3">My bids</h2>

  <?php
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  } else {
    $curr_page = $_GET['page'];
  }

  #Get the session of the user
  session_start();
  if (isset($_SESSION) && $_SESSION['logged_in']) {
    $username = $_SESSION['username'];
  } else {
    echo '<p>You must login to view your bids</p>';
    die();
  }

  $where_clause = "Exists (select * from bids as b where b.auctionId = a.auctionId and b.username = '$username')";


  $results_per_page = 10; // Number of results to display per page
  $curr_page_start_item = $results_per_page * ($curr_page - 1); // Index of first item on current page


  $query = "SELECT a.auctionId, a.title, a.details, a.endDate, a.category,
     case when count(b.bidId) > 0 then MAX(b.bidPrice)
     else a.startingPrice
     end as currentPrice, 
     count(b.bidId) as numBids
     FROM auctions as a  left join bids as b on a.auctionId = b.auctionId
     where $where_clause
     group by a.auctionId, a.title, a.details, a.endDate, a.category, a.startingPrice
     limit $curr_page_start_item, $results_per_page";
  $result = mysqli_query($conn, $query);
  $rowcount = mysqli_fetch_row(mysqli_query($conn, "select count(*) from Auctions as a where $where_clause"))[0]; // Total number of results
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
      while ($row = mysqli_fetch_array($result)) {
        print_listing_li($row['auctionId'], $row['title'], $row['details'], $row['currentPrice'], $row['numBids'], new DateTime($row['endDate']));
      }
      ?>
    </ul>


    <!-- Pagination for results listings -->
    <nav aria-label="Search results pages" class="mt-5">
      <ul class="pagination justify-content-center">

        <?php

        // Copy any currently-set GET variables to the URL.
        $querystring = "";
        foreach ($_GET as $key => $value) {
          if ($key != "page") {
            $querystring .= "$key=$value&amp;";
          }
        }

        $high_page_boost = max(3 - $curr_page, 0);
        $low_page_boost = max(2 - ($max_page - $curr_page), 0);
        $low_page = max(1, $curr_page - 2 - $low_page_boost);
        $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

        if ($curr_page != 1) {
          echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
        }

        if ($curr_page != $max_page) {
          echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
        }
        ?>

      </ul>
    </nav>


  </div>

  <?php include_once("footer.php") ?>