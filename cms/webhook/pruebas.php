<?php

/*=============================================
Depurar Errores
=============================================*/

define('DIR',__DIR__);

ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", DIR."/php_error_log");

date_default_timezone_set("America/Lima");


/* =============================================
Controladores
=============================================*/

require_once "../extensions/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

require_once "../controllers/curl.controller.php";
require_once "../controllers/clients.controller.php";
require_once "../controllers/bots.controller.php";

/*=============================================
Simulación del contenido JSON
=============================================*/

$input = '{"object":"whatsapp_business_account","entry":[{"id":"952399544271620","changes":[{"value":{"messaging_product":"whatsapp","metadata":{"display_phone_number":"15556393799","phone_number_id":"1025723653965602"},"contacts":[{"profile":{"name":"Carlos Polanco"},"wa_id":"51920155032","user_id":"PE.967744529547245"}],"messages":[{"context":{"from":"15556393799","id":"wamid.HBgLNTE5MjAxNTUwMzIVAgARGBI0QjVDMTkxREUyRkMwNTY4ODAA"},"from":"51920155032","from_user_id":"PE.967744529547245","id":"wamid.HBgLNTE5MjAxNTUwMzIVAgASGCBBQ0NBRkU5M0ZGNEVEQTc3MEU4MUNFNEE2NkNBMDQ0QQA=","timestamp":"1779667525","type":"interactive","interactive":{"type":"button_reply","button_reply":{"id":"1","title":"Contactar con un ase"}}}]},"field":"messages"}]}]}
';
$data = json_decode($input);
echo '<pre>$data'; print_r($data); echo '</pre>';
/*=============================================
Convierte el contenido JSON a un array asociativo
=============================================*/


$data = json_decode($input);
// echo '<pre>'; print_r($data); echo '</pre>';

// return

/*=============================================
Variables
=============================================*/

$type_message = null;
$status_message = null;
$id_whatsapp_message = null;
$client_message = null;
$phone_message = null;
$order_message = 0;
$type_conversation = null;
$template_message = null;


/*=============================================
Tipo de mensajes
=============================================*/

if(isset($data->entry[0]->changes[0]->value->messages)){
    $type_message = "client";
}

if(isset($data->entry[0]->changes[0]->value->statuses)){
    $type_message = "business";
    $status_message = $data->entry[0]->changes[0]->value->statuses[0]->status;
}

// echo '<pre>$type_message '; print_r($type_message); echo '</pre>';
// echo '<pre>$status_message '; print_r($status_message); echo '</pre>';


/*=============================================
Capturar la API Clound
=============================================*/

$url = "whatsapps?linkTo=id_number_whatsapp&equalTo=".$data->entry[0]->changes[0]->value->metadata->phone_number_id;
$method = "GET";
$fields = array();

$getApiWS = CurlController::request($url, $method, $fields);

if($getApiWS->status == 200){

    $getApiWS = $getApiWS->results[0];
    $id_whatsapp_message = $getApiWS->id_whatsapp;

} else {

    // Fallback para pruebas cuando el phone_number_id no está en la BD
    $getApiWS = (object)[
        'id_whatsapp'              => 1,
        'ai_whatsapp'              => 0,
        'token_whatsapp'           => 'TEST_TOKEN',
        'phone_number_id_whatsapp' => $data->entry[0]->changes[0]->value->metadata->phone_number_id,
    ];
    $id_whatsapp_message = $getApiWS->id_whatsapp;
}

echo '<pre>$id_whatsapp_message '; print_r($id_whatsapp_message); echo '</pre>';

