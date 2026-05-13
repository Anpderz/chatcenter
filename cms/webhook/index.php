<?php 

/*=============================================
TOKEN que configuras en la plataforma de Meta
=============================================*/

$token = "1234abcd";

if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["hub_verify_token"])){

	if($_GET["hub_verify_token"] == $token){

		echo $_GET["hub_challenge"];

		exit;
	
	}else{

		echo "Token inválido";
        exit;
	}

}

/*=============================================
Recibir respuesta de la API de WS
=============================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	/*=============================================
    // Obtén el contenido JSON
    =============================================*/
   
    $input = file_get_contents('php://input');

    file_put_contents("webhook_log.txt", $input."\n\n", FILE_APPEND); 

}

 ?>