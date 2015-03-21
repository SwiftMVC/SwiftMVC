<?php

/**
 * Description of users
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Users extends Controller {

    /**
     * Does three important things, first is retrieving the posted form data, second is checking each form field’s value
     * third thing it does is to create a new user row in the database
     */
    public function register() {
        $view = $this->getActionView();

        if (RequestMethods::post("save")) {
            $user = new User(array(
                "first" => RequestMethods::post("first"),
                "last" => RequestMethods::post("last"),
                "email" => RequestMethods::post("email"),
                "password" => RequestMethods::post("password")
            ));

            if ($user->validate()) {
                $user->save();
                $this->_upload("photo", $user->id);
                $this->actionView->set("success", true);
            }

            $this->actionView->set("errors", $user->errors);
        }
    }

    public function login() {
        if (RequestMethods::post("login")) {
            
            $email = RequestMethods::post("email");
            $password = RequestMethods::post("password");
            
            $view = $this->getActionView();
            $error = false;
            
            if (empty($email)) {
                $view->set("email_error", "Email not provided");
                $error = true;
            }
            
            if (empty($password)) {
                $view->set("password_error", "Password not provided");
                $error = true;
            }
            
            if (!$error) {
                $user = User::first(array(
                    "email = ?" => $email,
                    "password = ?" => $password,
                    "live = ?" => true,
                    "deleted = ?" => false
                ));
                
                if (!empty($user)) {
                    $session = Registry::get("session");
                    $session->set("user", serialize($user));
                    
                    header("Location: /users/profile.html");
                    exit();
                } else {
                    $view->set("password_error", "Email address and/or password are incorrect");
                }
            }
        }
    }

    public function profile() {
        $session = Registry::get("session");
        $user = unserialize($session->get("user", null));
        if (empty($user)) {
            $user = new StdClass();
            $user->first = "Mr.";
            $user->last = "Smith";
        }
        $this->getActionView()->set("user", $user);
    }

    public function search() {
        $view = $this->getActionView();

        $query = RequestMethods::post("query");
        $order = RequestMethods::post("order", "modified");
        $direction = RequestMethods::post("direction", "desc");
        $page = RequestMethods::post("page", 1);
        $limit = RequestMethods::post("limit", 10);

        $count = 0;
        $users = false;

        if (RequestMethods::post("search")) {
            $where = array(
                "SOUNDEX(first) = SOUNDEX(?)" => $query,
                "live = ?" => true,
                "deleted = ?" => false
            );

            $fields = array(
                "id", "first", "last"
            );

            $count = User::count($where);
            $users = User::all($where, $fields, $order, $direction, $limit, $page);
        }

        $view
                ->set("query", $query)
                ->set("order", $order)
                ->set("direction", $direction)
                ->set("page", $page)
                ->set("limit", $limit)
                ->set("count", $count)
                ->set("users", $users);
    }

    /**
     * @before _secure
     */
    public function settings() {
        $errors = array();

        if (RequestMethods::post("save")) {
            $this->user->first = RequestMethods::post("first");
            $this->user->last = RequestMethods::post("last");
            $this->user->email = RequestMethods::post("email");

            if (RequestMethods::post("password")) {
                $this->user->password = RequestMethods::post("password");
            }

            if ($this->user->validate()) {
                $this->user->save();
                $this->_upload("photo", $this->user->id);
                $this->actionView->set("success", true);
            }
            
            $errors = $this->user->errors;
        }
        $this->actionView->set("errors", $errors);
    }

    public function logout() {
        $this->setUser(false);

        $session = Registry::get("session");
        $session->erase("user");

        header("Location: /users/login.html");
        exit();
    }

    /**
     * @before _secure
     */
    public function friend($id) {
        $user = $this->getUser();

        $friend = new Friend(array(
            "user" => $user->id,
            "friend" => $id
        ));

        $friend->save();

        header("Location: /search.html");
        exit();
    }

    /**
     * @before _secure
     */
    public function unfriend($id) {
        $user = $this->getUser();

        $friend = Friend::first(array(
                    "user" => $user->id,
                    "friend" => $id
        ));

        if ($friend) {
            $friend = new Friend(array(
                "id" => $friend->id
            ));
            $friend->delete();
        }

        header("Location: /search.html");
        exit();
    }

    /**
     * @protected
     */
    public function _secure() {
        $user = $this->getUser();
        if (!$user) {
            header("Location: /login.html");
            exit();
        }
    }

    /**
     * The method checks whether a file has been uploaded. If it has, the method attempts to move the file to a permanent location.
     * @param type $name
     * @param type $user
     */
    protected function _upload($name, $user) {
        if (isset($_FILES[$name])) {
            $file = $_FILES[$name];
            $path = APP_PATH . "/public/uploads/";
            $time = time();
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = "{$user}-{$time}.{$extension}";
            if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
                $meta = getimagesize($path . $filename);
                if ($meta) {
                    $width = $meta[0];
                    $height = $meta[1];
                    $file = new File(array(
                        "name" => $filename,
                        "mime" => $file["type"],
                        "size" => $file["size"],
                        "width" => $width,
                        "height" => $height,
                        "user" => $user
                    ));
                    $file->save();
                }
            }
        }
    }

}
