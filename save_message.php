<?php

require_once("config.php");
require_once("keymessage.php");

session_start();

if (!isset($_SESSION['username'])) {
    echo "Error. ";
    return;
}

$code = mysqli_real_escape_string($connessione, $_POST["code"]);
$message = $_POST["message"];
$sender = mysqli_real_escape_string($connessione, $_POST["sender"]);
$identifier = mysqli_real_escape_string($connessione, $_POST["identifier"]);
$csrf_token_send_message_js = mysqli_real_escape_string($connessione, $_POST["csrf_token_send_message_js"]);
$csrf_token_send_message_php = $_SESSION['csrf_token_send_message'];

if (empty($message)) {
    echo "Error. ";
    return;
}

if (strlen($message) > 250) {
	return;
}

if (empty($csrf_token_send_message_js) || empty($csrf_token_send_message_php)) {
    echo "Restricted access. ";
    return;
}

if ($csrf_token_send_message_js !== $csrf_token_send_message_php) {
    echo "Restricted access. ";
    return;
}

$currentDate = date('Y-m-d H:i:s'); // Formato "YYYY-MM-DD"
$futureDate = date('Y-m-d H:i:s', strtotime('+3 months', strtotime($currentDate)));

$encryptedMessage = openssl_encrypt($message, "aes-256-cbc", $keymessage, 0, $keymessage);


$fileName = isset($_FILES["file"]["name"]) ? $_FILES["file"]["name"] : null;
$fileData = isset($_FILES["file"]["tmp_name"]) ? file_get_contents($_FILES["file"]["tmp_name"]) : null;

$query = "INSERT INTO chat_message (chatcode, author, text, deadline, time, identifier, file_data) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
$stmt = $connessione->prepare($query);

if(empty($fileData) && $fileData !== NULL) {
    echo "Errore: il file non è stato caricato correttamente.";
}

$stmt->bind_param("sssssb", $code, $sender, $encryptedMessage, $futureDate, $identifier, $fileData);
if($fileData !== NULL) {
    $stmt->send_long_data(5, $fileData);
}
$stmt->execute();
$result = $stmt->get_result();


$stmt->close();
$connessione->close();

?>
