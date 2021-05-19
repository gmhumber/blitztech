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

// Redirect user to error page if no ticketId is provided in the query string
if (!isset($_REQUEST["ticketId"])) {
    header("Location: error.php");
    exit();
}

// Load tickets and users XML files
$xmltickets = new DOMDocument;
$xmltickets->load('../XML/tickets.xml');
$xpathtickets = new DOMXPath($xmltickets);
$xmlusers = new DOMDocument;
$xmlusers->load('../XML/users.xml');
$xpathusers = new DOMXPath($xmlusers);

// Retrieve details of the selected ticket
$requestedTicketId = $_REQUEST["ticketId"];
$ticketNode = $xpathtickets->evaluate("//ticket[ticketId/text()=$requestedTicketId]")->item(0);
$submitterId = $ticketNode->getElementsByTagName("submitterId")->item(0)->nodeValue;
$handlerId = $ticketNode->getElementsByTagName("handlerId")->item(0)->nodeValue;
$category = $ticketNode->getElementsByTagName("category")->item(0)->nodeValue;
$status = $ticketNode->getElementsByTagName("status")->item(0)->nodeValue;

// Get ticket opening date
$openedDateObject = date_create($ticketNode->getElementsByTagName("openedDate")->item(0)->nodeValue);
$openedDate = date_format($openedDateObject,"Y/m/d H:i:s");

// Get ticket resolution date
$resolvedDateNodeList = $ticketNode->getElementsByTagName("resolvedDate");
if ($resolvedDateNodeList->length === 0 && $_SESSION["validatedUserPrivilages"] !== "admin") {
    $resolvedDate = "pending";
} else if ($resolvedDateNodeList->length === 0 && $_SESSION["validatedUserPrivilages"] === "admin") {
    $resolvedDate = '<a href="closeticket.php?ticketId=' . $requestedTicketId . '">Close Ticket</a>';
} else {
    $resolvedDateObject = date_create($resolvedDateNodeList->item(0)->nodeValue);
    $resolvedDate = date_format($resolvedDateObject,"Y/m/d H:i:s");
}

// Get submitter's name
$submitterUserNode = $xpathusers->evaluate("//user[userId/text()=$submitterId]")->item(0);
$submitterName = $submitterUserNode->getElementsByTagName("firstName")->item(0)     ->nodeValue . " " . $submitterUserNode->getElementsByTagName("lastName")->item(0)->nodeValue;

// Get handler's name
$handlerUserNode = $xpathusers->evaluate("//user[userId/text()=$handlerId]")->item(0);
$handlerName = $handlerUserNode->getElementsByTagName("firstName")->item(0)     ->nodeValue . " " . $handlerUserNode->getElementsByTagName("lastName")->item(0)->nodeValue;

// Construct the HTML code to output the ticket particulars
$ticketDetails = <<<DETAILS
    <tr>
        <th scope="row">$requestedTicketId</th>
        <td>$submitterName</td>
        <td>$handlerName</td>
        <td>$category</td>
        <td>$status</td>
        <td>$openedDate</td>
        <td>$resolvedDate</td>
    </tr>
DETAILS;

//Get and parse ticket messages
$messagesNodeList = $ticketNode->getElementsByTagName("message");
$messages = "";
if ($messagesNodeList->length === 0) {
    $messages = "";
} else {
    // Use a for-loop to get the message details from each message
    for ($i=0; $i < $messagesNodeList->length; $i++) {
        $senderId = $messagesNodeList[$i]->getElementsByTagName("senderId")->item(0)->nodeValue;
        $senderUserNode = $xpathusers->evaluate("//user[userId/text()=$senderId]")->item(0);
        $senderName = $senderUserNode->getElementsByTagName("firstName")->item(0)     ->nodeValue . " " . $senderUserNode->getElementsByTagName("lastName")->item(0)->nodeValue;
        
        $messageDateObject = date_create($messagesNodeList[$i]->getElementsByTagName("messageDateTime")->item(0)->nodeValue);
        $messageDate = date_format($messageDateObject, "Y/m/d H:i:s");

        $messageBody = $messagesNodeList[$i]->getElementsByTagName("body")->item(0)->nodeValue;

        $messages .= <<<MESSAGE
            <div class="list-group-item list-group-item-action flex-column align-items-start border-dark">
                <h3 class="h5">Sent by: $senderName</h3>
                <p>$messageBody</p>
                <small>Date: $messageDate</small>
            </div>
MESSAGE;
    }
}

