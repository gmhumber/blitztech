<?php
// Start or resume a session
session_start();

// Redirect user if they are already logged in
if (isset($_SESSION["validated"]) 
    && isset($_SESSION["validatedUserId"])
    && isset($_SESSION["validatedUserPrivilages"])
    && $_SESSION["validated"] === true) {
    
        header("Location: ticketlist.php");
        exit();
}

// Display error message if a previous login attempt failed
$errorFlag = false;
if (isset($_REQUEST["error"]) && $_REQUEST["error"] === "true") {
    
    $errorFlag = true;
    $errorMessage = '<div class="form-group"><p class="text-danger error-message">Please enter a valid email and password to continue.</p></div>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous" />
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/styles.css" />
    <title>Login</title>
</head>
<body>
    <header>
        <div class="container-fluid">
            <nav class="navbar navbar-expand-lg navbar-light bg-primary">
                <a class="navbar-brand title text-white" href="login.php">BlitzTech Support</a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php">Login</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="text-center">
        <div class="d-flex justify-content-center">
            <div class="form-group col-md-4 my-5">
                <form class="form-signin" action="postlogin.php" method="post">
                    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
                    <div class="form-group">
                        <label for="email" class="sr-only">Email address</label>
                        <input type="email" name="email" id="email" class="form-control my-3" placeholder="Email address" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password" class="sr-only">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    </div>
                        <?= $errorFlag ? $errorMessage : "" //Insert error message if appropriate ?>
                    <div class="form-group">
                        <button class="btn btn-md btn-primary my-2" type="submit">Sign in</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>