<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">

  <h2 class="my-3">Browse listings</h2>

  <div id="searchSpecs">
    <!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
    <form method="get" action="browse.php">
      <div class="row">
        <div class="col-md-5 pr-0">
          <div class="form-group">
            <label for="keyword" class="sr-only">Search keyword:</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-transparent pr-0 text-muted">
                  <i class="fa fa-search"></i>
                </span>
              </div>
              <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything">
            </div>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-group">
            <label for="cat" class="sr-only">Search within:</label>
            <select class="form-control" id="cat" name="cat">
              <option selected value="all">All categories</option>
              <?php
              $sql = "SELECT category FROM Categories";
              $result = mysqli_query($conn, $sql);
              while ($rows = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $rows['category'] . '">' . $rows['category'] . "</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-inline">
            <label class="mx-2" for="order_by">Sort by:</label>
            <select class="form-control" id="order_by" name="order_by">
              <option selected value="pricelow">Price (low to high)</option>
              <option value="pricehigh">Price (high to low)</option>
              <option value="date">Soonest expiry</option>
            </select>
          </div>
        </div>
        <div class="col-md-1 px-0">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </div>
      <div class="form-inline">
        <p>Filter Price Range:</p>
        <div class="col-md-3">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">£</span>
            </div>
            <input name="priceLow" type="text" class="form-control" placeholder="Low">
          </div>
        </div>
        <div class="col-md-3">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">£</span>
            </div>
            <input name="priceHigh" type="text" class="form-control" placeholder="High">
          </div>
        </div>
      </div>
    </form>
  </div> <!-- end search specs bar -->


</div>

<?php
$where_clause = "a.endDate >= NOW()";
$order_clause = "";
$outside_where_clause = "1=1";
// Retrieve these from the URL
if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
  $where_clause .= " AND (a.title LIKE '%$keyword%' OR a.details LIKE '%$keyword%')";
}

if (isset($_GET['cat'])) {
  $category = $_GET['cat'];
  if ($category != "all") {
    $where_clause .= " AND a.category = '$category'";
  }
}

if (isset($_GET['has_img'])) {
  $category = $_GET['has_img'];
  $where_clause .= " AND a.image is not null";
}

if (isset($_GET['priceLow']) && !empty($_GET['priceLow'])) {
  $priceLow = $_GET['priceLow'];
  $outside_where_clause .= " AND items.currentPrice >= '$priceLow'";
}
if (isset($_GET['priceHigh']) && !empty($_GET['priceHigh'])) {
  $priceHigh = $_GET['priceHigh'];
  $outside_where_clause .= " AND items.currentPrice <= '$priceHigh'";
}

if (isset($_GET['order_by'])) {
  $ordering = $_GET['order_by'];
  switch ($ordering) {
    case "pricelow":
      $order_clause = "ORDER BY currentPrice ASC";
      break;
    case "pricehigh":
      $order_clause = "ORDER BY currentPrice DESC";
      break;
    case "date":
      $order_clause = "ORDER BY a.endDate ASC";
      break;
    default:
  }
}

if (!isset($_GET['page'])) {
  $curr_page = 1;
} else {
  $curr_page = $_GET['page'];
}

$results_per_page = 10; // Number of results to display per page
$curr_page_start_item = $results_per_page * ($curr_page - 1); // Index of first item on current page


$query = "SELECT * FROM (SELECT a.auctionId, a.title, a.details, a.endDate, a.category,
     case when count(b.bidId) > 0 then MAX(b.bidPrice)
     else a.startingPrice
     end as currentPrice, 
     count(b.bidId) as numBids
     FROM auctions as a  left join bids as b on a.auctionId = b.auctionId
     where $where_clause
     group by a.auctionId, a.title, a.details, a.endDate, a.category, a.startingPrice
     $order_clause
     limit $curr_page_start_item, $results_per_page) as items
     where $outside_where_clause";
$result = mysqli_query($conn, $query);
$rowcount = mysqli_fetch_row(mysqli_query($conn, "select count(*) from Auctions as a where $where_clause"))[0]; // Total number of results
$max_page = ceil($rowcount / $results_per_page);
echo $query;
?>

<div class="container mt-5">

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

      <?php if ($rowcount > 0) {

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
      } else {
        echo '<p class="text-center">No results found.</p>';
      }
      ?>

    </ul>
  </nav>


</div>



<?php include_once("footer.php") ?>