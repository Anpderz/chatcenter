<?php

$limitContact = 10;
$limitMessage = 10;
$contacts = array();
$messages  = array();

/*=============================================
Traemos los contactos
=============================================*/

$url    = "contacts?orderBy=date_updated_contact&orderMode=DESC&startAt=0&endAt=".$limitContact;
$method = "GET";
$fields = array();

$getContacts = CurlController::request($url, $method, $fields);

if ($getContacts->status == 200) {

    $contacts = $getContacts->results;

    /*=============================================
    Traemos los mensajes del contacto activo
    =============================================*/

    if (isset($_GET["phone"])) {
        $url = "messages?linkTo=phone_message&equalTo=".explode("_", $_GET["phone"])[0]."&orderBy=id_message&orderMode=DESC&startAt=0&endAt=".$limitMessage;
    } else {
        $url = "messages?linkTo=phone_message&equalTo=".$contacts[0]->phone_contact."&orderBy=id_message&orderMode=DESC&startAt=0&endAt=".$limitMessage;
    }

    $getMessages = CurlController::request($url, $method, $fields);

    if ($getMessages->status == 200) {
        $messages = $getMessages->results;
    }

}
?>
<div class="container-fluid p-0">
  <div class="main-container">
    <?php
    include "modules/chat-container/chat-container.php";
    include "modules/contact-list/contact-list.php";
    ?>
  </div>
</div>
