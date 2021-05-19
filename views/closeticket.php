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

// Display error page if the ticketId is not provided
if (!isset($_GET['ticketId'])) {

    header("Location: error.php");
    exit();
}

// Load tickets XML file
$xmltickets = new DOMDocument;
$xmltickets->preserveWhiteSpace = false;
$xmltickets->formatOutput = true;
$xmltickets->load('../XML/tickets.xml');
$xpathtickets = new DOMXPath($xmltickets);

$ticketId = $_GET['ticketId'];

// Retrieve the ticket nodes to be manipulated
$ticketNode = $xpathtickets->evaluate("//ticket[ticketId/text()=$ticketId]")->item(0);
$statusNode = $ticketNode->getElementsByTagName("status")->item(0);
$ticketDateTimesNode = $ticketNode->getElementsByTagName("ticketDateTimes")->item(0);

//Construct new elements to close the ticket and save those to the XML file
$newStatusElement = $xmltickets->createElement("status", "resolved");
$ticketNode->replaceChild($newStatusElement, $statusNode);

$resolvedDate = date("Y-m-d\TH:i:s");
$newResolvedDateTimeElement = $xmltickets->createElement("resolvedDate", $resolvedDate);
$ticketDateTimesNode->appendChild($newResolvedDateTimeElement);

$xmltickets->save('../XML/tickets.xml');

// Redirect user to updated ticket details page
header("Location: ticketdetails.php?ticketId=$ticketId");
exit();


?>