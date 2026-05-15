 <div class="chat-container">

   <div class="chat-header d-flex justify-content-between">

     <?php if (!empty($contacts)): ?>
    <!--=============================================
    Validar Asistente 
    =============================================-->  
       <div class="bot-name bg-white rounded p-1" id="botName">
         <div class="custom-control custom-checkbox">
           <input type="checkbox" class="custom-control-input mt-2 form-check-input" id="customCheck" name="example1" 
           <?php if (isset($_GET["phone"]) && (explode("_", $_GET["phone"])[1] ?? 0) == 1 || $contacts[0]->ai_contact == 1): ?>
           checked <?php endif?> style="width:18px !important; height:18px !important">
           <label class="custom-control-label mb-1 me-1" for="customCheck"><i class="fas fa-robot text-success"></i></label>
         </div>

       </div>
       <div class="text-center">+<?php echo mb_substr($contacts[0]->phone_contact, 0, 2); ?>
       <?php if (isset($_GET["phone"])): $phoneContact = explode("_", $_GET["phone"])[0]; ?>
       <?php else: $phoneContact = $contacts[0]->phone_contact; ?>
       <?php endif; ?>
         <?php echo mb_substr($phoneContact, 2, 3); ?>
         <?php echo mb_substr($phoneContact, 5, 3); ?>
         <?php echo mb_substr($phoneContact, 8, 3); ?></div>
       <div>
         <button type="button" class="btn btn-sm text-white rounded m-0 px-1 py-0 border-0"><i class="bi bi-pencil-square"></i></button>
         <button type="button" class="btn btn-sm text-white rounded m-0 px-1 py-0 border-0"><i class="bi bi-trash"></i></button>
       </div>
     <?php endif; ?>
   </div>

   <div class="chat-body" id="chatBody">
     <?php if (!empty($messages)): $messages = array_reverse($messages); ?>
      <input type="hidden" id="phoneMessage" value="<?php echo $phoneContact; ?>">
      <input type="hidden" id="orderMessage" value="<?php echo end($messages)->order_message; ?>">
       <div class="d-flex justify-content-center text-center"><span class="badge border bg-success rounded py-2 px-2">
           <?php if (date("Y-m-d") == TemplateController::formatDate(8, end($messages)->date_updated_message)): ?>
             Hoy
           <?php else: ?>
             <?php echo TemplateController::formatDate(7, $messages[0]->date_updated_message); ?>
           <?php endif; ?>
         </span>
       </div>
       <?php foreach ($messages as $key => $value): ?>
         <?php if ($value->type_message == "business"): ?>
           <?php include "business/business.php"; ?>
         <?php else: ?>
           <?php include "client/client.php"; ?>
         <?php endif; ?>
       <?php endforeach; ?>
     <?php endif; ?>
   </div>

   <div class="chat-footer position-relative w-100">

     <button type="button" class="me-2 attach"><i class="fas fa-paperclip"></i></button>
     <input type="text" id="userInput" placeholder="Escribe tu mensaje...">
     <button type="button" class="send"><i class="fas fa-paper-plane"></i></button>

   </div>

 </div>