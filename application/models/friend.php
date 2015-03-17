<?php

/**
 * Description of friend
 *
 * @author Faizan Ayubi
 */
class Friend extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_friend;

    public function friend($id) {
        $user = $this->getUser();
        $friend = new Friend(array("user" => $user->id, "friend" => $id));
        $friend->save();
        header("Location: /search.html");
        exit();
    }

    public function unfriend($id) {
        $user = $this->getUser();
        $friend = Friend::first(array("user" => $user->id, "friend" => $id));
        if ($friend) {
            $friend = new Friend(array("id" => $friend->id));
            $friend->delete();
        }
        header("Location: /search.html");
        exit();
    }

}
