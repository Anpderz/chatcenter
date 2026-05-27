<?php

Class BotsController {
    static public function responseBots($bot, $getApiWS, $phone_message, $order_message, $idListMenu = null) {

    /*=============================================
    Traemos la plantilla del bot
    =============================================*/

    $url = "bots?linkTo=title_bot&equalTo=".$bot;
    $method = "GET";
    $fields = array();

    $getBot = CurlController::request($url, $method, $fields);

    $json             = null;
    $business_message = null;
    $template_message = null;

    if($getBot->status == 200){

        $getBot = $getBot->results[0];

        if($getBot->type_bot == "text"){

            $json = '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "'.$phone_message.'",
    "type": "text",
    "text": {
      "preview_url": true,
      "body": '.json_encode(urldecode($getBot->body_text_bot)).'
    }
  }';
  $business_message = urldecode($getBot->body_text_bot);
  $template_message = '{"type":"bot","title":"'.$bot.'"}';
        }

        if($getBot->type_bot == "interactive") {
            $business_message = urldecode($getBot->body_text_bot);
            $template_message = '{"type":"bot","title":"'.$bot.'"}';
            $json = '{
                "messaging_product": "whatsapp",
                "recipient_type": "individual",
                "to": "'.$phone_message.'",
                "type": "interactive",
                "interactive": {
                    "type": "'.$getBot->interactive_bot.'",';
        $header = null;
        if(!empty($getBot->header_text_bot)){
            $header = '{"type":"text","text":"'.mb_substr(trim(urldecode($getBot->header_text_bot)),0, 60).'"}';
        }
        if(!empty($getBot->header_image_bot)){
            $header = '{"type":"image","image":{"link":"'.urldecode($getBot->header_image_bot).'"}}';
        }
        if(!empty($getBot->header_video_bot)){
            $header = '{"type":"video","video":{"link":"'.urldecode($getBot->header_video_bot).'"}}';
        }

        $json .= ($header ? '"header":'.$header.',' : '');
        $json .= '"body":{"text":'.json_encode(urldecode($getBot->body_text_bot)).'},';
        $json .= '"footer":{"text":'.json_encode(mb_substr(trim(urldecode($getBot->footer_text_bot)),0,60)).'},';

        if($getBot->interactive_bot == "button"){

            $json .= '"action":{"buttons":[';

            if(!empty($getBot->buttons_bot)){

                $buttonsArr = [];
                foreach (json_decode(urldecode($getBot->buttons_bot)) as $key => $value) {
                    $buttonsArr[] = '{"type":"reply","reply":{"id":'.json_encode((string)$key).',"title":'.json_encode(mb_substr($value,0,20)).'}}';
                }
                $json .= implode(',', $buttonsArr);

            }
            $json .= ']}}}';

        }
         if($getBot->type_bot == "list") {
       $json.= ' "action": {
        "button": "Ver opciones",
        "sections": [
          {
            "title": "'.$getBot->title_list_bot.'",
            "rows": [';
            if(!empty($getBot->list_bot)){

                $buttonsArr = [];
                foreach (json_decode(urldecode($getBot->list_bot)) as $key => $value) {
                
                $json .= '{
                "id": "'.$value->id.'"
                
                },';
}
            }
            $json .= ']}}}';

        }'
            ]
          },
        ]
      }';
          $business_message = urldecode($getBot->body_text_bot);
  $template_message = '{"type":"bot","title":"'.$bot.'"}';
        }

        } // close if(interactive)

        /*=============================================
        Llevar el orden del mensaje
        =============================================*/

        $url = "messages?linkTo=phone_message&equalTo=".$phone_message."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";

    $getMessages = CurlController::request($url, $method, $fields);

    if($getMessages->status == 200){

        $order_message = $getMessages->results[0]->order_message + 1;
    }

    /* =============================================
    Guardar mensaje del negocio
    =============================================*/
    $url = "messages?token=no&except=id_message";
    $method = "POST";
    $fields = array(
        "type_message" => "business",
        "id_whatsapp_message" => $getApiWS->id_whatsapp,
        "business_message" => $business_message,
        "phone_message" => $phone_message,
        "order_message" => $order_message,
        "template_message" => $template_message,
         "date_created_message" => date("Y-m-d"),
    );

    $saveMessage = CurlController::request($url, $method, $fields);

    if($saveMessage->status == 200){

        /*=============================================
        Enviamos datos JSON a la API de WhatsApp
        =============================================*/

        $sendMessage = CurlController::apiWS($json, $getApiWS);

    }

    } // close if($getBot->status == 200)

  } // close function responseBots

    /*=============================================
    Respuesta dinámica con datos del pedido
    =============================================*/

    static public function responseBotsOrder($getApiWS, $phone_message, $order_message, $order) {

        $body  = "📦 ¡Encontramos tu pedido!\n\n";
        $body .= "🚚 Empresa de envío: " . $order->company_order . "\n\n";
        $body .= "👉 Rastrea tu pedido aquí:\n" . $order->tracking_url_order;

        $json = json_encode([
            "messaging_product" => "whatsapp",
            "recipient_type"    => "individual",
            "to"                => $phone_message,
            "type"              => "text",
            "text"              => ["preview_url" => false, "body" => $body]
        ]);

        $url    = "messages?linkTo=phone_message&equalTo=".$phone_message."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";
        $method = "GET";
        $fields = array();

        $getMessages = CurlController::request($url, $method, $fields);
        if($getMessages->status == 200){
            $order_message = $getMessages->results[0]->order_message + 1;
        }

        $url    = "messages?token=no&except=id_message";
        $method = "POST";
        $fields = array(
            "type_message"         => "business",
            "id_whatsapp_message"  => $getApiWS->id_whatsapp,
            "business_message"     => $body,
            "phone_message"        => $phone_message,
            "order_message"        => $order_message,
            "template_message"     => '{"type":"bot","title":"tracking_found"}',
            "date_created_message" => date("Y-m-d"),
        );

        $saveMessage = CurlController::request($url, $method, $fields);

        if($saveMessage->status == 200){

            CurlController::apiWS($json, $getApiWS);

            // Segundo mensaje: solo el código para que el usuario pueda copiarlo fácilmente
            $order_message++;

            $jsonCode = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type"    => "individual",
                "to"                => $phone_message,
                "type"              => "text",
                "text"              => ["preview_url" => false, "body" => $order->tracking_code_order]
            ]);

            $url    = "messages?token=no&except=id_message";
            $method = "POST";
            $fields = array(
                "type_message"         => "business",
                "id_whatsapp_message"  => $getApiWS->id_whatsapp,
                "business_message"     => $order->tracking_code_order,
                "phone_message"        => $phone_message,
                "order_message"        => $order_message,
                "template_message"     => '{"type":"bot","title":"tracking_code"}',
                "date_created_message" => date("Y-m-d"),
            );

            $saveCode = CurlController::request($url, $method, $fields);
            if($saveCode->status == 200){
                CurlController::apiWS($jsonCode, $getApiWS);
            }

            // Tercer mensaje: código de seguridad (si aplica, ej. Shalom)
            if(!empty($order->security_code_order)){

                $order_message++;

                $jsonSec = json_encode([
                    "messaging_product" => "whatsapp",
                    "recipient_type"    => "individual",
                    "to"                => $phone_message,
                    "type"              => "text",
                    "text"              => ["preview_url" => false, "body" => "🔐 Código de seguridad:\n" . $order->security_code_order]
                ]);

                $url    = "messages?token=no&except=id_message";
                $method = "POST";
                $fields = array(
                    "type_message"         => "business",
                    "id_whatsapp_message"  => $getApiWS->id_whatsapp,
                    "business_message"     => "🔐 Código de seguridad:\n" . $order->security_code_order,
                    "phone_message"        => $phone_message,
                    "order_message"        => $order_message,
                    "template_message"     => '{"type":"bot","title":"tracking_security"}',
                    "date_created_message" => date("Y-m-d"),
                );

                $saveSec = CurlController::request($url, $method, $fields);
                if($saveSec->status == 200){
                    CurlController::apiWS($jsonSec, $getApiWS);
                }
            }
        }

    } // close function responseBotsOrder

} // close class BotsController