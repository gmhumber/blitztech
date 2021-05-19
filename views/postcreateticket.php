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

// Only allow regular users to submit new tickets
if ($_SESSION["validatedUserPrivilages"] !== "user") {

    header("Location: error.php");
    exit();
}

// Redirect user to error page if submitted form is incomplete
if (!isset($_POST["category"]) || !isset($_POST["description"])) {
    header("Location: error.php");
    exit();
}

// Verify that the inputted ticket category is one of the permitted categories
$permittedCategories = ["troubleshoot", "repairs", "returns", "inquiry"];
if (!in_array($_POST["category"], $permittedCategories)) {
    header("Location: error.php");
    exit();
}

// Load tickets XML file
$xmltickets = new DOMDocument('1.0');
$xmltickets->preserveWhiteSpace = false;
$xmltickets->formatOutput = true;
$xmltickets->load('../XML/tickets.xml');
$xpathtickets = new DOMXPath($xmltickets);

// Get next ticket ID number by adding 1 to the largest ticketId on record 
$ticketIdsNodeList = $xpathtickets->evaluate("//ticketId");
$ticketIdsArray = [];

for ($i=0; $i < $ticketIdsNodeList->length; $i++) {
    array_push($ticketIdsArray, $ticketIdsNodeList->item($i)->nodeValue);
}

$newTicketId = strval(intval(max($ticketIdsArray)) + 1);

$newTicketCategory = $_POST['category'];
$newTicketDescription = $_POST['description'];
$newTicketDate = date("Y-m-d\TH:i:s");
$newTicketSubmitterId = $_SESSION["validatedUserId"];
$newTicketHandlerId = "1";

// Build the new ticket element and save it to the XML file
$ticketsNode = $xmltickets->getElementsByTagName("tickets")->item(0);

// Create the new nodes
$newTicketElement = $xmltickets->createElement("ticket");
$newTicketIdElement = $xmltickets->createElement("ticketId", $newTicketId);
$newSubmitterIdElement = $xmltickets->createElement("submitterId", $newTicketSubmitterId);
$newHandlerIdElement = $xmltickets->createElement("handlerId", $newTicketHandlerId);
$newCategoryElement = $xmltickets->createElement("category", $newTicketCategory);
$newStatusElement = $xmltickets->createElement("status", "open");
$newTicketDateTimesElement = $xmltickets->createElement("ticketDateTimes");
$newOpenedDateElement = $xmltickets->createElement("openedDate", $newTicketDate);
$newMemosElement = $xmltickets->createElement("memos");
$newMessagesElement = $xmltickets->createElement("messages");
$newMessageElement = $xmltickets->createElement("message");
$newMessageDateTimeElement = $xmltickets->createElement("messageDateTime", $newTicketDate);
$newSenderId = $xmltickets->createElement("senderId", $newTicketSubmitterId);
$newMessageBody = $xmltickets->createElement("body", $newTicketDescription);

// Append nodes to each other to form the new node element's structure
$newTicketElement->appendChild($newTicketIdElement);
$newTicketElement->appendChild($newSubmitterIdElement);
$newTicketElement->appendChild($newHandlerIdElement);
$newTicketElement->appendChild($newCategoryElement);
$newTicketElement->appendChild($newStatusElement);

$newTicketDateTimesElement->appendChild($newOpenedDateElement);

$newTicketElement->appendChild($newTicketDateTimesElement);
$newTicketElement->appendChild($newMemosElement);

$newMessageElement->appendChild($newMessageDateTimeElement);
$newMessageElement->appendChild($newSenderId);
$newMessageElement->appendChild($newMessageBody);

$newMessagesElement->appendChild($newMessageElement);

$newTicketElement->appendChild($newMessagesElement);

$ticketsNode->appendChild($newTicketElement);

$xmltickets->save('../XML/tickets.xml');

// Redirect user to tickets list after the new ticket is created
header("Location: ticketlist.php");
exit();



?>