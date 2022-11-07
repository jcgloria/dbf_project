<?php
include_once("header.php");
#Get the session of the user
session_start();
if (isset($_SESSION) && $_SESSION['logged_in']) {
    $username = $_SESSION['username'];
} else {
    echo '<p>You must login to view your profile</p>';
    die();
}
?>

<div class="container">
    <h2 class="my-3">Your profile</h2>

    <?php
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $accountType = $row['seller'];
    $email = $row['email'];
    // $password = $row['password'];
    ?>

    <form method="POST" action="process_profile_update.php">
        <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label text-right">Username:</label>
            <label class="col-sm-2 col-form-label text-left"><b><?php echo $username; ?></b></label>
        </div>
        <div class="form-group row">
            <label for="accountType" class="col-sm-2 col-form-label text-right">Account type:</label>
            <?php if ($accountType == false) : ?>
                <div class="col-sm-2 col-form-label text-left"><b>Buyer</b></label>
                    <div class="col-sm-10">
                        <input class="form-check-input" type="checkbox" name="accountType" id="accountType" value="seller">
                        <label class="form-check-label" for="accountType">Upgrade to seller?</label>
                    </div>
                </div>
            <?php else : ?>
                <label class="col-sm-2 col-form-label text-left"><b>Seller</b></label>
            <?php endif; ?>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label text-right">Email:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="email" id="email" placeholder="Email" value="<?php echo $email; ?>">
                <small id="emailHelp" class="form-text text-muted"></small>
            </div>
        </div>
        <!-- <div class="form-group row">
        <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
        <div class="col-sm-10">
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>">
        <small id="passwordHelp" class="form-text text-muted"></small>
        </div>
    </div>
    <div class="form-group row">
        <label for="confirmPassword" class="col-sm-2 col-form-label text-right">Confirm Password</label>
        <div class="col-sm-10">
        <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" value="<?php echo $password; ?>">
        <small id="confirmPasswordHelp" class="form-text text-muted"></small>
    </div> -->
        <div class="form-group row">
            <button type="submit" class="btn btn-primary form-control" name='update'>Update</button>
        </div>
    </form>
</div>

<?php include_once("footer.php") ?>