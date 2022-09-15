<?php

require_once("config/config.php");
require_once(AppConfig::CONNEX_DIR);
require_once(AppConfig::MODEL_DIR . "/Forum.php");

final class ForumsController
{
    public static function get_all_forums(): array
    {
        $sql = "SELECT * FROM forum ORDER BY date_publication DESC;";
        
        $connection = DatabaseConnection::get_database_connection();
        
        $result = mysqli_query($connection, $sql);
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        $forums = [];
        
        foreach ($result as $queryResult)
        {
            $forum = Forum::from_mysqli_row($queryResult);
            
            $forums[] = $forum;
            
            unset($queryResult);
        }
        
        mysqli_close($connection);
        
        unset($sql, $connection, $result);
        
        return $forums;
    }
    
    public static function get_all_forums_from_user(int $userId): array
    {
        $sql = "SELECT * FROM forum WHERE id_utilisateur = ?;";
        
        $connection = DatabaseConnection::get_database_connection();
        $statement = mysqli_prepare($connection, $sql);
        
        mysqli_stmt_bind_param($statement, "i", $userId);
        mysqli_stmt_execute($statement);
        
        $result = mysqli_stmt_get_result($statement);
        $forums = [];
        
        foreach ($result as $queryResult)
        {
            $forum = Forum::from_mysqli_row($queryResult);
            
            $forums[] = $forum;
        }
        
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($sql, $connection, $statement, $result);
        
        return $forums;
    }
    
    public static function get_forum(int $id): ?Forum
    {
        $sql = "SELECT * FROM forum WHERE id = ?;";
        
        $connection = DatabaseConnection::get_database_connection();
        $statement = mysqli_prepare($connection, $sql);
        
        $forum = null;
        
        mysqli_stmt_bind_param($statement, "i", $id);
        mysqli_stmt_execute($statement);
    
        $result = mysqli_stmt_get_result($statement);
        $rowCount = mysqli_num_rows($result);
        
        if ($rowCount == 1)
        {
            $queryResult = mysqli_fetch_assoc($result);
            
            $forum = Forum::from_mysqli_row($queryResult);
            
            unset($queryResult);
        }
        
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($sql, $connection, $statement, $result, $rowCount);
        
        return $forum;
    }
    
    public static function create_forum(Forum $forum): void
    {
        if (mb_strlen($forum->titre) < 5 || mb_strlen($forum->titre) > 100)
        {
            throw new LengthException("Le titre de l'article doit contenir entre 5 et 100 caractères");
        }
        
        if (mb_strlen($forum->article) > 1000)
        {
            throw new LengthException("Le contenu de l'article ne doit pas contenir plus de 1000 caractères");
        }
        
        if (!isset($forum->id_utilisateur) || !filter_var($forum->id_utilisateur, FILTER_VALIDATE_INT))
        {
            throw new ValueError("Un id d'utilisateur valide est requis");
        }
        
        $sql = "INSERT INTO forum (id_utilisateur, titre, article, date_publication) VALUES (?, ?, ?, ?);";
        
        $connection = DatabaseConnection::get_database_connection();
        
        $forum->id_utilisateur = intval(filter_var($forum->id_utilisateur, FILTER_SANITIZE_NUMBER_INT));
        $forum->titre = mysqli_real_escape_string($connection, filter_var($forum->titre, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $forum->article = mysqli_real_escape_string($connection, filter_var($forum->article, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $forum->date_publication = mysqli_real_escape_string($connection, filter_var($forum->date_publication, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        
        mysqli_begin_transaction($connection);
        
        $statement = mysqli_prepare($connection, $sql);
        
        mysqli_stmt_bind_param($statement, "isss", $forum->id_utilisateur, $forum->titre, $forum->article, $forum->date_publication);
        mysqli_stmt_execute($statement);
        
        mysqli_commit($connection);
        
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($sql, $connection, $statement);
    }
    
    public static function update_forum(Forum $forum): void
    {
        if (mb_strlen($forum->titre) < 5 || mb_strlen($forum->titre) > 100)
        {
            throw new LengthException("Le titre de l'article doit contenir entre 5 et 100 caractères");
        }
    
        if (mb_strlen($forum->article) > 1000)
        {
            throw new LengthException("Le contenu de l'article ne doit pas contenir plus de 1000 caractères");
        }
    
        if (!isset($forum->id_utilisateur) || !filter_var($forum->id_utilisateur, FILTER_VALIDATE_INT))
        {
            throw new ValueError("Un id d'utilisateur valide est requis");
        }
    
        if (!isset($forum->id) || !filter_var($forum->id, FILTER_VALIDATE_INT))
        {
            throw new ValueError("Un id d'article valide est requis");
        }
        
        $sql = "UPDATE forum SET id_utilisateur = ?, titre = ?, article = ?, date_publication = ? WHERE id = ?;";
    
        $connection = DatabaseConnection::get_database_connection();
    
        $forum->id = intval(filter_var($forum->id, FILTER_SANITIZE_NUMBER_INT));
        $forum->id_utilisateur = intval(filter_var($forum->id_utilisateur, FILTER_SANITIZE_NUMBER_INT));
        $forum->titre = mysqli_real_escape_string($connection, filter_var($forum->titre, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $forum->article = mysqli_real_escape_string($connection, filter_var($forum->article, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $forum->date_publication = mysqli_real_escape_string($connection, filter_var($forum->date_publication, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
        mysqli_begin_transaction($connection);
    
        $statement = mysqli_prepare($connection, $sql);
    
        mysqli_stmt_bind_param($statement, "isssi", $forum->id_utilisateur, $forum->titre, $forum->article, $forum->date_publication, $forum->id);
        mysqli_stmt_execute($statement);
    
        mysqli_commit($connection);
    
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($sql, $connection, $statement);
    }
    
    public static function delete_forum(int $id): void
    {
        $sql = "DELETE FROM forum WHERE id = ?;";
        
        $connection = DatabaseConnection::get_database_connection();
    
        mysqli_begin_transaction($connection);
    
        $statement = mysqli_prepare($connection, $sql);
    
        mysqli_stmt_bind_param($statement, "i", $id);
        mysqli_stmt_execute($statement);
    
        mysqli_commit($connection);
    
        mysqli_stmt_close($statement);
        mysqli_close($connection);
    
        unset($sql, $connection, $statement);
    }
}