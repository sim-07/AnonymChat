<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['username'])) {
    // Se l'utente non è loggato, reindirizzalo alla pagina di login
    header('Location: index.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

$stmt = $connessione->prepare("SELECT ID FROM webchat_users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Errore nella query: " . mysqli_error($connessione));
}

$row = mysqli_fetch_assoc($result);
$username_id = $row['ID'];

$_SESSION['sender'] = $username;

$_SESSION['csrf_token_delete_php'] = bin2hex(random_bytes(32));
$_SESSION['csrf_token_chat'] = bin2hex(random_bytes(32));

$_SESSION['csrf_token_logout_php'] = bin2hex(random_bytes(32));


//$createdChatQuery = "SELECT ID FROM chat_room WHERE author = '$username'";
//$resultChat = mysqli_query($connessione, $createdChatQuery);

$stmt = $connessione->prepare("SELECT ID FROM chat_room WHERE author = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$resultChat = $stmt->get_result();

$chatId = [];

while ($rowChat = mysqli_fetch_assoc($resultChat)) {
    $chatId[] = $rowChat['ID']; // Aggiungi l'ID alla lista
}

$chatIdJSON = json_encode($chatId);


?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="https://www.anonymchat.altervista.org/logo_chat2.png" />
    <title>AnonymChat | <?php echo $username ?></title>
    <style>
        body {
            min-height: 100vh;
            font-family: Arial;
            /*overflow: hidden;*/
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }
        
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: gray;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgb(67, 67, 67);
        }

        .fullPageMenu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #111;
            transition: 0.5s;
			z-index: 1;
        }

        .fullPageMenu.active {
            top: -100%;
        }

        .fullPageBanner.active {
            display: block;
        }

        .fullPageBanner {
            display: none;
        }

        .fullPageMenu .banner {
            position: relative;
            width: 600px;
            height: 100%;
            margin-top: 320px;
        }

        .fullPageMenu .banner svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fullPageMenu .nav {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .fullPageMenu .nav ul {
            position: relative;
        }

        .fullPageMenu .nav ul li {
            position: relative;
            list-style: none;
            padding: 0 20px;
            margin: 5px 0;
            overflow: hidden;
            display: table;
        }

        .fullPageMenu .nav ul li:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ff0047;
            transition: transform 0.5s ease-in-out;
            transform: scaleY(0);
            transform-origin: bottom;
        }

        .fullPageMenu .nav ul li:hover:before {
            transition: transform 0.5s ease-in-out;
            transform: scaleY(1);
            transform-origin: top;
        }

        .fullPageMenu .nav ul li a {
            position: relative;
            color: #fff;
            text-decoration: none;
            font-size: 4em;
            font-weight: 700;
            line-height: 1em;
            display: inline-block;
            text-transform: uppercase;
            margin-top: 10px;

        }

        .fullPageMenu .nav ul li a::before {
            content: attr(data-text);
            position: absolute;
            bottom: -100%;
            left: 0;
            color: #fff;
        }

        .fullPageMenu .nav ul li:hover a {

            color: fff;
        }

        .menuicon {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: #fff url(https://i.postimg.cc/k4rb3zpp/pngwave.png);
            background-size: 40px;
            cursor: pointer;
            background-repeat: no-repeat;
            background-position: 10px;
            z-index: 1;
        }

        .menuicon.active {
            background: #fff url(https://i.postimg.cc/25t1dMY5/pngegg-1.png);
            background-size: 40px;
            background-repeat: no-repeat;
            background-position: 10px;
        }
        
        
        
        @media(max-width:1040px) {
        	.fullPageMenu .banner {
            	display: none;
        	}
    	}
        
        @media only screen and (max-width: 483px) {
        	#link_code {
            	height: 70px !important;
    			width: 90% !important;
            }
        }


        @media only screen and (max-width: 445px) {
            .fullPageMenu .nav ul {
                font-size: 14px;
            }
        }

        @media only screen and (max-width: 350px) {
            .fullPageMenu .nav ul {
                font-size: 10px;
            }
        }

        .barra {
            width: 100%;
            height: 100px;
            background-color: rgb(0, 0, 0);
            margin-top: 0px;
            position: fixed;
			z-index: 1;
        }
        
        li {
        	margin-top: 30px !important;
        }
        
        #welcome-message {
        	top: 40px;
    		left: 20px;
    		position: fixed;
    		color: white;
    		font-size: 24px;
        }
        
        input {
            border-radius: 5px;
    		border: none;
    		height: 40px;
    		/*width: 100%;*/
    		padding: 6px 11px 6px 68px;
    		box-shadow: 0px 2px 5px -1px gray;
    		font-size: 16px;
        }
        
        button {
        	height: 40px;
    		border-radius: 5px;
            padding: 5px 15px 5px 15px;
    		border: none;
    		background-color: white;
    		box-shadow: 0px 3px 11px -3px gray;
    		letter-spacing: 1px;
    		cursor: pointer;
        }
        
        button:hover {
            background-color: #efefef;
        }

        button:active {
            background-color: #e5e5e5;
        }
        
        .searchResult {
        	margin-top: 20px;
    		height: 30px;
    		padding: 10px 10px 29px 18px;
    		background-color: lightgray;
    		border-radius: 5px;
    		cursor: pointer;
            text-decoration: none;
            color: black;
            display: block;
        }
        
        .link-container {
    		position: absolute;
    		left: 50%;
    		transform: translate(-50%, 140px);
    		width: 96%;
		}
        
        .icon-search {
        	position: absolute;
    		top: 13px;
    		left: 14px;
        }
        
        .ex-chat {
        	margin-top: 274px;
    		margin-left: 13px;
    		margin-right: 10px;
    		position: absolute;
        }
        
        .hide {
        	display: none;
        }
        
        #create_chat {
    		position: absolute;
    		width: 97%;
    		left: 50%;
    		transform: translate(-50%, +140px);
        }
        
        #link_code {
        	border-radius: 5px;
    		border: none;
    		height: 45px;
    		width: 100%;
  			padding: 12px 11px 6px 18px;
    		box-shadow: 0px 2px 5px -1px gray;
    		font-size: 16px;
    		color: black;
    		text-decoration: none;
    		display: block;
    		line-height: 1.5;
    		min-width: 260px;
    		letter-spacing: 0px;
    		cursor: pointer;
        }
        
        
        .formConfirmDelete {
        	margin-top: 130px;
    		position: absolute;
            margin-left: 30px;
            
        }
        
        .chatCodeContainer {
        	top: 480px;
    		max-height: 250px;
    		overflow-y: auto;
    		max-width: 500px;
    		position: absolute;
    		width: 97%;
    		left: 11px;
    		padding: 3px;
        }
        
        .inputExChat {
        	cursor: pointer;
    		margin-bottom: 10px;
    		width: 100%;
    		border-radius: 5px;
    		border: none;
    		height: 40px;
    		padding: 6px 11px 6px 68px;
    		box-shadow: 0px 2px 5px -1px gray;
    		font-size: 16px;
        }
        
    </style>
