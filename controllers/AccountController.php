<?php

require_once("config/config.php");
require_once("lib/core.php");

final class AccountController
{
    public static function Login()
    {
        render(AppConfig::VIEW_DIR . "/account/login.php");
    }
    
    public static function Register()
    {
        render(AppConfig::VIEW_DIR . "/account/register.php");
    }
    
    public static function Logout()
    {
        render(AppConfig::VIEW_DIR . "/account/logout.php");
    }
    
    public static function Forums()
    {
        render(AppConfig::VIEW_DIR . "/account/forums.php");
    }
}