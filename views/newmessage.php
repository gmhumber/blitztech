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

// Display error page if the form received is incomplete
if (!isset($_POST['ticketId']) 
    || !isset($_POST['senderId'])
    || !isset($_POST['newmessage'])) {

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
$senderId = $_POST['senderId'];
$newMessage = $_POST['newmessage'];

// Retrieve the ticket nodes to be manipulated
$ticketNode = $xpathtickets->evaluate("//ticket[ticketId/text()=$ticketId]")->item(0);
$messagesNode = $ticketNode->getElementsByTagName("messages")->item(0);
$submitterId = $ticketNode->getElementsByTagName("submitterId")->item(0)->nodeValue;
$handlerId = $ticketNode->getElementsByTagName("handlerId")->item(0)->nodeValue;

// Verify that the senderId received as part of the post form data matches the known user Id of the logged in user
if ($_SESSION["validatedUserId"] !== $senderId) {

    header("Location: error.php");
    exit();
}

// Verify that the user submitting the message is a party to the ticket or is an admin, otherwise redirect the user to the error page
if (!($_SESSION["validatedUserId"] === $submitterId || $_SESSION["validatedUserId"] === $handlerId || $_SESSION["validatedUserPrivilages"] === "admin")) {

    header("Location: error.php");
    exit();
}

// Build the new message XML element and save it to the XML file
$messageDate = date("Y-m-d\TH:i:s");
$newMessageElement = $xmltickets->createElement("message");
$newMessageDateTimeElement = $xmltickets->createElement("messageDateTime", $messageDate);
$newSenderIdElement = $xmltickets->createElement("senderId", $senderId);
$newBodyElement = $xmltickets->createElement("body", $newMessage);
$newMessageElement->appendChild($newMessageDateTimeElement);
$newMessageElement->appendChild($newSenderIdElement);
$newMessageElement->appendChild($newBodyElement);
$messagesNode->appendChild($newMessageElement);
$xmltickets->save('../XML/tickets.xml');

// Redirect user to updated ticket details page
header("Location: ticketdetails.php?ticketId=$ticketId");
exit();

?>