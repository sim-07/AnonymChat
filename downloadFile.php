<?php

require_once "config.php"; // Assicurarsi che la connessione al database sia definita qui
require_once "keymessage.php";

// Funzione per loggare messaggi per il debugging
// function log_message($message) {
//     error_log($message, 3, 'logfile.log');
// }

if (isset($_POST['file']) && isset($_POST['chatcode'])) {
    // log_message("File identifier ricevuto: " . $_POST['file'] . "\n");
    $identifier_file = $_POST['file'];
    $chatcode = $_POST['chatcode'];
    
    // Verifica la connessione al database
    if ($connessione->connect_error) {
        http_response_code(500);
        echo json_encode(array('error' => 'Errore di connessione al database'));
        exit;
    }
    
    $query = "SELECT chatcode, text, file_data FROM chat_message WHERE identifier = ?";
    $stmt = $connessione->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(array('error' => 'Errore interno del server'));
        exit;
    }

    $stmt->bind_param("s", $identifier_file);
    $stmt->execute();
    $stmt->bind_result($chatCodeDb, $fileNameEncrypted, $fileContent);
    
    if ($stmt->fetch()) {
        $fileName = openssl_decrypt($fileNameEncrypted, "aes-256-cbc", $keymessage, 0, $keymessage);
        
        if ($fileName && $fileContent) {
            if ($chatcode === $chatCodeDb) {
                $response = array(
                    'fileName' => $fileName,
                    'fileContent' => base64_encode($fileContent)
                );

                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                http_response_code(403);
                echo json_encode(array('error' => 'Chatcode non corrispondenti.'));
                exit;
            }
        } else {
            http_response_code(404);
            echo json_encode(array('error' => 'Dati non trovati'));
            exit;
        }
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Dati non trovati'));
        exit;
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array('error' => 'Richiesta non valida'));
    exit;
}

$connessione->close();
?>
