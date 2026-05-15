<?php

define('DIR', __DIR__);

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', DIR . '/error.log');

date_default_timezone_set("America/Lima");

require_once "../extensions/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once "../controllers/template.controller.php";
require_once "../controllers/curl.controller.php";

class ChatAjax {

    public $phoneMessage;
    public $orderMessage;
    public $conversation;
    public $phone;
    public $token;

    public function ajaxLastMessage() {

        /*=============================================
        Revisar si hay mensajes nuevos en el chat
        =============================================*/

        $url = "messages?linkTo=phone_message&equalTo=" . $this->phoneMessage . "&startAt=" . $this->orderMessage . "&endAt=2";
        $method = "GET";
        $fields = array();

        $getMessages = CurlController::request($url, $method, $fields);

        if ($getMessages->status == 200) {

            if ($getMessages->total > 1) {

                if ($getMessages->results[1]->type_message == "client") {
                    $message = '<div class="msg user"> ' . $getMessages->results[1]->client_message . ' <br><span class="small text-muted float-end mt-2">' . TemplateController::formatDate(6, $getMessages->results[1]->date_updated_message) . '</span></div>';
                    $response = array(
                        "type"      => "client",
                        "message"   => base64_encode($message),
                        "lastOrder" => $getMessages->results[1]->order_message
                    );
                    echo json_encode($response);
                }

                if ($getMessages->results[1]->type_message == "business") {
                    $message = '<div class="msg bot"> ' . $getMessages->results[1]->business_message . ' <br><span class="small text-muted float-end mt-2">' . TemplateController::formatDate(6, $getMessages->results[1]->date_updated_message) . '</span></div>';
                    $response = array(
                        "type"      => "business",
                        "message"   => base64_encode($message),
                        "lastOrder" => $getMessages->results[1]->order_message
                    );
                    echo json_encode($response);
                }

            }
        }

    }

    /*=============================================
    Conversaciones manuales por parte del negocio
    =============================================*/

    public function ajaxSendMessage() {

        /*=============================================
        JSON para enviar por WPP
        =============================================*/

        $json = '{
            "messaging_product": "whatsapp",
            "recipient_type": "individual",
            "to": "' . $this->phone . '",
            "type": "text",
            "text": {
                "preview_url": true,
                "body": "' . $this->conversation . '"
            }
        }';

        $business_message = $this->conversation;
        $template_message = '{"type": "manual", "title": ""}';

        /*=============================================
        Llevar el orden de los mensajes
        =============================================*/

        $url = "messages?linkTo=phone_message&equalTo=" . $this->phone . "&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";
        $method = "GET";
        $fields = array();
        $order_message = 0;

        $getMessages = CurlController::request($url, $method, $fields);

        if ($getMessages->status == 200) {
            $order_message = $getMessages->results[0]->order_message + 1;
        }

        /*=============================================
        Capturar datos de la API de WhatsApp
        =============================================*/

        $url = "whatsapps?linkTo=status_whatsapp&equalTo=1&orderBy=id_whatsapp&orderMode=DESC&startAt=0&endAt=1";

        $getApiWS = CurlController::request($url, $method, $fields);

        if ($getApiWS->status == 200) {

            $getApiWS = $getApiWS->results[0];

            /*=============================================
            Guardar mensaje del negocio
            =============================================*/

            $url = "messages?token=" . $this->token . "&table=admins&suffix=admin";
            $method = "POST";
            $fields = array(
                "type_message"        => "business",
                "id_whatsapp_message" => $getApiWS->id_whatsapp,
                "business_message"    => $business_message,
                "phone_message"       => $this->phone,
                "order_message"       => $order_message,
                "template_message"    => $template_message,
                "date_created_message" => date("Y-m-d"),
            );

            $saveMessage = CurlController::request($url, $method, $fields);

            if ($saveMessage->status == 200) {

                /*=============================================
                Enviamos datos JSON a la API de WhatsApp
                =============================================*/

                $sendMessage = CurlController::apiWS($json, $getApiWS);

                echo json_encode(array("status" => "ok"));
            }
        }
    }

}

/*=============================================
Revisar si hay mensajes nuevos en el chat
=============================================*/

if (isset($_POST["phone_message"])) {

    $ajax = new ChatAjax();
    $ajax->phoneMessage = $_POST["phone_message"];
    $ajax->orderMessage = $_POST["order_message"];
    $ajax->ajaxLastMessage();

}

/*=============================================
Conversaciones manuales por parte del negocio
=============================================*/

if (isset($_POST["conversation"])) {

    $ajax = new ChatAjax();
    $ajax->conversation = $_POST["conversation"];
    $ajax->phone = $_POST["phone"];
    $ajax->token = $_POST["token"];
    $ajax->ajaxSendMessage();

}