</head>

<body>
    <div class="barra">
    	<h2 id="welcome-message"></h2>
    </div>

   
    
    <!--
    <div class="search-container hide" id="search_container">
    	<i class="fas fa-search icon-search"></i>
        <input placeholder="Search a user" oninput="search_user()" id="search-user">
        <div id="search-results" style="margin-top: 30px;"></div>
    </div>
    -->
    
    
    
    <div id="container">
    
    	<form class="link-container hide" id="link_container" action="chat-room.php" method="POST">
    		<input name="csrf_token_chat" type="hidden" value="<?php echo $_SESSION['csrf_token_chat']; ?>">
            <input name="username_id" type="hidden" value="<?php echo $username_id; ?>">
            <input name="code" id="randomCodeInput" type="hidden" value="">
        	<button id="link_code" type="submit">
    	</form>
    
    	<button onclick="createChat()" id="create_chat">Create new chat</button>
    	
        <!--ex chat------------------------- -->
    	<form class="ex-chat" action="chat-room.php" method="POST">   
    		<p style="margin-bottom: 17px; color: #6c6c6c;">Join an existing chat:</p>
    		<input style="padding: 6px 11px 6px 12px;" placeholder="Enter code" id="ex-chat" name="code">
            <input name="csrf_token_chat" type="hidden" value="<?php echo $_SESSION['csrf_token_chat']; ?>">
            <input name="username_id" type="hidden" value="<?php echo $username_id; ?>">
        	<button style="margin-top: 10px;">Submit</button>
    	</form>
        
        <h4 style="margin-top: 430px; position: absolute; margin-left: 13px;" id="chatMess">Chats created by you</h4>
        <div class="chatCodeContainer" id="chatCodeContainer">
    	</div>
        
    </div>
    

    <div class="fullPageMenu active" id="nav"><!--Menù-->
        <!--div class="banner fullPageBanner" id="banner">
            <h1 style="color: white; font-size: 90px; margin-top: 150px; margin-left: 40px;">LOGO</h1>
            <img src="logo_chat.png" style="margin-top: 150px; margin-left: 40px;">
        </div-->
        <div class="nav" id="nav">
            <ul>
            	<form id="logoutForm" action="logout.php" method="POST">
                	<input type="hidden" name="csrf_token_logout_js" value="<?php echo $_SESSION['csrf_token_logout_php']; ?>">
                	<li onclick="document.getElementById('logoutForm').submit();" style="cursor: pointer;"><a>Logout</a></li>
                </form>
                
                <li><a id="deleteAccountButton" onclick="confirm_delete()" style="cursor: pointer;">Delete account</a></li>
                <!--li><a href="logout.php">Change account</a></li-->
                <li><a href="contact_us.php">Contact us</a></li>
            </ul>
        </div>
    </div>
    <span class="menuicon active" id="toggle" onclick="menuToggle()"></span>
    
    <form action="delete_account.php" method="POST" class="formConfirmDelete hide" id="formConfirmDelete">
    	<h4 style="margin-bottom: 30px;">Are you sure you want to delete your account? This action is irreversible</h4>
    	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token_delete_php']; ?>">
        <input name="password" type="password" placeholder="Enter password" style="padding: 6px 10px 6px 15px;">
        <button onclick="cancel_delete_account()" type="button">Cancel</button>
        <button type="submit"  style="margin-top: 20px; background-color: #9f0000; color: white;">Delete Account</button>
        
    </form>
