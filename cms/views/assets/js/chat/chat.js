/*=============================================
Desbloquear audio en el primer click del usuario
=============================================*/

$(document).one("click", function(){

	["#chatSound","#messageSound"].forEach(function(id){
		var el = $(id)[0];
		if(el && el.src) el.play().then(function(){ el.pause(); el.currentTime = 0; }).catch(function(){});
	});

});

/*=============================================
Mover el scroll hasta la última conversación
=============================================*/

function scrollMoveToEnd(){

	$(document).ready(function() {

		var messages = $(".msg:last");

		if(messages.length > 0){

			$("html, body, #chatBody").animate({

				scrollTop: $("#chatBody")[0].scrollHeight

			},500);

		}

	})

}

scrollMoveToEnd();

/*=============================================
Revisar si hay mensajes nuevos en el chat
=============================================*/

var interval = setInterval(function(){

	var phoneMessage = $("#phoneMessage").val();
	var orderMessage = $("#orderMessage").val();

	if(phoneMessage != undefined && orderMessage != undefined){

		intervalMessage(phoneMessage, orderMessage);
	}

	/*=============================================
	Función para identificar nuevos chats
	=============================================*/

	intervalChat();

}, 2000);

/*=============================================
Función si hay mensajes nuevos en el chat
=============================================*/

function intervalMessage(phoneMessage, orderMessage){

	var data = new FormData();
	data.append("phoneMessage", phoneMessage);
	data.append("orderMessage", orderMessage);

	$.ajax({
		url: "/ajax/chat.ajax.php",
		method: "POST",
		data: data,
		contentType: false,
		cache: false,
		processData: false,
		success: function (response){

			// console.log("response", response);

			if(response != ""){

				// console.log("response", response);

				var response = JSON.parse("["+response+"]");

				$("#chatBody").append(decodeURIComponent(escape(atob(response[0].message))));
				$("#orderMessage").val(response[0].lastOrder);

				/*=============================================
				Sonido cuando el cliente escribe un nuevo mensaje en el chat actual
				=============================================*/

				if(response[0].type == "client" && $("#messageSound").attr("src")){

					$("#messageSound")[0].play().catch(function(){});

				}

				scrollMoveToEnd();

			}

		}

	})

}

/*=============================================
Respondiendo Chat Manualmente desde el botón
=============================================*/

$(document).on("click", ".send", function(){

	var conversation = $("#userInput").val();

	sendMessage(conversation);

})

/*=============================================
Respondiendo Chat Manualmente con Enter
=============================================*/

$("#userInput").keyup(function(event){

	event.preventDefault();

	if(event.keyCode == 13 && $("#userInput").val() != ""){

		var conversation = $("#userInput").val();

		sendMessage(conversation);
	}

})

/*=============================================
Función para enviar la conversación
=============================================*/

function sendMessage(conversation){

	suppressChatSound = true;

	$("#userInput").val("");

	var data = new FormData();
	data.append("conversation", conversation);
	data.append("phone", $("#phoneMessage").val());
	data.append("token", localStorage.getItem("tokenAdmin"));

	$.ajax({
		url: "/ajax/chat.ajax.php",
		method: "POST",
		data: data,
		contentType: false,
		cache: false,
		processData: false,
		success: function (response){

			// console.log("response", response);
		}

	})

}

/*=============================================
Función para identificar nuevos chats
=============================================*/

var firstChatInterval = true;
var suppressChatSound = false;

function intervalChat(){

	var data = new FormData();
	data.append("lastIdMessage", $("#lastIdMessage").attr("lastIdMessage"));
	data.append("phone", $("#phoneMessage").val());
	data.append("borderChat", $("#borderChat").val());

	$.ajax({
		url: "/ajax/chat.ajax.php",
		method: "POST",
		data: data,
		contentType: false,
		cache: false,
		processData: false,
		success: function (response){

			if(response != ""){

				$("#lastIdMessage").html('');

				// console.log("response", response);

				var response = JSON.parse("["+response.slice(0,-1)+"]");

				$("#lastIdMessage").attr("lastIdMessage", response[0].lastIdMessage);

				/*=============================================
				Sonido cuando el cliente tiene una conversación nueva
				=============================================*/

				if(!firstChatInterval && !suppressChatSound && response[0].phone != $("#phoneMessage").val() && $("#chatSound").attr("src")){

					$("#chatSound")[0].play().catch(function(){});
				}

				firstChatInterval = false;
				suppressChatSound = false;

				response.forEach((e,i) => {

					$("#lastIdMessage").append(decodeURIComponent(escape(atob(e.chats))));

				})

			}
		}

	})

}
