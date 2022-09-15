<?php

require_once("config/config.php");
require_once("lib/core.php");

$module = isset($_REQUEST["module"]) ? filter_input(INPUT_GET, "module", FILTER_SANITIZE_FULL_SPECIAL_CHARS) : AppConfig::MVC_CONFIG["default_module"];
$action = isset($_REQUEST["action"]) ? filter_input(INPUT_GET, "action", FILTER_SANITIZE_FULL_SPECIAL_CHARS) : AppConfig::MVC_CONFIG["default_action"];

$controller_file = "controllers/" . ucfirst($module) . "Controller.php";

if (!file_exists($controller_file))
{
    trigger_error("Invalid controller");
    
    exit;
}

require_once($controller_file);

$function = get_controller_name($module) . "::" . ucfirst($action);

/*if (!function_exists($function))
{
    trigger_error("Invalid controller action");
    
    exit;
}*/

call_user_func($function);