<?php
session_start();
include_once "php/config.php";
include_once "php/dbCommonFunctions.php";
include_once "php/dbconnect.php";

if (!isset($_SESSION['currentLoggedID'])) {
    header("Location: index.php?err=2");
    exit;
}

try {
    $conn = connectdb();
    //fetch all data to display on page
    $sql = "SELECT referral, name, surname, date, gender, notes, email 
                FROM account INNER JOIN guest ON account.Guest_ID = guest.ID 
                WHERE username='" . $_SESSION['currentLoggedUsername'] . "'";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

} catch (Exception $e) {
    header("Location: index.php?err=db");
}

$ref = $row['referral'];
$name = $row['name'];
$sur = $row['surname'];
$date = $row['date'];
$gender = $row['gender'];
$notes = $row['notes'];
$email = $row['email'];

//fetch data to print current test type on screen
try {

    $refrow = fetchReferralInfo($ref, $conn); //return an array with referral data

    $_SESSION['referralTest'] = array( //gather referral data
        "guest" => $refrow['fk_GuestTest'],
        "count" => $refrow['fk_TestCount']
    );

    $sql = "SELECT Type FROM test WHERE Guest_ID='{$_SESSION['referralTest']['guest']}' AND Test_count='{$_SESSION['referralTest']['count']}'";
    $result = $conn->query($sql);
    $refrow = $result->fetch_assoc();
    if (isset($refrow['Type'])) {
        $testTypeExt = $refrow['Type'];
    } else
        $testTypeExt = 'No test created yet';

} catch (Exception $e) { //if invalid
    header("Location: ../demographicData.php?" . $type . $ref . "&ref=&err=3");
    exit;
}

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
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/staircaseStyle.css" rel="stylesheet">
    <!--		<link rel ="stylesheet" href="css/style.css-->
    <?php //if (isset($_SESSION['version'])) echo "?{$_SESSION['version']}"; 
    ?><!--">-->
    <script type="text/javascript" src="js/funzioni.js<?php if (isset($_SESSION['version'])) echo "?{$_SESSION['version']}"; ?>"></script>

    <title>Psychoacoustics-web - User settings</title>
</head>

