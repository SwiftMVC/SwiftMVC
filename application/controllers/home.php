<?php

/**
 * The Default Example Controller Class
 *
 * @author Faizan Ayubi, Hemant Mann
 */
use Framework\Controller as Controller;

class Home extends Controller {

    public function index() {
    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

        $view = $this->getActionView(); // Gets the property _actionView
        $view->set('ip', $this->request->getIp());
        $view->set('headers', $this->request->headerBag()->all());
    }

    public function post() {
    	$view = $this->getActionView();	// Gets the property _actionView
    	if ($this->request->isPost()) {
    		$view->set('message', 'POST request!!');
    	} else {
    		$view->set('message', 'request is not post!!');
    	}
    }

}
