<?php

function render(string $file, object $data = null)
{
    if (!isset($_SESSION))
    {
        session_start([
            "name" => "Prog_Dynamique_TP2_Session",
            "cookie_secure" => "1",
            "cookie_httponly" => "1",
            "cookie_samesite" =>  "Strict",
            "use_strict_mode" => "1"
        ]);
    }
    
    require_once("config/config.php");
    
    $layout_file = AppConfig::VIEW_DIR . "/layouts/layout.php";
    
    ob_start();
    
    include_once($file);
    
    $content = ob_get_clean();
    
    include_once($layout_file);
}

function get_controller_name(string $controller): string
{
    return ucfirst($controller) . "Controller";
}

function trim_array($value, $key): void
{
    $value = trim($value);
}

function is_authenticated(): bool
{
    if (isset($_SESSION["fingerprint"]))
    {
        return $_SESSION["fingerprint"] == hash("sha512", $_SERVER["HTTP_USER_AGENT"] . $_SERVER["REMOTE_ADDR"]);
    }
    
    return false;
}