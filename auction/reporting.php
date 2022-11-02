<?php include_once("header.php") ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<br>
<div class="container">
    <h1 class='text-center'>Reporting</h1>
    <br>
    <div class="row">
        <div class="col-sm">
            <h5 class="text-center">Average Price of Auctions by Category</h5>
            <canvas id="avgPriceAuction" width="250" height="250"></canvas>
        </div>
        <div class="col-sm">
            <h5 class="text-center">Average Number of Bids by Category</h5>
            <canvas id="avgNumBids" width="250" height="250"></canvas>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm">
            <h5 class="text-center">Number of Auctions by Category</h5>
            <canvas id="auctionsPerCategory" width="250" height="250"></canvas>
        </div>
        <div class="col-sm">
            <h5 class="text-center">Number of Users</h5>
            <canvas id="ratioSellersBuyers" width="250" height="250"></canvas>
        </div>
    </div>
</div>
<script>
    function generateRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    };

    /////DOM elements
    const avgPriceAuction = document.getElementById('avgPriceAuction');
    const avgNumBids = document.getElementById('avgNumBids');
    const auctionsPerCategory = document.getElementById('auctionsPerCategory');
    const ratioSellersBuyers = document.getElementById('ratioSellersBuyers');


    //Average price of auctions per category
    const avgPriceAuctionsData = <?php
                                    $sql = "SELECT AVG(currentPrice) as avgPrice, category FROM(
        SELECT a.auctionId, a.category,
             case when count(b.bidId) > 0 then MAX(b.bidPrice)
             else a.startingPrice
             end as currentPrice 
             FROM auctions as a left join bids as b on a.auctionId = b.auctionId
             group by a.auctionId, a.title, a.details, a.endDate, a.category, a.startingPrice
            ) as prices
          GROUP BY category";
                                    $result = $conn->query($sql);
                                    $data = array();
                                    while ($row = $result->fetch_assoc()) {
                                        $data[] = $row;
                                    }
                                    echo json_encode($data);
                                    ?>;
    const avgPriceAuctionChart = new Chart(avgPriceAuction, {
        type: 'pie',
        data: {
            labels: avgPriceAuctionsData.map((row) => row.category),
            datasets: [{
                data: avgPriceAuctionsData.map((row) => row.avgPrice),
                backgroundColor: avgPriceAuctionsData.map((row) => generateRandomColor()),
                hoverOffset: 4
            }]
        }
    });

    //Average number of bids per category
    const avgNumBidsData = <?php
                            $sql = "SELECT AVG(numBids) as avgBids, category
        FROM (
        SELECT count(b.bidId) as numBids, a.auctionId as auctionId, a.category as category 
            from Bids b join Auctions a on b.auctionId = a.auctionId group by a.category, a.auctionId
            ) as counts
        GROUP BY category";
                            $result = $conn->query($sql);
                            $data = array();
                            while ($row = $result->fetch_assoc()) {
                                $data[] = $row;
                            }
                            echo json_encode($data);

                            ?>;
    const avgNumBidsChart = new Chart(avgNumBids, {
        type: 'bar',
        data: {
            labels: avgNumBidsData.map((row) => row.category),
            datasets: [{
                label: 'Avgerage number of bids per category',
                backgroundColor: avgNumBidsData.map((row) => generateRandomColor()),
                data: avgNumBidsData.map((row) => row.avgBids),
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Average number of bids'
                    }
                }
            }
        },
    });

    // Number of auctions per category
    const auctionsPerCategoryData = <?php
                                    $sql = "SELECT COUNT(*) AS numAuctions, category FROM Auctions GROUP BY category";
                                    $result = mysqli_query($conn, $sql);
                                    $data = array();
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $data[] = $row;
                                    }
                                    echo json_encode($data);
                                    ?>;
    const auctionsPerCategoryChart = new Chart(auctionsPerCategory, {
        type: 'bar',
        data: {
            labels: auctionsPerCategoryData.map((row) => row.category),
            datasets: [{
                label: 'Number of auctions',
                backgroundColor: auctionsPerCategoryData.map((row) => generateRandomColor()),
                data: auctionsPerCategoryData.map((row) => row.numAuctions),
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of auctions'
                    }
                }
            }
        },
    });

    //Ratio of sellers to buyers
    const ratioSellersBuyersData = <?php
                                    $result = mysqli_query($conn, "select count(*) as num_users, seller from Users group by seller");
                                    $row = mysqli_fetch_array($result);
                                    $num_users->buyer = $row['num_users'];
                                    $row = mysqli_fetch_array($result);
                                    $num_users->seller = $row['num_users'];
                                    echo json_encode($num_users);
                                    ?>;
    const ratioSellersBuyersChart = new Chart(ratioSellersBuyers, {
        type: 'pie',
        data: {
            labels: [
                'Buyers',
                'Sellers',
            ],
            datasets: [{
                data: [ratioSellersBuyersData.buyer, ratioSellersBuyersData.seller],
                backgroundColor: [
                    generateRandomColor(),
                    generateRandomColor(),
                ],
                hoverOffset: 4
            }]
        }
    });
</script>