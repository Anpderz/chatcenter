  <div class="contact-list">

    <input class="form-control rounded mb-2" type="text" placeholder="⌕ Buscar...">
    <h5>Chats recientes</h5>
    <?php if (!empty($contacts)): ?>
      <?php foreach ($contacts as $key => $value): ?>

        <a href="/chat?phone=<?php echo $value->phone_contact ?>_<?php echo $value->ai_contact ?>_<?php echo $value->id_contact ?>" class="text-dark" onclick="alertClick('(Cargando conversación...)')">
          <div class="contact-item pb-0">

            <div class="d-flex justify-content-between pb-0">
              <div><span class="small"><strong>+<?php echo mb_substr($value->phone_contact, 0, 2); ?>
                    <?php echo mb_substr($value->phone_contact, 2, 3); ?>
                    <?php echo mb_substr($value->phone_contact, 5, 3); ?>
                    <?php echo mb_substr($value->phone_contact, 8, 3); ?></strong>
                </span>
              </div>

              <div>


                <?php if (!empty($messages) && end($messages)->phone_message == $value->phone_contact): $date_updated_message = end($messages)->date_updated_message; ?>
                  <?php if (end($messages)->type_message == "business"): $textMessage = "..." ?>
                  <?php else: $textMessage = mb_substr(end($messages)->client_message, 0, 15) . "..."; ?>
                  <?php endif; ?>
                <?php else: ?>
                  <?php
                  $url = "messages?linkTo=phone_message&equalTo=" . $value->phone_contact . "&startAt=0&endAt=1&orderBy=id_message&orderMode=DESC";

                  $lastMessage = CurlController::request($url, $method, $fields);
                  if ($lastMessage->status == 200) {
                    $date_updated_message = $lastMessage->results[0]->date_updated_message;

                    if ($lastMessage->results[0]->type_message == "business") {
                      $textMessage = "...";
                    } else {
                      $textMessage = mb_substr($lastMessage->results[0]->client_message, 0, 15) . "...";
                    }
                  } else {
                    $date_updated_message = $value->date_updated_contact;
                    $textMessage = "No hay mensajes aún";
                  }
                  ?>
                <?php endif; ?>
                <?php if (date("Y-m-d") == TemplateController::formatDate(8, $date_updated_message)): ?>
                  <span class="small"> <?php echo TemplateController::formatDate(6, $date_updated_message); ?></span>
                <?php else: ?>
                  <span class="small"> <?php echo TemplateController::formatDate(5, $date_updated_message); ?></span>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex justify-content-between pb-0">
              <div>
                <p class="small"><?php echo $textMessage; ?></p>
              </div>

              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input mt-2 form-check-input" id="customCheck" name="example1" <?php if ($value->ai_contact == 1): ?> checked <?php endif ?> style="width:18px !important; height:18px !important">
                <label class="custom-control-label mb-1" for="customCheck"><i class="fas fa-robot text-success"></i></label>
              </div>
            </div>

          </div>

        </a>

      <?php endforeach; ?>
    <?php endif; ?>

  </div>