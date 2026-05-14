<?php

Class ClientsController {
    static public function responseClients($getApiWS, $phone_message, $order_message, $type_conversation) {

/*=============================================
Orden de la conversación
=============================================*/

        if($order_message == 0){

            /*=============================================
            Buscamos el contacto
            =============================================*/

            $url = "contacts?linkTo=phone_contact&equalTo=".$phone_message;
            $method = "GET";
            $fields = array();

            $getContacts = CurlController::request($url, $method, $fields);

            if($getContacts->status != 200){

                /*=============================================
                Creamos el contacto
                =============================================*/

                $url = "contacts?token=no&except=id_contact";
                $method = "POST";
                $fields = array(
                    "phone_contact" => $phone_message,
                    "ai_contact" => $getApiWS->ai_whatsapp,
                    "date_created_contact" => date("Y-m-d"),
                );

                $createContact = CurlController::request($url, $method, $fields);

            }else{

                /*=============================================
                Actualizamos la fecha de la última conversación
                =============================================*/

                $url = "contacts?id=".$getContacts->results[0]->id_contact."&nameId=id_contact&token=no&id_contact";
                $method = "PUT";
                $fields = array(
                    "date_updated_contact" => date("Y-m-d H:i:s"),
                );

                $fields = http_build_query($fields);
                $updateContact = CurlController::request($url, $method, $fields);

            }

            /*=============================================
            Repuesta del bot
            =============================================*/

            if($getApiWS->ai_whatsapp == 0){

                /*=============================================
                Respuesta plantilla bot
                =============================================*/

                $url = "conversations?linkTo=phone_conversation&equalTo=".$phone_message;
                $method = "GET";
                $fields = array();

                $getConversation = CurlController::request($url, $method, $fields);
                $conversation = $getConversation->status == 200 ? $getConversation->results[0]->bot_conversation : "";

                if(empty($conversation)){
                    $getFirstBot = CurlController::request("bots?startAt=0&endAt=1&orderBy=id_bot&orderMode=ASC", "GET", array());
                    $conversation = $getFirstBot->status == 200 ? $getFirstBot->results[0]->title_bot : "";
                }

                $reponseBots = BotsController::responseBots($conversation, $getApiWS, $phone_message, $order_message);

                echo '<pre>$reponseBots '; print_r($reponseBots); echo '</pre>';

            }

        }

    }

}

?>