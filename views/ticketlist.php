<?php

// Start or resume a session
session_start();

// Redirect user if they are not logged in
if (!isset($_SESSION["validated"]) 
    || !isset($_SESSION["validatedUserId"])
    || !isset($_SESSION["validatedUserPrivilages"])) {
    
    header("Location: login.php?error=true");
    exit();
}

// Load tickets XML file
$xmldoc = new DOMDocument;
$xmldoc->load('../XML/tickets.xml');
$xpath = new DOMXPath($xmldoc);

// Get a list of all tickets that the user has access to
if ($_SESSION["validatedUserPrivilages"] === "user") {

    $validatedUserId = $_SESSION["validatedUserId"];
    $ticketsNodeList = $xpath->evaluate("//ticket[submitterId/text()=$validatedUserId]");

} else if ($_SESSION["validatedUserPrivilages"] === "admin") {

    $ticketsNodeList = $xmldoc->getElementsByTagName("ticket");

} else {

    header("Location: error.php");
    exit();

}

// Use a for-loop to construct the HTML code that will display the ticket summaries
$ticketsHtmlOutput = "";
for ($i=0; $i < $ticketsNodeList->length; $i++) {
    $ticketId = $ticketsNodeList[$i]
                ->getElementsByTagName("ticketId")
                ->item(0)
                ->nodeValue;
    $status = $ticketsNodeList[$i]
                ->getElementsByTagName("status")
                ->item(0)
                ->nodeValue;
    $openedDate = $ticketsNodeList[$i]
                ->getElementsByTagName("openedDate")
                ->item(0)
                ->nodeValue;

    $openedDate = date_create($openedDate);
    $formattedDate = date_format($openedDate, "Y/m/d H:i:s");
                
    $ticketsHtmlOutput .= <<<OUTPUT
        <tr>
            <th scope="row">$ticketId</th>
            <td>$formattedDate</td>
            <td>$status</td>
            <td><a href="../views/ticketdetails.php?ticketId=$ticketId">Details</a></td>
        </tr>
OUTPUT;

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
    <title>Ticket List</title>
</head>
<body class="container">
    <header>
        <div>
            <nav class="navbar navbar-expand-lg navbar-light bg-primary">
                <a class="navbar-brand title text-white" href="login.php">BlitzTech Support</a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="p-3">
        <div class="table-responsive-lg mb-5">
                <h1 class="h3">Ticket Details</h1>
                <?= $_SESSION["validatedUserPrivilages"] === "user" ? '<p class="float-md-right"><a href="createticket.php">Create New Ticket</a></p>' : "" ?>
                <table class="table">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Ticket ID</th>
                            <th scope="col">Opening Date</th>
                            <th scope="col">Status</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $ticketsHtmlOutput ?>
                    </tbody>
                </table>
            </div>
    </main>
</body>
</html>