<!DOCTYPE html>
<html>

<body>
    <form action="tests.php" method="post" autocomplete="off">
        <input type="text" name="textAction" />
        <input type="submit" value="submit" />
    </form>
    <br>
    <?php
    if (isset($_POST['textAction'])) {
        $message = $_POST['textAction'];
    } else {
        $message = "";
    }
    echo $message;

    if (isset($_GET['myArg'])) {
        $queryMessage = $_GET['myArg'];
        echo "This message came from a query: $queryMessage";
    }
    ?>
</body>

</html>