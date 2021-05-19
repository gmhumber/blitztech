<?php
// Start or resume a session
session_start();

// Load Users XML file
$xmldoc = new DOMDocument;
$xmldoc->load('../XML/users.xml');

$inputEmail = $_POST['email'];
$inputPassword = $_POST['password'];

// Prepare to validate the inputted values with known correct values by getting a node list of all "user" elements
$validatedUserId;
$usersNodeList = $xmldoc->getElementsByTagName("user");

// Use a for-loop to iterate over the "user" node list and try to find an existing user with an email and password that matches the inputted values 
for ($i = 0; $i < $usersNodeList->length; $i++) {
    $knownEmail = $usersNodeList[$i]
                    ->getElementsByTagName("email")
                    ->item(0)
                    ->nodeValue;
    $knownPassword = $usersNodeList[$i]
                        ->getElementsByTagName("password")
                        ->item(0)
                        ->nodeValue;

    // Test the inputted email and password for a match against the user data on record
    if ($knownEmail === $inputEmail) {
        
        if (password_verify($inputPassword, $knownPassword) === true) {

            $validatedUserId = $usersNodeList[$i]
                                ->getElementsByTagName("userId")
                                ->item(0)
                                ->nodeValue;
            $validatedUserPrivilages = $usersNodeList[$i]
                                        ->getElementsByTagName("privilages")
                                        ->item(0)
                                        ->nodeValue;

            // Set some user validation results  
            $_SESSION["validated"] = true;
            $_SESSION["validatedUserId"] = $validatedUserId;
            $_SESSION["validatedUserPrivilages"] = $validatedUserPrivilages;

            // Redirect user to tickets list if validation succeeds
            header("Location: ticketlist.php");
            exit();

        }
    }
}

// Redirect user to the login page if validation fails or the user tried to access this page without first logging in
header("Location: login.php?error=true");
exit();



?>