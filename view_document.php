<?php
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

Confirm_Login();

if (isset($_GET['type']) && isset($_GET['SouthAfricanID'])) {
    $documentType = $_GET['type'];
    $SouthAfricanID = $_GET['SouthAfricanID'];

    // Fetch the document from the database
    $query = "SELECT $documentType FROM documents WHERE SouthAfricanID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $SouthAfricanID);
    $stmt->execute();
    $stmt->bind_result($document);
    $stmt->fetch();
    
    if ($document) {
        // Output headers to download the file
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$documentType.pdf\"");
        echo $document;
    } else {
        echo "No document found.";
    }
    $stmt->close();
} else {
    echo "Invalid parameters.";
}
?>
