<?php

namespace Ligne;

/**
 * Captura los errores comunes que pueden ocurrir
 * dentro de un proyecto, esto no manja todos los errores del framework ya que
 * otras cosas especificas como errores al pasar un url
 * o errores de archivo de vista son manejados desde el controlador
 * principal
 *
 * Maneja todos los errores faltales, warning, notice.
 **/
class ErrorHandler
{
    /**
     * dev = Developer
     * pro = Production
     */
    private $enviroment;

    public function __construct($enviroment)
    {
        $this->enviroment = $enviroment;
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'fatalHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($this->is_notice($errno) || $this->is_warning($errno) || $this->is_fatal_error($errno)) {
            $this->showDevMessages($errstr, "<span class='code'>$errstr</span> " . $errfile  . " <span class='code'>on line $errline</span>");
        }
    }

    /**
     * Esta funcion es utilizada para mostrar los errores mas comunes en una vista
     * mas amigable para el desarrollador, no deberia estar en modo de produccion ya que
     * prodria revelar datos que usted no desea que los usuarios sepan
     *
     * Acceso a base de datos, rutas, nombre de clases, directorios, etc...
     * @param $header
     * @param $description
     * @param null $route
     */
    public function showDevMessages($header, $description, $route = null)
    {
        if ($this->enviroment == 'dev') {
            if (ob_get_length() > 0) ob_clean();
            http_response_code(500);
            include(__DIR__ . '/views/dev/view.php');
            die();
        } elseif ($this->enviroment == 'pro') {
            http_response_code(500);
            include(__DIR__ . '/views/production/index.html');
            die();
        }
    }

    public function fatalHandler()
    {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if ($error !== NULL) {
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
            $this->showDevMessages(substr($errstr, 0, 50) . '...', "<p class='error-description'>$errstr</p> " . $errfile  . " <span class='code'>on line $errline</span>");
        }
    }

    private function is_notice($errno)
    {
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
                return true;
        }
    }
    private function is_warning($errno)
    {
        switch ($errno) {
            case E_WARNING:
            case E_USER_WARNING:
                return true;
        }
    }

    private function is_fatal_error($errno)
    {
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                return true;
        }
    }
}
