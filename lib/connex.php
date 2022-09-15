<?php

final class DatabaseConnection
{
    public static function get_database_connection(): mysqli
    {
        $connection = mysqli_connect("localhost", "e1929723", "cymWxk57fg3JjjUY8J6m", "e1929723");
        
        mysqli_set_charset($connection, "utf8");
        
        return $connection;
    }
}