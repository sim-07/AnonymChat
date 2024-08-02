<?php

require_once "config.php";
require_once "keymessage.php";


session_start();

/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code']) && isset($_SESSION['username'])) {

    $code = mysqli_real_escape_string($connessione, $_POST['code']);
    
    $lastUnivocoClient = mysqli_real_escape_string($connessione, $_POST['lastUnivocoClient']);
    
    $loadAllMessages = mysqli_real_escape_string($connessione, $_POST['loadAllMessages']);

    if ($loadAllMessages === 'true') {
    	$query = "SELECT text, univoco, author, identifier, time, file_data FROM chat_message WHERE chatcode = ?";
		$stmt = $connessione->prepare($query);
		$stmt->bind_param("s", $code);
		$stmt->execute();
		$result = $stmt->get_result();
        //$loadAllMessages = false;
    } elseif ($loadAllMessages === 'false'/* || isset($loadAllMessages)*/) {
    	$query = "SELECT text, univoco, author, identifier, time, file_data FROM chat_message WHERE chatcode = ? AND univoco > ?";
		$stmt = $connessione->prepare($query);
		$stmt->bind_param("ss", $code, $lastUnivocoClient);
		$stmt->execute();
		$result = $stmt->get_result();
    }

    if ($result) {
        $messages = array();
        $identifiers = array();
        
        
        while ($row = mysqli_fetch_assoc($result)) {
            
        	$univoco = $row['univoco'];
        	$encryptedText = $row['text'];
            $author = $row['author'];
            $identifier = $row['identifier'];
            $time = $row['time'];
            $file_data = $row['file_data'];
            $identifiers[] = $identifier;
            
            $decryptedText = openssl_decrypt($encryptedText, "aes-256-cbc", $keymessage, 0, $keymessage);
            
            $isFile = false;
            
        	if ($decryptedText !== false) {
            	if ($file_data !== NULL && strlen($file_data) > 0) {
                	$isFile = true;
                }
            	$messages[] = array(
                	'text' => $decryptedText,
                	'author' => $author,
                    'univoco' => $univoco,
                    'identifier' => $identifier,
                    'time' => $time,
                    'isFile' => $isFile
            	);
        	}
        	$univoco = $row['univoco'];
    	}   

        header('Content-Type: application/json');
		echo json_encode(array('messages' => $messages, 'univoco' => $univoco, 'identifier' => $identifiers, 'time' => $time, 'isFile' => $isFile));

        
    } else {
        http_response_code(500);
        echo json_encode(array('error' => 'Errore durante il recupero dei messaggi dal database.'));
    }
} else {
    // Parametro 'code' non presente nella query string
    //http_response_code(400);
    //echo json_encode(array('error' => 'Parametro "code" mancante.'));
    //echo "Error. ";
    header("Location: dashboard.php");
}

$stmt->close();
$connessione->close();
?>
