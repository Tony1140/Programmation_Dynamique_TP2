<?php

class AppConfig
{
    public const MODEL_DIR = "models";
    public const VIEW_DIR = "views";
    public const CONNEX_DIR = "lib/connex.php";
    
    public const MVC_CONFIG = [
        "default_module" => "home",
        "default_action" => "index"
    ];
}