</body>

<script>

  var username_id = '<?= $username_id ?>'
  var chatIds = <?php echo $chatIdJSON; ?>;
    
   //console.log(chatIds);

  var chatCodeContainer = document.getElementById("chatCodeContainer");
  var ex_chat = encodeURIComponent(document.getElementById("ex-chat"));
  var dynamicInput = document.querySelectorAll(".inputExChat");
    
   
  if(chatIds.length != 0) {
  	for (let i = 0; i < chatIds.length; i++) {
        var chatId = chatIds[i];
        
        // Crea l'elemento input
        var input = document.createElement("p");
        input.type = "text";
        input.name = "chatId";
        input.textContent = chatId;
        input.disabled = true;
        input.classList.add("inputExChat");
        
        input.setAttribute("onclick", "copyInputText('" + chatId + "')");
        
        // Aggiungi l'input al div chatCodeContainer
        chatCodeContainer.appendChild(input);
        
    
    
    //---------------------------------------------------------
  
  			//var dynamicInput = document.querySelectorAll(".inputExChat");

  			/*dynamicInput.forEach(function(input) {
            
    			input.addEventListener("click", function() {
        			var textToCopy = input.value;
        			var exChatInput = document.getElementById("ex-chat");
        			exChatInput.value = textToCopy;
        			console.log("Text copied to ex-chat: " + textToCopy);
    			});
			});*/
            
            //---------------------------------------------------------
            
	}
			
    
  } else {
  	document.getElementById("chatMess").classList.add("hide");
  }
  
  
  
  
  function copyInputText(chatId) {
        var exChatInput = document.getElementById("ex-chat");
    	exChatInput.value = chatId;
    	//console.log("Text copied to ex-chat: " + chatId);

  }

  function menuToggle() {
        var nav = document.getElementById("nav")//tutto il menù
        var toggle = document.getElementById("toggle")//icona del menù
        var banner = document.getElementById("banner")//logo
        //qui si aggiunge la classe active
        nav.classList.toggle("active")
        toggle.classList.toggle("active")
        //banner.classList.toggle("active")
    }


	var randomCode = null;
    
    /*function showRandomCode() {
    if (randomCode) {
        console.log("Codice casuale: " + randomCode);
    } else {
        console.error("Codice casuale non ancora generato o già utilizzato.");
    }
    
}*/


	function createChat() {
    var link_container = document.getElementById("link_container");
    var search_button = document.getElementById("create_chat");
    link_container.classList.remove("hide");
    search_button.classList.add("hide");
    
    var link_code = document.getElementById("link_code");
    
    

    if (!randomCode) {
        // Genera il codice casuale solo se non è già stato generato
        randomCode = generateRandomCode(32);

        // Mostra il codice casuale nel frontend
        //showRandomCode();

        // Esegui la richiesta AJAX per memorizzare il codice nel database
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Codice memorizzato con successo nel database
                    //console.log("Codice casuale memorizzato nel database: " + randomCode);
                } else {
                    // Si è verificato un errore durante la richiesta al server
                    //console.error("Errore durante il salvataggio del codice casuale nel database.");
                    console.error(xhr.responseText); // Visualizza il messaggio di errore del server nella console
                }
            }
        };
        xhr.open('POST', 'store_random_code.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('code=' + encodeURIComponent(randomCode));
    } else {
        // Il codice casuale è già stato generato, mostra il codice esistente nel frontend
        //showRandomCode();
        
    }
    
    link_code.innerHTML = "Chat code: " + randomCode + " | Click here to go to the chat. ";
    //link_code.setAttribute("href", "chat-room.php?id=" + encodeURIComponent(username_id) + "&code=" + encodeURIComponent(randomCode));
    
}



function generateRandomCode(length) {
        var charset = "abcdefghijklmnopqrstuvwxyz0123456789";
        var code = "";
        for (var i = 0; i < length; i++) {
            var randomIndex = Math.floor(Math.random() * charset.length);
            code += charset.charAt(randomIndex);
        }
        document.getElementById("randomCodeInput").value = code;
        return code;
    }

    
    
    function confirm_delete() {
    	let form = document.getElementById("formConfirmDelete");
        let container = document.getElementById("container");
        
        
        menuToggle();
        
        setTimeout ( function() {
        	form.classList.remove("hide");
        	container.classList.add("hide");
        }, 200);
    }
    
    function cancel_delete_account() {
    	let form = document.getElementById("formConfirmDelete");
        let container = document.getElementById("container");
        
        form.classList.add("hide");
        container.classList.remove("hide");
    }


    var username = '<?= $username ?>'
    var message = document.getElementById("welcome-message");
    message.innerHTML = "Welcome " + username + "!";
    
    
    
    
    
    
</script>



</html>