// Get memos relating to the ticket and process them for display, but the system will only display the internal memos when the logged in user has administrative privilages
$memos = '<h2 class="h3">Internal Memos</h2><div class="list-group">';
if ($_SESSION["validatedUserPrivilages"] === "admin") {
    $memosNodeList = $ticketNode->getElementsByTagName("memo");
    for ($i=0; $i < $memosNodeList->length; $i++) {
        $memoDateObject = date_create($memosNodeList[$i]->getElementsByTagName("memoDateTime")->item(0)->nodeValue);
        $memoDate = date_format($memoDateObject, "Y/m/d H:i:s");

        $memoAuthorId = $memosNodeList[$i]->getElementsByTagName("authorId")->item(0)->nodeValue;
        $memoAuthorNode = $xpathusers->evaluate("//user[userId/text()=$memoAuthorId]")->item(0);
        $authorName = $memoAuthorNode->getElementsByTagName("firstName")->item(0)     ->nodeValue . " " . $memoAuthorNode->getElementsByTagName("lastName")->item(0)->nodeValue;       

        $note = $memosNodeList[$i]->getElementsByTagName("note")->item(0)->nodeValue;

        $memos .= <<<MEMO
            <div class="list-group-item list-group-item-action flex-column align-items-start border-dark">
                <h3 class="h5">Memo author: $authorName</h3>
                <p>$note</p>
                <small>Date: $memoDate</small>
            </div>

MEMO;

}

    // Add the HTML code to create the Create New Memo form 
    $loggedInUserId = $_SESSION["validatedUserId"];
    $memos .= <<<MEMOFORM
        </div>
        <div class="form=group mt-4">
            <h3 class="h4">Create New Memo</h3>
            <form method="post" action="newmemo.php">
                <input type="hidden" id="ticketId" name="ticketId" value="$requestedTicketId" />
                <input type="hidden" id="authorId" name="authorId" value="$loggedInUserId" />
                <label for="newmemo" class="sr-only">New Memo</label>
                <textarea class="form-control"id="newmemo" name="newmemo" rows="3" required></textarea>
                <button type="submit" class="btn btn-primary mt-3">Create New Memo</button>
            </form>
        </div>
MEMOFORM;

} else {
    $memos = "";
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
    <title>Ticket Details</title>
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
            <h2 class="h3">Ticket Details</h2>
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Ticket ID</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Assigned Rep.</th>
                        <th scope="col">Category</th>
                        <th scope="col">Status</th>
                        <th scope="col">Opened Date</th>
                        <th scope="col">Resolution Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?= $ticketDetails ?>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h2 class="h3">Messages</h2>
                <div class="list-group">
                    <?= $messages ?>                
                </div>

                <div class="form=group mt-4">
                    <h3 class="h4">Send New Message</h3>
                    <form method="post" action="newmessage.php">
                        <input type="hidden" id="ticketId" name="ticketId" value="<?= $requestedTicketId ?>" />
                        <input type="hidden" id="senderId" name="senderId" value="<?= $_SESSION["validatedUserId"] ?>" />
                        <label for="newmessage" class="sr-only">New Message</label>
                        <textarea class="form-control"id="newmessage" name="newmessage" rows="3" required></textarea>
                        <button type="submit" class="btn btn-primary mt-3">Send Message</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <?= $_SESSION["validatedUserPrivilages"] === "admin" ? $memos : "" ?>
            </div>
        </div>
    </main>

</body>
</html>