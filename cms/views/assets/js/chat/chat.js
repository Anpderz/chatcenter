/*==============================================
Revisar si hay mensajes nuevos en el chat
==============================================*/

var intervalChat = setInterval(function() {
    var phoneMessage = $("#phoneMessage").val();
    var orderMessage = $("#orderMessage").val();

    if (phoneMessage && orderMessage) {
        intervalMessage(phoneMessage, orderMessage);
    }
}, 2000);

/*==============================================
Revisar si hay mensajes nuevos en el chat
==============================================*/

function intervalMessage(phoneMessage, orderMessage) {
    var data = new FormData();
    data.append("phone_message", phoneMessage);
    data.append("order_message", orderMessage);

    $.ajax({
        url: "/ajax/chat.ajax.php",
        method: "POST",
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response != "") {
                var parsed = JSON.parse("[" + response + "]");
                $("#chatBody").append(decodeURIComponent(escape(atob(parsed[0].message))));
                $("#orderMessage").val(parsed[0].lastOrder);
                intervalMessage($("#phoneMessage").val(), parsed[0].lastOrder);
            }
        }
    });
}

/*==============================================
Respondiendo chat manualmente desde el botón
==============================================*/

$(document).on("click", ".send", function() {
    var conversation = $('#userInput').val();
    sendMessage(conversation);
}) 

/*==============================================
Respondiendo chat manualmente desde el botón
==============================================*/

$("#userInput").keyup(function(event) {
    event.preventDefault();
    if (event.keyCode === 13 && $("#userInput").val() != "") {
        var conversation = $('#userInput').val();
        sendMessage(conversation);
    }
})
/*==============================================
Funcion para enviar la conversación
==============================================*/
function sendMessage(conversation) {

    var phoneMessage = $("#phoneMessage").val();
    var token = localStorage.getItem("tokenAdmin");

    if (!phoneMessage || !token) {
        console.error("Falta phone o token:", phoneMessage, token);
        return;
    }

    $("#userInput").val("");

    var data = new FormData();
    data.append("conversation", conversation);
    data.append("phone", phoneMessage);
    data.append("token", token);

    $.ajax({
        url: "/ajax/chat.ajax.php",
        method: "POST",
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(response) {
            console.log("sendMessage response:", response);
            if (response != "") {
                var parsed = JSON.parse("[" + response + "]");
                if (parsed[0].status == "ok") {
                    var time = new Date().toLocaleTimeString("es-PE", { hour: "2-digit", minute: "2-digit" });
                    var html = '<div class="msg bot"> ' + conversation + ' <br><span class="small text-muted float-end mt-2">' + time + '</span></div>';
                    $("#chatBody").append(html);
                    var newOrder = parseInt($("#orderMessage").val()) + 1;
                    $("#orderMessage").val(newOrder);
                    $(".contact-list a[href*=\"" + phoneMessage + "\"] p.small").text("...");
                }
            }
        }
    });
}