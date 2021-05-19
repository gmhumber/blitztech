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

// Display error page if a non-admin user tries to access this page
if ($_SESSION["validatedUserPrivilages"] !== "admin") {

    header("Location: error.php");
    exit();
}

// Display error page if the form received is incomplete
if (!isset($_POST['ticketId']) 
    || !isset($_POST['authorId'])
    || !isset($_POST['newmemo'])) {

    header("Location: error.php");
    exit();
}

// Load tickets XML file
$xmltickets = new DOMDocument;
$xmltickets->preserveWhiteSpace = false;
$xmltickets->formatOutput = true;
$xmltickets->load('../XML/tickets.xml');
$xpathtickets = new DOMXPath($xmltickets);


$ticketId = $_POST['ticketId'];
$authorId = $_POST['authorId'];
$newMemo = $_POST['newmemo'];

// Retrieve the ticket nodes to be manipulated
$ticketNode = $xpathtickets->evaluate("//ticket[ticketId/text()=$ticketId]")->item(0);
$memosNode = $ticketNode->getElementsByTagName("memos")->item(0);
$submitterId = $ticketNode->getElementsByTagName("submitterId")->item(0)->nodeValue;
$handlerId = $ticketNode->getElementsByTagName("handlerId")->item(0)->nodeValue;

// Verify that the authorId received as part of the post form data matches the known user Id of the logged in user
if ($_SESSION["validatedUserId"] !== $authorId) {

    header("Location: error.php");
    exit();
}

// Build the new message XML element and save it to the XML file
$memoDate = date("Y-m-d\TH:i:s");
$newMemoElement = $xmltickets->createElement("memo");
$newMemoDateTimeElement = $xmltickets->createElement("memoDateTime", $memoDate);
$newAuthorIdElement = $xmltickets->createElement("authorId", $authorId);
$newNoteElement = $xmltickets->createElement("note", $newMemo);
$newMemoElement->appendChild($newMemoDateTimeElement);
$newMemoElement->appendChild($newAuthorIdElement);
$newMemoElement->appendChild($newNoteElement);
$memosNode->appendChild($newMemoElement);
$xmltickets->save('../XML/tickets.xml');

// Redirect user to updated ticket details page
header("Location: ticketdetails.php?ticketId=$ticketId");
exit();

?>