<body>

    <!-- Navigation Bar -->
    <?php include_once 'html_modules/navbar.php'; ?>

    <?php
    //se si sceglie un username già esistente verrà messo "?err=1" nell'url
    if (isset($_GET['err'])) {

        if ($_GET['err'] == 0)
            echo "<div class='alert alert-danger'>Some inserted characters aren't allowed</div>";
        if ($_GET['err'] == 1)
            echo "<div class='alert alert-danger'>Username already taken</div>";
        if ($_GET['err'] == 2)
            echo "<div class='alert alert-danger'>Wrong password</div>";
        if ($_GET['err'] == 3)
            echo "<div class='alert alert-success'>Password changed</div>";
        if ($_GET['err'] == 4)
            echo "<div class='alert alert-success'>Test settings changed</div>";
        if ($_GET['err'] == 5)
            echo "<div class='alert alert-danger'>Select a test type from the menu</div>";
        if ($_GET['err'] == 6)
            echo "<div class='alert alert-danger'>the created test already exist, it is now your active referral</div>";
    }  
    ?>

    <div class="container my-5">

        <!-- Test Settings section -->
        <div class="container-fluid p-4 border rounded-4 bg-light">
            <h4 class="mb-3">Test settings</h4>
            <form action="php/newReferral.php" class="settingForm ref">
                <div class="row row-cols-1 row-cols-lg-2 g-3 justify-content-center align-items-center">
                    
                    <!-- invite code box -->
                    <div class="col">
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text title" title="click to copy">Invite code</span>
                            <span class="input-group-text form-control link" id="ref" title="click to copy"><?php echo $ref; ?></span>
                        </div>
                    </div>

                    <!-- complete referral link box -->
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text title" title="click to copy">Link</span>
                            <span class="input-group-text form-control overflow-scroll link" id="link" title="click to copy">localhost/acoustic-web2/demographicData.php?ref=<?php echo $ref; ?></span>
                        </div>
                    </div>

                    <!-- test type selection -->
                    <div class="col">
                        <select name='testType' class="form-select" onchange="updateLink('<?php echo $ref; ?>')" id="testType">
                            <option selected disabled value=''> Select a Test Type</option>
                            <option value='amp'>Pure tone intensity</option>
                            <option value='freq'>Pure tone frequency</option>
                            <option value='dur'>Pure tone duration</option>
                            <option value='gap'>Noise Gap</option>
                            <option value='ndur'>Noise Duration</option>
                            <option value='nmod'>Noise Modulation</option>
                        </select>
                    </div>

                    
                    <div class="col">
                        <div class="row row-cols-2 g-3">
                            
                        <!-- current test type -->
                            <div class="col d-grid">
                                <h6 class="mb-0">test type: <?php echo $testTypeExt; ?></h6>
                                <!--<button type="submit" class="btn btn-primary btn-red">Change invite code</button>-->
                            </div>

                            <!-- change test settings button -->
                            <div class="col d-grid">
                                <button type="button" class="btn btn-primary btn-red" onclick="window.location='php/updateSavedSettings.php?test='+document.getElementById('testType').value">
                                    Change test settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <?php
        //this section is active only is a Superuser is logged --ignore
        try {
            $sql = "SELECT Type FROM account WHERE Guest_ID='{$_SESSION['currentLoggedID']}' AND Username='{$_SESSION['currentLoggedUsername']}'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            if (isset($row['Type']) && $row['Type'] == 1) { ?>
                <div class="container-fluid p-4 border rounded-4 bg-light mt-5">
                    <h4 class="mb-3">Create new superuser</h4>
                    <form action="php/newUsername.php" method="POST" class="settingForm ref">
                        <div class="row row-cols-1 row-cols-lg-2 g-3 justify-content-center align-items-center">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text title" onclick="copy('ref')" title="Username">Username</span>
                                    <input type="text" class="form-control" placeholder="Username" name="username">
                                </div>
                            </div>
                            <div class="col d-grid">
                                <button type="submit" class="btn btn-primary btn-red">Create new Superuser</button>
                            </div>
                        </div>
                    </form>
                </div>
        <?php }
        } catch (Exception $e) {
            header("Location: index.php?err=db");
        }
        ?>

        <!-- change password section -->
        <div class="container-fluid p-4 border rounded-4 bg-light mt-5">
            <h4 class="mb-3">Change password</h4>
            <form action="php/changePsw.php" method="post" class="settingForm">
                <div class="row row-cols-1 row-cols-lg-3 g-3 justify-content-center align-items-center">
                    
                    <!-- old psw form -->
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">Old password</span>
                            <input type="password" class="form-control" placeholder="Old password" name="oldPsw">
                        </div>
                    </div>

                    <!-- new psw form -->
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">New password</span>
                            <input type="password" class="form-control" placeholder="New password" name="newPsw">
                        </div>
                    </div>

                    <!-- change psw button -->
                    <div class="col d-grid">
                        <button type="submit" class="btn btn-primary btn-red">Change Password</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- change user settings section -->
        <div class="container-fluid p-4 border rounded-4 bg-light mt-5">
            <h4 class="mb-3">Change user settings</h4>
            <form method="post" action="php/changeUserData.php" class="settingForm">
                <div class="row row-cols-1 row-cols-lg-2 g-3 justify-content-center align-items-center">
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">Username</span>
                            <input type="text" class="form-control" name="usr" value="<?php echo $_SESSION['currentLoggedUsername']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">Email</span>
                            <input type="text" class="form-control" name="email" value="<?php echo $email; ?>">
                        </div>
                    </div>

                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">Name</span>
                            <input type="text" class="form-control" name="name" value="<?php echo $name; ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">Surname</span>
                            <input type="text" class="form-control" name="surname" value="<?php echo $sur; ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">Birth date</span>
                            <input type="date" class="form-control" name="date" value="<?php echo $date; ?>">
                        </div>
                    </div>
                    <div class="col">
                        <select class="form-select" id="gender" name="gender">
                            <option value="" selected disabled>Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col">
                        <div class="input-group notes">
                            <span class="input-group-text">Notes</span>
                            <input type="text" class="form-control" placeholder="Notes" name="notes" value="<?php echo $notes; ?>">
                        </div>
                    </div>
                    <div class="col d-grid">
                        <button type="submit" class="btn btn-primary btn-red">Save</button>
                    </div>
                </div>
            </form>
        </div>
</body>

</html>