/*=============================================
Capturar mensaje del cliente
=============================================*/
if($type_message == "client"){

    $phone_message = $data->entry[0]->changes[0]->value->messages[0]->from;

    /*=============================================
    Capturando texto
    =============================================*/
    if(isset($data->entry[0]->changes[0]->value->messages[0]->text)){
    $client_message = $data->entry[0]->changes[0]->value->messages[0]->text->body;
    $type_conversation = "text";
    }


    /*=============================================
    Capturando un imagen
    =============================================*/
    if(isset($data->entry[0]->changes[0]->value->messages[0]->image)){
            if(isset($data->entry[0]->changes[0]->value->messages[0]->image->caption)){
                $caption = $data->entry[0]->changes[0]->value->messages[0]->image->caption;
            }else{
                $caption = "";
            }
    $client_message = '{"type":"image","mime":"'. $data->entry[0]->changes[0]->value->messages[0]->image->mime_type .'", "id":"'. $data->entry[0]->changes[0]->value->messages[0]->image->id .'", "caption":"'. $caption .'"}';
    $type_conversation = "image";
    }

    /*=============================================
    Capturando un video
    =============================================*/

    if(isset($data->entry[0]->changes[0]->value->messages[0]->video)){
            if(isset($data->entry[0]->changes[0]->value->messages[0]->video->caption)){
                $caption = $data->entry[0]->changes[0]->value->messages[0]->video->caption;
            }else{
                $caption = "";
            }
    $client_message = '{"type":"video","mime":"'. $data->entry[0]->changes[0]->value->messages[0]->video->mime_type .'", "id":"'. $data->entry[0]->changes[0]->value->messages[0]->video->id .'", "caption":"'. $caption .'"}';
    $type_conversation = "video";
    }

    /*=============================================
    Capturando un audio
    =============================================*/

    if(isset($data->entry[0]->changes[0]->value->messages[0]->audio)){
            
    $client_message = '{"type":"audio","mime":"'. $data->entry[0]->changes[0]->value->messages[0]->audio->mime_type .'", "id":"'. $data->entry[0]->changes[0]->value->messages[0]->audio->id .'"}';
    $type_conversation = "audio";
    }

    /*=============================================
    Capturando un documento
    =============================================*/

    if(isset($data->entry[0]->changes[0]->value->messages[0]->document)){
            if(isset($data->entry[0]->changes[0]->value->messages[0]->document->caption)){
                $caption = $data->entry[0]->changes[0]->value->messages[0]->document->caption;
            }else{
                $caption = "";
            }
    $client_message = '{"type":"document","mime":"'. $data->entry[0]->changes[0]->value->messages[0]->document->mime_type .'", "id":"'. $data->entry[0]->changes[0]->value->messages[0]->document->id .'", "caption":"'. $caption .'"}';
    $type_conversation = "document";
    }

 
     /*=============================================
    Capturar respuesta interactiva
    =============================================*/

    if(isset($data->entry[0]->changes[0]->value->messages[0]->interactive)){
            $type_conversation = "interactive";

            if(isset($data->entry[0]->changes[0]->value->messages[0]->interactive->button_reply)){
            $client_message = '{"id": "'.$data->entry[0]->changes[0]->value->messages[0]->interactive->button_reply->id.'", "text": "'.$data->entry[0]->changes[0]->value->messages[0]->interactive->button_reply->title.'"}';
    }}






   echo '<pre>$client_message '; print_r($client_message); echo '</pre>';
    echo '<pre>$phone_message '; print_r($phone_message); echo '</pre>';
    /*=============================================
    Capturar el orden del mensaje
    =============================================*/

    $url = "messages?linkTo=phone_message&equalTo=".$phone_message."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";

    $getMessages = CurlController::request($url, $method, $fields);

    if($getMessages->status == 200){

        $order_message = $getMessages->results[0]->order_message + 1;
        $template_message = $getMessages->results[0]->template_message;
        
        }


    /* =============================================
    Guardar mensaje del negocio
    =============================================*/
    $url = "messages?token=no&except=id_message";
    $method = "POST";
    $fields = array(
        "type_message" => $type_message,
        "id_whatsapp_message" => $id_whatsapp_message,
        "client_message" => $client_message,
        "phone_message" => $phone_message,
        "template_message" => $template_message,
        "order_message" => $order_message,
        "date_created_message" => date("Y-m-d"),
    );

    $saveMessage = CurlController::request($url, $method, $fields);


    if($saveMessage->status == 200){

        /*=============================================
        Respuesta al cliente
        =============================================*/

        $responseClients = ClientsController::responseClients($getApiWS, $phone_message, $order_message, $type_conversation);

        echo '<pre>$responseClients '; print_r($responseClients); echo '</pre>';

    }
}

/*=============================================
Capturar mensaje del negocio
=============================================*/
if ($type_message == "business" && $status_message == "sent") {
    /*=============================================
    Capturar el número de teléfono
    =============================================*/

    $phone_message = $data->entry[0]->changes[0]->value->statuses[0]->recipient_id;
    echo '<pre>$phone_message '; print_r($phone_message); echo '</pre>';

    /*=============================================
    Capturando el ID de la conversación
    =============================================*/
    
    $idConversation = isset($data->entry[0]->changes[0]->value->statuses[0]->conversation->id)
        ? $data->entry[0]->changes[0]->value->statuses[0]->conversation->id
        : null;
    echo '<pre>$idConversation '; print_r($idConversation); echo '</pre>';

    /*=============================================
    Capturar fecha de vencimiento del mensaje
    =============================================*/

    $expireConversation = isset($data->entry[0]->changes[0]->value->statuses[0]->conversation->expiration_timestamp)
        ? $data->entry[0]->changes[0]->value->statuses[0]->conversation->expiration_timestamp
        : null;
    $expireConversation = $expireConversation ? (new DateTime("@$expireConversation"))->format('Y-m-d H:i:s') : null;
    echo '<pre>$expireConversation '; print_r($expireConversation); echo '</pre>';

    /*=============================================
    Traer la últma respuesta del negocio
    =============================================*/

    $url = "messages?linkTo=phone_message&equalTo=".$phone_message."&orderBy=id_message&orderMode=DESC&startAt=0&endAt=10";

    $method = "GET";
    $fields = array();

    $getMessage = CurlController::request($url, $method, $fields);

    if ($getMessage && $getMessage->status == 200) {
        $businessMsg = null;
        foreach ($getMessage->results as $msg) {
            if ($msg->type_message == "business") { $businessMsg = $msg; break; }
        }
        $getMessage = $businessMsg;
        /*=============================================
        Actualizar última respuesta del negocio
        =============================================*/

        if (!$getMessage) { echo "No hay mensaje business para este teléfono"; return; }
        $url = "messages?id=".$getMessage->id_message."&nameId=id_message&token=no&except=id_message";
        $method = "PUT";
        $fields = array(
            "id_conversation_message" => $idConversation,
            "expiration_message"      => $expireConversation);

        $fields= http_build_query($fields);
        $updateMessage = CurlController::request($url, $method, $fields);
        if ($updateMessage && $updateMessage->status == 200) {
            echo "todo Ok";


    } }}