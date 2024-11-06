 <!-- Navigation Bar -->
 <nav class="navbar navbar-dark bg-dark shadow-lg text-white">

<div class="container">

    <!-- Site title -->
    <a class="navbar-brand" href="index.php">
        <img src="files/logo.png" alt="" width="25" height="25" class="d-inline-block align-text-top">
        <span id="menuTitle">PSYCHOACOUSTICS-WEB</span>
    </a>

    <!-- user Login and Settings Buttons -->
    <form class="d-flex align-items-center">
        
        <?php
        //if the user is not logged show the login buttons
        if (!isset($_SESSION['currentLoggedUsername'])) {
        ?>
            <button id="menuSignUp" class="btn btn-outline-light me-3" type="button" onclick="location.href='register.php'">
                Sign Up
            </button>
            <button id="menuLogIn" class="btn btn-outline-light me-3" type="button" onclick="location.href='login.php'">
                Log In
            </button>

        <?php
            //if the user is logged show the welcome message and the 'your test' and 'logout' buttons
        } else { ?>
            <label id="menuWelcome" class='text-white navbar-text me-3'>Welcome <?php echo $_SESSION['currentLoggedUsername'];
                                                                                echo '   #' . $_SESSION['currentLoggedID']; ?></label>
            <button id="menuYourTests" class="btn btn-outline-light me-3" type="button" onclick="location.href='yourTests.php'">
                Your tests
            </button>
            <button id="menuLogOut" class="btn btn-outline-light me-3" type="button" onclick="location.href='php/logout.php'">
                Log Out
            </button>
            <a class='settings navbar-text' href='userSettings.php'>
                <i class='material-icons rotate text-white'>settings</i>
            </a>
        <?php } ?>

    </form>
</div>
</nav>