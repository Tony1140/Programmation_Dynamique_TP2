<?php

require_once("config/config.php");
require_once("lib/core.php");

final class HomeController
{
    public static function Index()
    {
        render(AppConfig::VIEW_DIR . "/home/index.php");
    }
}