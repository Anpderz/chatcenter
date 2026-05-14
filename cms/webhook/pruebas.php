<?php

/*=============================================
Depurar Errores
=============================================*/

define('DIR',__DIR__);

ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", DIR."/php_error_log");

/* =============================================
Controladores
=============================================*/

require_once "../extensions/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

require_once "../controllers/curl.controller.php";

/*=============================================
Simulación del contenido JSON
=============================================*/

$input = '{"object":"whatsapp_business_account","entry":[{"id":"952399544271620","changes":[{"value":{"messaging_product":"whatsapp","metadata":{"display_phone_number":"15556393799","phone_number_id":"1025723653965602"},"contacts":[{"profile":{"name":"Carlos Polanco"},"wa_id":"51920155032","user_id":"PE.967744529547245"}],"messages":[{"from":"51920155032","from_user_id":"PE.967744529547245","id":"wamid.HBgLNTE5MjAxNTUwMzIVAgASGCBBQ0Y5NUJBQkZDRkJDM0NCMjdCNDk4MDQwRjhFRkJGNgA=","timestamp":"1778766865","type":"image","image":{"caption":"Hola quiero un caf\u00e9","mime_type":"image\/jpeg","sha256":"HCaHabZ6gVq4V\/SPmMzBTWkoEUI8NgjfVshSveeWM3c=","id":"27251809591111687","url":"https:\/\/lookaside.fbsbx.com\/whatsapp_business\/attachments\/?mid=27251809591111687&source=webhook&ext=1778767167&hash=ARkmzY3OC-p0F9jJeqlf0KxeMiqXaSp1p4Om4x3WbooRFw"}}]},"field":"messages"}]}]}';

/*=============================================
Convierte el contenido JSON a un array asociativo
=============================================*/


$data = json_decode($input);
echo '<pre>'; print_r($data); echo '</pre>';

/*=============================================
Variables
=============================================*/

$type_message = null;
$id_whatsapp_message = null;
$client_message = null;
$phone_message = null;
$order_message = 0;

/*=============================================
Tipo de mensajes
=============================================*/

if(isset($data->entry[0]->changes[0]->value->messages)){
    $type_message = "client";
}

if(isset($data->entry[0]->changes[0]->value->statuses)){
    $type_message = "business";
}

echo '<pre>'; print_r($type_message); echo '</pre>';

/*=============================================
Capturar la API Clound
=============================================*/

$url = "whatsapps?linkTo=id_number_whatsapp&equalTo=".$data->entry[0]->changes[0]->value->metadata->phone_number_id;
$method = "GET";
$fields = array();

$getApiWS = CurlController::request($url, $method, $fields);

if($getApiWS->status == 200){

    $getApiWS = $getApiWS->results[0];
    $id_whatsapp = $getApiWS->id_whatsapp;
    $id_whatsapp_message = $getApiWS->id_whatsapp;

}

echo '<pre>$getApiWS '; print_r($getApiWS); echo '</pre>';

/*=============================================
Capturar mensaje del cliente
=============================================*/
if($type_message == "client"){

    $phone_message = $data->entry[0]->changes[0]->value->messages[0]->from;

    if(isset($data->entry[0]->changes[0]->value->messages[0]->text)){
    $client_message = $data->entry[0]->changes[0]->value->messages[0]->text->body;
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
    }

    /*=============================================
    Capturando un audio
    =============================================*/

    if(isset($data->entry[0]->changes[0]->value->messages[0]->audio)){
            
    $client_message = '{"type":"audio","mime":"'. $data->entry[0]->changes[0]->value->messages[0]->audio->mime_type .'", "id":"'. $data->entry[0]->changes[0]->value->messages[0]->audio->id .'"}';
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
    }

    echo '<pre>$client_message '; print_r($client_message); echo '</pre>';
    echo '<pre>$phone_message '; print_r($phone_message); echo '</pre>';


    $url = "messages?linkTo=phone_message&equalTo=".$phone_message."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";

    $getMessages = CurlController::request($url, $method, $fields);

    if($getMessages->status == 200){

        $order_message = $getMessages->results[0]->order_message + 1;
    }

    $url = "messages?token=no&except=id_message";
    $method = "POST";
    $fields = array(
        "type_message" => $type_message,
        "id_whatsapp_message" => $id_whatsapp_message,
        "client_message" => $client_message,
        "phone_message" => $phone_message,
        "order_message" => $order_message,
        "date_created_message" => date("Y-m-d"),
    );

    $createMessage = CurlController::request($url, $method, $fields);
    echo '<pre>$createMessage '; print_r($createMessage); echo '</pre>';

}
