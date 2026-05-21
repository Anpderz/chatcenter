<?php

define('DIR', __DIR__);

ini_set("display_errors",1);
ini_set("log_errors",1);
ini_set("error_log", DIR."/php_error_log");

date_default_timezone_set("America/Lima");

require_once "../extensions/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once "../controllers/template.controller.php";
require_once "../controllers/curl.controller.php";

class ChatAjax{

	/*=============================================
	Revisar si hay mensajes nuevos en el chat
	=============================================*/

	public $phoneMessage;
	public $orderMessage;

	public function ajaxLastMessage(){

		$url = "messages?linkTo=phone_message&equalTo=".$this->phoneMessage."&startAt=".$this->orderMessage."&endAt=2";
		$method = "GET";
		$fields = array();

		$getMessages = CurlController::request($url,$method,$fields);

		if($getMessages->status == 200){

			if($getMessages->total > 1){

				/*=============================================
				Validar si el mensaje es del cliente
				=============================================*/

				if($getMessages->results[1]->type_message == "client"){

					$url = "whatsapps?linkTo=status_whatsapp&equalTo=1";
					$method = "GET";
					$fields = array();

					$getApiWS = CurlController::request($url,$method,$fields);

					if($getApiWS->status == 200){
						$getApiWS = $getApiWS->results[0];
					}

					if(isset(json_decode($getMessages->results[1]->client_message)->id) && isset(json_decode($getMessages->results[1]->client_message)->text)){

						$getMessages->results[1]->client_message = json_decode($getMessages->results[1]->client_message)->text;

					}else if(isset(json_decode($getMessages->results[1]->client_message)->type) && json_decode($getMessages->results[1]->client_message)->type == "image"){

						$archive = CurlController::apiWS($getApiWS,json_decode($getMessages->results[1]->client_message)->id."_ajax");
						$getMessages->results[1]->client_message = '<a href="'.$archive.'" target="_blank"><img src="'.$archive.'" class="img-fluid rounded"></a><br>'.urldecode(json_decode($getMessages->results[1]->client_message)->caption);

					}else{

						$getMessages->results[1]->client_message = $getMessages->results[1]->client_message;
					}

					$message = '<div class="msg user">
									<div class="pt-2" style="max-width:300px">'.$getMessages->results[1]->client_message.'</div><br>
									<span class="small text-muted float-end">
										'.TemplateController::formatDate(6,$getMessages->results[1]->date_updated_message).'
									</span>
								</div>';

					$response = array(
						"type" => "client",
						"message" => base64_encode($message),
						"lastOrder" => $getMessages->results[1]->order_message
					);

					echo json_encode($response);

				}

				/*=============================================
				Validar si el mensaje es del negocio
				=============================================*/

				if($getMessages->results[1]->type_message == "business"){

					$business_message = "";

					if(isset(json_decode($getMessages->results[1]->template_message)->type) && json_decode($getMessages->results[1]->template_message)->type == "bot"){

						$url = "bots?linkTo=title_bot&equalTo=".json_decode($getMessages->results[1]->template_message)->title;
						$method = "GET";
						$fields = array();

						$getBot = CurlController::request($url,$method,$fields);

						if($getBot->status == 200){

							$bot = $getBot->results[0];

							if(!empty($bot->header_image_bot)){
								$business_message .= '<a href="'.urldecode($bot->header_image_bot).'" target="_blank"><img src="'.urldecode($bot->header_image_bot).'" class="img-fluid rounded"></a>';
							}

							if(!empty($bot->header_text_bot)){
								$business_message .= '<div><strong>'.str_replace("\\n","<br>", urldecode($bot->header_text_bot)).'</strong></div>';
							}

							if(!empty($bot->header_video_bot)){
								$business_message .= '<video controls src="'.urldecode($bot->header_video_bot).'" class="img-fluid rounded"></video>';
							}

							if(!empty($bot->body_text_bot)){
								$business_message .= str_replace("\\n","<br>", urldecode($bot->body_text_bot));
							}else{
								$getMessages->results[1]->business_message = str_replace(["\r", "\n" ], '\n', $getMessages->results[1]->business_message);
								$business_message .= str_replace("\\n","<br>", urldecode($getMessages->results[1]->business_message));
							}

							if(!empty($bot->footer_text_bot)){
								$business_message .= '<hr><div><small>'.str_replace("\\n","<br>", urldecode($bot->footer_text_bot)).'</small></div>';
							}

							if($bot->type_bot == "interactive" && $bot->interactive_bot == "button"){
								foreach (json_decode(urldecode($bot->buttons_bot)) as $index => $item) {
									$business_message .= '<div class="small mt-2 border-top p-2 w-100 text-start bg-light"><i class="bi bi-arrow-90deg-left"></i> '.$item.'</div>';
								}
							}

							if($bot->type_bot == "interactive" && $bot->interactive_bot == "list"){
								foreach (json_decode(urldecode($bot->list_bot)) as $index => $item) {
									$business_message .= '<div class="small mt-2 border-top p-2 w-100 text-start bg-light"><strong>'.$item->title.'</strong><br>'.$item->description.'</div>';
								}
							}

							if($bot->type_bot == "interactive" && $bot->interactive_bot == "none"){

								$url = "messages?linkTo=order_message,phone_message&equalTo=".($getMessages->results[1]->order_message-1).",".$getMessages->results[1]->phone_message."&select=client_message";
								$method = "GET";
								$fields = array();

								$getMess = CurlController::request($url,$method,$fields);

								if($getMess->status == 200){
									$getMess = json_decode($getMess->results[0]->client_message)->id;
								}

								$url = "relations?rel=products,categories&type=product,category&linkTo=id_category&equalTo=".$getMess;
								$method = "GET";
								$fields = array();

								$getMenu = CurlController::request($url,$method,$fields);

								if($getMenu->status == 200){
									$menu = $getMenu->results;
									foreach ($menu as $index => $item) {
										$business_message .= '<div class="small mt-2 border-top p-2 w-100 text-start bg-light"><strong>'.urldecode($item->title_product).'</strong><br>$'.$item->price_product.' USD</div>';
									}
								}
							}
						}

					}else{

						$business_message = $getMessages->results[1]->business_message;
					}

					$message = '<div class="msg bot">
									<div class="pt-2" style="max-width:300px">'.preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', $business_message).'</div><br>
									<span class="small text-muted float-end">
										'.TemplateController::formatDate(6,$getMessages->results[1]->date_updated_message).'
									</span>
								</div>';

					$response = array(
						"type" => "business",
						"message" => base64_encode($message),
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

	public $conversation;
	public $phone;
	public $token;

	public function ajaxSendMessage(){

		$json = '{
				  "messaging_product": "whatsapp",
				  "recipient_type": "individual",
				  "to": "'.$this->phone.'",
				  "type": "text",
				  "text": {
				    "preview_url": true,
				    "body": "'.$this->conversation.'"
				  }
				}';

		$business_message = $this->conversation;
		$template_message = '{"type":"manual","title":""}';

		/*=============================================
		Llevar el orden de los mensajes
		=============================================*/

		$url = "messages?linkTo=phone_message&equalTo=".$this->phone."&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";
		$method = "GET";
		$fields = array();

		$getMessages = CurlController::request($url,$method,$fields);

		if($getMessages->status == 200){
			$order_message = $getMessages->results[0]->order_message + 1;
		}

		/*=============================================
		Capturar API WS activa
		=============================================*/

		$url = "whatsapps?linkTo=status_whatsapp&equalTo=1&orderBy=id_whatsapp&orderMode=DESC&startAt=0&endAt=1";

		$getApiWS = CurlController::request($url,$method,$fields);

		if($getApiWS->status == 200){
			$getApiWS = $getApiWS->results[0];
		}

		/*=============================================
		Guardamos la respuesta del negocio
		=============================================*/

		$url = "messages?token=".$this->token."&table=admins&suffix=admin";
		$method = "POST";
		$fields = array(
			"type_message" => "business",
			"id_whatsapp_message" => $getApiWS->id_whatsapp,
			"business_message" => $business_message,
			"phone_message" => $this->phone,
			"order_message" => $order_message,
			"template_message" => $template_message,
			"date_created_message" => date("Y-m-d")
		);

		$saveMessage = CurlController::request($url,$method,$fields);

		if($saveMessage->status == 200){

			/*=============================================
			Enviamos datos JSON a la API de WhatsApp
			=============================================*/

			$apiWS = CurlController::apiWS($json,$getApiWS);

		}

	}

	/*=============================================
	Traer último chat
	=============================================*/

	public $lastIdMessage;
	public $borderChat;

	public function ajaxLastChat(){

		/*=============================================
		Validamos que el último ID no esté en la vista
		=============================================*/

		$url = "messages?orderBy=id_message&orderMode=DESC&startAt=0&endAt=1&select=id_message";
		$method = "GET";
		$fields = array();

		$getMessage = CurlController::request($url,$method,$fields);

		if($getMessage->status == 200){

			if($this->lastIdMessage == $getMessage->results[0]->id_message){
				return;
			}else{
				$lastIdMessage = $getMessage->results[0]->id_message;
			}
		}

		/*=============================================
		Traemos los contactos
		=============================================*/

		$url = "contacts?orderBy=date_updated_contact&orderMode=DESC&startAt=0&endAt=10";
		$method = "GET";
		$fields = array();

		$getContacts = CurlController::request($url,$method,$fields);

		if($getContacts->status == 200){

			$contacts = $getContacts->results;

			foreach ($contacts as $key => $value) {

				/*=============================================
				Traemos la conversación más reciente
				=============================================*/

				$url = "messages?linkTo=phone_message&equalTo=".$value->phone_contact."&orderBy=id_message&orderMode=DESC&startAt=0&endAt=1";

				$getMessages = CurlController::request($url,$method,$fields);

				if($getMessages->status == 200){
					$messages = $getMessages->results[0];
				}else{
					$messages = null;
				}

				/*=============================================
				Todos los Mensajes
				=============================================*/

				if (!empty($messages)){

					$date_updated_message = $messages->date_updated_message;

					if ($messages->type_message == "business"){

						$textMessage = "...";
						$bellNotification = "";

					}else{

						$textMessage = mb_substr($messages->client_message,0,17)."...";
						$bellNotification = "ok";

						if($key == 0){
							$textSuccess = "text-success";
						}else{
							$textSuccess = "";
						}
					}

				/*=============================================
				Cuando no hay mensajes
				=============================================*/

				}else{

					$date_updated_message = $value->date_updated_contact;
					$textMessage = "";
					$bellNotification = "";
				}

				$chats = '<a href="/chat?phone='.$value->phone_contact.'_'.$value->ai_contact.'_'.$value->id_contact.'" class="text-dark" onclick="alertClick(1)">';

				if($this->borderChat != "" && $this->borderChat == $value->phone_contact){
					$chats .= '<div class="contact-item pb-0" style="border:1px solid #090">';
				}else{
					$chats .= '<div class="contact-item pb-0">';
				}

				$chats .= '<div class="d-flex justify-content-between pb-0">
				              <div>
				                <span class="small">';

				if($bellNotification == "ok"){
					$chats .= '<i class="bi bi-bell-fill '.$textSuccess.'"></i>';
				}

				$chats .= '<strong>
				                    +'.mb_substr($value->phone_contact,0,2).'
				                      '.mb_substr($value->phone_contact,2,3).'
				                      '.mb_substr($value->phone_contact,5,3).'
				                      '.mb_substr($value->phone_contact,8,3).'
				                  </strong>
				                </span>
				              </div>
				              <div>';

				if (date("Y-m-d") == TemplateController::formatDate(8,$date_updated_message)){
					$chats .= '<span class="small">'.TemplateController::formatDate(6,$date_updated_message).'</span>';
				}else{
					$chats .= '<span class="small">'.TemplateController::formatDate(5,$date_updated_message).'</span>';
				}

				$chats .= '</div>
				            </div>
				            <div class="d-flex justify-content-between pb-0">
				              <div>
				                <p class="small">'.$textMessage.'</p>
				              </div>
				              <div class="custom-control custom-checkbox">
				                <input
				                type="checkbox"
				                class="custom-control-input mt-2 form-check-input"
				                id="customCheck"
				                name="example1"';

				if ($value->ai_contact == 1){
					$chats .= 'checked';
				}

				$chats .= 'style="width:18px !important; height:18px !important">
				                <label class="custom-control-label mb-1" for="customCheck">
				                  <i class="fas fa-robot text-success"></i>
				                </label>
				              </div>
				            </div>
				          </div>
				        </a>';

				$response = array(
					"chats" => base64_encode($chats),
					"lastIdMessage" => $lastIdMessage,
					"phone" => $contacts[0]->phone_contact
				);

				echo json_encode($response).",";
			}
		}

	}

}

/*=============================================
Revisar si hay mensajes nuevos en el chat
=============================================*/

if(isset($_POST["phoneMessage"])){

	$ajax = new ChatAjax();
	$ajax->phoneMessage = $_POST["phoneMessage"];
	$ajax->orderMessage = $_POST["orderMessage"];
	$ajax->ajaxLastMessage();

}

/*=============================================
Conversaciones manuales por parte del negocio
=============================================*/

if(isset($_POST["conversation"])){

	$ajax = new ChatAjax();
	$ajax->conversation = $_POST["conversation"];
	$ajax->phone = $_POST["phone"];
	$ajax->token = $_POST["token"];
	$ajax->ajaxSendMessage();
}

/*=============================================
Traer último chat
=============================================*/

if(isset($_POST["lastIdMessage"])){

	$ajax = new ChatAjax();
	$ajax->lastIdMessage = $_POST["lastIdMessage"];
	$ajax->phone = $_POST["phone"];
	$ajax->borderChat = $_POST["borderChat"] ?? null;
	$ajax->ajaxLastChat();
}