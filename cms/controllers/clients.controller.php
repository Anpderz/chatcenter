<?php 

Class ClientsController{

	static public function responseClients($getApiWS,$phone_message,$order_message,$type_conversation){

        $idListMenu = null;
		
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

            $getContact = CurlController::request($url,$method,$fields);

            if($getContact->status != 200){

            	/*=============================================
            	Creamos el contacto
            	=============================================*/

            	$url = "contacts?token=no&except=id_contact";
            	$method = "POST";
            	$fields = array(
            		"phone_contact" => $phone_message,
            		"ai_contact" => $getApiWS->ai_whatsapp,
            		"date_created_contact" => date("Y-m-d")
            	);

            	$createContact = CurlController::request($url,$method,$fields);
            
            }else{

            	/*=============================================
            	Actualizamos la fecha de la última conversación con el contacto
            	=============================================*/
            	$url = "contacts?id=".$getContact->results[0]->id_contact."&nameId=id_contact&token=no&except=id_contact";
            	$method = "PUT";
            	$fields = array(
            		"date_updated_contact" => date("Y-m-d H:i:s")
            	);

            	$fields = http_build_query($fields);

            	$updateContact = CurlController::request($url,$method,$fields);

            }

            /*=============================================
            Respuesta con el chatbot
            =============================================*/

            if($getApiWS->ai_whatsapp == 0){

            	/*=============================================
            	Respuesta con Plantilla Bot
            	=============================================*/

            	$responseBots = BotsController::responseBots("welcome",$getApiWS,$phone_message,$order_message,$idListMenu);
            	echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';
            
            }
		
        }else{

            /*=============================================
            Buscamos el contacto
            =============================================*/

            $url = "contacts?linkTo=phone_contact&equalTo=".$phone_message;
            $method = "GET";
            $fields = array();

            $getContact = CurlController::request($url,$method,$fields);

            /*=============================================
            Actualizamos la fecha de la última conversación con el contacto
            =============================================*/
            $url = "contacts?id=".$getContact->results[0]->id_contact."&nameId=id_contact&token=no&except=id_contact";
            $method = "PUT";
            $fields = array(
                "date_updated_contact" => date("Y-m-d H:i:s")
            );

            $fields = http_build_query($fields);

            $updateContact = CurlController::request($url,$method,$fields);

            /*=============================================
            Traer el último mensaje del bot (para saber qué plantilla se envió)
            =============================================*/

            $url = "messages?linkTo=type_message,phone_message&equalTo=business,".$phone_message."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";
            $method = "GET";
            $fields = array();

            $getLastBot = CurlController::request($url,$method,$fields);
            $lastTemplate = ($getLastBot->status == 200) ? $getLastBot->results[0]->template_message : null;

            /*=============================================
            Traer la última conversacion del cliente (para saber qué botón presionó)
            =============================================*/

            $url = "messages?linkTo=type_message,phone_message&equalTo=client,".$phone_message."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";

            $getMessage = CurlController::request($url,$method,$fields);

            if($getMessage->status == 200){

                $message = $getMessage->results[0];

                /*=============================================
                Si se envió la plantilla "welcome"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"welcome"}'){

                    /*=============================================
                    Si la respuesta es interactiva
                    =============================================*/

                    if($type_conversation == "interactive"){

                        /*=============================================
                         Si la respuesta es 1: Realizar pedido
                        =============================================*/

                        if(json_decode($message->client_message)->id == 1){

                            $responseBots = BotsController::responseBots("menu",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';
                        
                        }


                        /*=============================================
                         Si la respuesta es 2: Pedido
                        =============================================*/

                        if(json_decode($message->client_message)->id == 2){

                            $responseBots = BotsController::responseBots("tracking_email",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }

                        /*=============================================
                         Si la respuesta es 3: Atención al cliente
                        =============================================*/

                        if(json_decode($message->client_message)->id == 3){

                            $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';
                        
                        }

                    }else{

                        $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';
                    }
                
                }

                /*=============================================
                Si se envió la plantilla "reservation"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"reservation"}'){

                    $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';
                }

                /*=============================================
                Si se envió la plantilla "menu"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"menu"}' ||
                   $lastTemplate == '{"type":"bot","title":"reset"}'){

                    /*=============================================
                    Si la respuesta es interactiva
                    =============================================*/

                    if($type_conversation == "interactive"){

                        if(is_numeric(json_decode($message->client_message)->id)){

                            $idListMenu = json_decode($message->client_message)->id;

                            $responseBots = BotsController::responseBots("listMenu",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }else{

                            if(json_decode($message->client_message)->id == "domicilio"){

                                $responseBots = BotsController::responseBots("delivery",$getApiWS,$phone_message,$order_message,$idListMenu);
                                echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                            }else{

                                $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                                echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                            }
     
                        }

                    }else{

                      $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                        echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                    }

                }

                /*=============================================
                Si se envió la plantilla "lista de menu"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"listMenu"}'){

                    /*=============================================
                    Si la respuesta es interactiva
                    =============================================*/

                    if($type_conversation == "interactive"){

                        $responseBots = BotsController::responseBots("reset",$getApiWS,$phone_message,$order_message,$idListMenu);
                        echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                    }else{

                        if($message->client_message == "Menú" || 
                           $message->client_message == "menú" ||
                           $message->client_message == "Menu" ||
                           $message->client_message == "MENÚ" ||
                           $message->client_message == "MENU" ||
                           $message->client_message == "menu"){

                            $responseBots = BotsController::responseBots("menu",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }else{

                            $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';
                        }
                    }

                }

                /*=============================================
                Si se envió la plantilla "name"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"name"}'){

                    $responseBots = BotsController::responseBots("phone",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "phone"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"phone"}'){

                    $responseBots = BotsController::responseBots("email",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "email"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"email"}'){

                    $responseBots = BotsController::responseBots("address",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "address"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"address"}'){

                    $responseBots = BotsController::responseBots("process",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "confirmation"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"confirmation"}'){
                   
                    /*=============================================
                    Si la respuesta es interactiva
                    =============================================*/

                    if($type_conversation == "interactive"){

                        if(json_decode($message->client_message)->id == 1){

                            $responseBots = BotsController::responseBots("checkout",$getApiWS,$phone_message,$order_message,$message->id_conversation_message);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }else{

                            $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>'; 
                        }

                    }else{

                      $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                    }

                }

                /*=============================================
                Si se envió la plantilla "checkout"
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"checkout"}'){

                    $responseBots = BotsController::responseBots("conversation",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "tracking_email" → pedir DNI
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"tracking_email"}'){

                    $responseBots = BotsController::responseBots("tracking_dni",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "tracking_not_found" → reintentar o volver al menú
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"tracking_not_found"}'){

                    if($type_conversation == "interactive"){

                        if(json_decode($message->client_message)->id == 0){

                            // Reintentar: volver a pedir el correo
                            $responseBots = BotsController::responseBots("tracking_email",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }else{

                            // Volver al menú principal
                            $responseBots = BotsController::responseBots("welcome",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }

                    }

                }

                /*=============================================
                Si se envió la plantilla "tracking_code" o "tracking_security" → resetear al menú
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"tracking_code"}' ||
                   $lastTemplate == '{"type":"bot","title":"tracking_security"}'){

                    $responseBots = BotsController::responseBots("welcome",$getApiWS,$phone_message,$order_message,$idListMenu);
                    echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                }

                /*=============================================
                Si se envió la plantilla "tracking_dni" → buscar pedido
                =============================================*/

                if($lastTemplate == '{"type":"bot","title":"tracking_dni"}'){

                    $dni = preg_replace('/[^0-9]/', '', trim($message->client_message));

                    /*=============================================
                    Traer el correo (penúltimo mensaje del cliente)
                    =============================================*/

                    $url = "messages?linkTo=type_message,phone_message&equalTo=client,".$phone_message."&startAt=1&endAt=2&orderBy=id_message&orderMode=DESC";
                    $getEmailMsg = CurlController::request($url,$method,$fields);

                    if($getEmailMsg->status == 200){

                        $email = filter_var(trim($getEmailMsg->results[0]->client_message), FILTER_SANITIZE_EMAIL);

                        if(filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($dni) >= 7){

                            /*=============================================
                            Buscar pedido en la BD
                            =============================================*/

                            $url = "orders?linkTo=email_order,dni_order&equalTo=".$email.",".$dni;
                            $getOrder = CurlController::request($url,$method,$fields);

                            if($getOrder->status == 200){

                                $responseBots = BotsController::responseBotsOrder($getApiWS,$phone_message,$order_message,$getOrder->results[0]);
                                echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                            }else{

                                $responseBots = BotsController::responseBots("tracking_not_found",$getApiWS,$phone_message,$order_message,$idListMenu);
                                echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                            }

                        }else{

                            $responseBots = BotsController::responseBots("tracking_not_found",$getApiWS,$phone_message,$order_message,$idListMenu);
                            echo '<pre>$responseBots '; print_r($responseBots); echo '</pre>';

                        }

                    }

                }

            }

        }
	}
}
