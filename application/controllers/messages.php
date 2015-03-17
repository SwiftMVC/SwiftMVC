<?php

/**
 * Description of messages
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;

class Messages extends Controller {

    /**
     * Collects posted form data into a new message database row. It performs the required validation.
     * and if everything looks good will save the record and redirect back to the home page.
     */
    public function add() {
        $user = $this->getUser();

        if (RequestMethods::post("share")) {
            $message = new Message(array(
                "body" => RequestMethods::post("body"),
                "message" => RequestMethods::post("message"),
                "user" => $user->id
            ));

            if ($message->validate()) {
                $message->save();
                header("Location: /");
                exit();
            }
        }
    }

}
