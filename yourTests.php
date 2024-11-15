<?php
session_start();
include "php/config.php";
include_once "php/dbconnect.php";

//check if there is a user logged
if (!isset($_SESSION['currentLoggedUsername']) || !isset($_SESSION['currentLoggedID']))
    header("Location: index.php");

?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="files/logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/staircaseStyle.css">
    <script src="js/libraries/maintainscroll.js"></script>

    <title>Psychoacoustics-web - Test results</title>
</head>


<body>

    <!-- Navigation Bar -->
    <?php include_once 'html_modules/navbar.php'; ?>


    <div class="container">
        <h1 class="mt-5 pt-5">Welcome <?php echo $_SESSION['currentLoggedUsername']; ?> <?php echo '  #' . $_SESSION['currentLoggedID']; ?> </h1>
        <div class="row g-3">

            <!-- download all your data Button -->
            <div class="col d-grid">
                <button type='button' class='btn btn-primary btn-lg btn-red'
                    onclick='location.href="php/downloadYours.php?all=1"'>
                    Download all your data
                </button>
            </div>

            <!-- Download all your guest's data Button -->
            <div class="col d-grid">
                <button type='button' class='btn btn-primary btn-lg btn-red'
                    onclick='location.href="php/downloadYours.php?all=0"'>
                    Download all your guest's data
                </button>
            </div>


            <?php
            //function deicated to Superuser
            try {
                $conn = connectdb();

                $usr = $_SESSION['currentLoggedUsername'];
                $id = $_SESSION['currentLoggedID'];

                $sql = "SELECT Type FROM account WHERE Guest_ID='$id' AND Username='$usr'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                if ($row['Type'] == 1) { ?>
                    <div class="col d-grid">
                        <button type='button' class='btn btn-primary btn-lg btn-red'
                            onclick='location.href="php/downloadAll.php"'>
                            Download all the data in the database
                        </button>
                    </div>
            <?php }
            } catch (Exception $e) {
                header("Location: index.php?err=db");
            }
            ?>

        </div>

        <!-- your results Table -->
        <h3 class="mt-5">Your results</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">Test</th>
                    <th scope="col">Time</th>
                    <th scope="col">Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT Guest_ID, Test_count, Timestamp, Type FROM test WHERE Guest_ID='$id' AND ref_name != ''";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["Test_count"]; ?></td>
                            <td><?php echo $row["Timestamp"]; ?></td>
                            <td><?php echo $row["Type"]; ?></td>

                            <?php $TestId = (int)$row['Guest_ID'] ?>
                            <?php $TestCount = (int)$row['Test_count'] ?>

                            <td class="text-end">
                                <form method="post" action="php/deleteRecord.php">
                                    <input type="hidden" name="testId" value="<?php echo $TestId; ?>">
                                    <input type="hidden" name="testCount" value="<?php echo $TestCount; ?>">
                                    <button type="submit" class="btn btn-link text-danger p-0"
                                        id="<?php echo $TestId; ?>"
                                        name="delete_id"
                                        title="ID: <?php echo $TestId; ?> COUNT: <?php echo $TestCount; ?>">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                <?php }
                } catch (Exception $e) {
                    header("Location: index.php?err=db");
                }
                ?>
            </tbody>
        </table>


        <!-- Guest's results Table -->
        <h3 class="mt-5">Your guest's results</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Time</th>
                    <th scope="col">Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT ID, Name, Test_count, Timestamp, Type FROM test INNER JOIN guest ON Guest_ID=ID WHERE fk_guest='{$_SESSION['currentLoggedUsername']}'";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["Name"]; ?></td>
                            <td><?php echo $row["Timestamp"]; ?></td>
                            <td><?php echo $row["Type"]; ?></td>

                            <?php $TestId = (int)$row['ID'] ?>
                            <?php $TestCount = (int)$row['Test_count'] ?>

                            <td class="text-end">
                                <form method="post" action="php/deleteRecord.php">
                                    <input type="hidden" name="testId" value="<?php echo $TestId; ?>">
                                    <input type="hidden" name="testCount" value="<?php echo $TestCount; ?>">
                                    <button type="submit" class="btn btn-link text-danger p-0"
                                        id="<?php echo $TestId; ?>"
                                        name="delete_id"
                                        title="ID: <?php echo $TestId; ?> COUNT: <?php echo $TestCount; ?>">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                <?php }
                } catch (Exception $e) {
                    header("Location: index.php?err=db");
                }
                ?>
        </table>

    </div>


</body>

</html>