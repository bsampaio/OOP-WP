<?php

namespace OOP_WP;

/**
 * Classe responsável pelo 'parsing' das globais $_SERVER
 * e $_REQUEST, automatizando diversos procedimentos de verificação de protocolos
 * e filtragem de inputs de usuário.
 * Também auxilia na resposta de erros e na construção de JSONs com codificação
 * UTF-8 ativa por padrão.
 * 
 * Created by Breno Grillo.
 * User: criativa
 * Date: 05/10/15
 * Time: 17:19
 */

class Request
{
    public static function isPost()
    {
        return self::isWhat('post');
    }

    public static function isGet()
    {
        return self::isWhat('get');
    }

    public static function isWhat($what)
    {
        if ($_SERVER['REQUEST_METHOD'] == strtoupper($what)) {
            return true;
        }

        return false;
    }

    public static function isValid($rules, $array)
    {
        $valid = true;
        foreach($rules as $rule){
            $valid &= isset($array[$rule]);
        }

        return $valid;
    }
    /**
     * Função para redirecionar o usuário à URL anterior, podendo
     * adicionar variáveis GET com o parametro $with
     * Ex.: Request::redirectBack('?success=true&campaign=google_ads')
     * @param  array  $with Array de parâmetros para adicionar ao redirect
     * @return void
     */
    public static function redirectBack($with = [''])
    {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . $with[0]);
    }
    
    /**
     * Emite um erro 500 com uma mensagem passada por parâmetro.
     * @param  string $message Mensagem de erro
     * @return void
     */
    public static function error($message = "Internal Server Error")
    {
        header('HTTP/1.1 500 Internal Server Error');
        echo $message;
    }

    public static function redirectTo($script = 'index.php')
    {
        return header("Location: $script");
    }

    public static function get($index) {
        return (isset($_GET[$index])) ? filter_var($_GET[$index], FILTER_SANITIZE_SPECIAL_CHARS) : '';
    }

    public static function post($index = null) {
        if(!$index) {
            return $_POST;
        }
        return (isset($_POST[$index])) ? filter_var($_POST[$index], FILTER_SANITIZE_SPECIAL_CHARS) : '';
    }

    public static function toJson(array $array) {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }
}