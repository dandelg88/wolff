<?php

namespace Core;

class Loader {

    private $library;
    private $session;
    private $cache;
    private $upload;
    private $extension;


    public function __construct($session, $cache, $upload, $extension) {
        $this->template = new Template($cache);
        $this->session = &$session;
        $this->cache = &$cache;
        $this->upload = &$upload;
        $this->extension = &$extension;
    }
    

    /**
     * Load a model in the indicated directory
     * @param dir the model directory
     * @return object the model
     */
    public function model(string $dir) {
        //Sanitize directory
        $dir = sanitizePath($dir);
        $file_path = APP . 'model/' . $dir . '.php';

        if (!modelExists($dir)) {
            error_log("Warning: The model '" . $dir . "' doesn't exists"); 
            return null;
        }

        $dir = str_replace('/', '\\', $dir);
        $class = 'Model' . '\\' . $dir;

        $model = new $class($this, $this->session, $this->cache);
        $model->index();

        return $model;
    }


    /**
     * Load a controller in the indicated directory
     * @param string $dir the controller directory
     * @return object the controller
     */
    public function controller(string $dir) {
        //Sanitize directory
        $dir = sanitizePath($dir);

        //load controller default function and return it
        if (controllerExists($dir)) {
            $controller = $this->getController($dir);
            $controller->index();
            return $controller;
        }

        //Get a possible function from the url
        $function = substr($dir, strrpos($dir, '/') + 1);
        $dir = substr($dir, 0, strrpos($dir, '/'));

        //load controller indicated function and return it
        if (controllerExists($dir)) {
            $controller = $this->getController($dir);
            $controller->$function();
            return $controller;
        }

        return null;
    }


    /**
     * Get a controller with its main variables initialized
     * @param string $dir the controller directory
     * @return object the controller with its main variables initialized
     */
    private function getController(string $dir) {
        $dir = str_replace('/', '\\', $dir);
        $class = 'Controller' . '\\' . $dir;
        
        $controller = new $class($this, $this->session, $this->cache, $this->upload, $this->extension);
        return $controller;
    }


    /**
     * Load a language in the indicated directory
     * @param string $dir the language directory
     * @param string $language the language selected
     */
    public function language(string $dir, string $language = LANGUAGE) {
        //Sanitize directory
        $dir = sanitizePath($dir);
        $file_path = $_SERVER['DOCUMENT_ROOT'] . APP . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $dir . '.php';
        
        if (languageExists($dir)) {
            include_once($file_path);
        }

        if (!isset($data)) {
            error_log("Warning: The " . $language . " language for '" . $dir . "' doesn't exists"); 
        } else {
            return $data;
        }

    }


    /**
     * Load a library in the indicated directory
     * @param string $dir the library directory
     */
    public function library(string $dir) {
        $dir = sanitizeURL($dir);
        $name = substr($dir, strrpos($dir, '/'));

        if ($name == 'library') {
            error_log("Warning: The library shouldn't be named library"); 
            return null;
        }
        
        if (!libraryExists($dir)) {
            error_log("Warning: The library '" . $dir . "' doesn't exists"); 
            return null;
        }

        //Initialize the library for the object which called this function
        $dir = str_replace('/', '\\', $dir);
        $className = 'Library' . '\\' . $dir;
        return new $className;
    }

    
    /**
     * Load a view in the indicated directory
     * @param string $dir the view directory
     * @param array $data the view data
     * @param bool $cache use or not the cache system
     */
    public function view(string $dir, array $data = array(), bool $cache = true) {
        $dir = sanitizePath($dir);
        $this->template->get($dir, $data, $cache, false);
    }


    /**
     * Get a view in the indicated directory
     * @param string $dir the view directory
     * @param array $data the data
     * @return string the view
     */
    public function getView(string $dir, array $data = array()) {
        $dir = sanitizePath($dir);
        return $this->template->get($dir, $data, false, true);
    }


    /**
     * Load the 404 view page
     */
    public function redirect404() {
        header("HTTP/1.0 404 Not Found");
        $controller = $this->controller('_404');
        die();
    }

}