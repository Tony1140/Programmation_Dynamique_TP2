<?php

require_once("config/config.php");
require_once(AppConfig::CONNEX_DIR);
require_once(AppConfig::MODEL_DIR . "/User.php");

final class UsersController
{
    public static function get_all_users(): array
    {
        $sql = "SELECT * FROM utilisateur ORDER BY nom;";
        
        $connection = DatabaseConnection::get_database_connection();
        
        $result = mysqli_query($connection, $sql);
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        $users = [];
        
        foreach ($result as $queryResult)
        {
            $user = User::from_mysqli_row($queryResult);
            
            $users[] = $user;
            
            unset($queryResult);
        }
        
        mysqli_close($connection);
        
        unset($sql, $connection, $result);
        
        return $users;
    }
    
    public static function get_user(int $id): ?User
    {
        $sql = "SELECT * FROM utilisateur WHERE id = ?;";
        
        $connection = DatabaseConnection::get_database_connection();
        $statement = mysqli_prepare($connection, $sql);
    
        $user = null;
        
        mysqli_stmt_bind_param($statement, "i", $id);
        mysqli_stmt_execute($statement);
        
        $result = mysqli_stmt_get_result($statement);
        $rowCount = mysqli_num_rows($result);
        
        if ($rowCount == 1)
        {
            $queryResult = mysqli_fetch_assoc($result);
            
            $user = User::from_mysqli_row($queryResult);
            
            unset($queryResult);
        }
        
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($sql, $connection, $statement, $result, $rowCount);
        
        return $user;
    }
    
    public static function create_user(User $user): void
    {
        if (mb_strlen($user->nom) < 2 || mb_strlen($user->nom) > 25)
        {
            throw new LengthException("Le nom de l'utilisateur doit contenir entre 2 et 25 caractères");
        }
        
        if (!filter_var($user->nom_utilisateur, FILTER_VALIDATE_EMAIL))
        {
            throw new InvalidArgumentException("L'adresse courriel de l'utilisateur doit être une adresse courriel valide");
        }
        
        if (mb_strlen($user->mot_de_passe) < 6 || mb_strlen($user->mot_de_passe) > 20)
        {
            throw new LengthException("Le mot de passe doit contenir entre 6 et 20 caractères");
        }
        
        $sql = "INSERT INTO utilisateur (nom, nom_utilisateur, mot_de_passe, date_naissance) VALUES (?, ?, ?, ?);";
        
        $connection = DatabaseConnection::get_database_connection();
        
        $user->nom = mysqli_real_escape_string($connection, filter_var($user->nom, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $user->nom_utilisateur = mysqli_real_escape_string($connection, filter_var($user->nom_utilisateur, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $user->mot_de_passe = password_hash(mysqli_real_escape_string($connection, filter_var($user->mot_de_passe, FILTER_SANITIZE_FULL_SPECIAL_CHARS)), PASSWORD_BCRYPT, ["cost" => 12]);
        $user->date_naissance = mysqli_real_escape_string($connection, filter_var($user->date_naissance, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        
        mysqli_begin_transaction($connection);
        
        $statement = mysqli_prepare($connection, $sql);
        
        mysqli_stmt_bind_param($statement, "ssss", $user->nom, $user->nom_utilisateur, $user->mot_de_passe, $user->date_naissance);
        mysqli_stmt_execute($statement);
        
        mysqli_commit($connection);
        
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($sql, $connection, $statement);
    }
    
    public static function update_user(User $user)
    {
        if (!isset($user->id) || !filter_var($user->id, FILTER_VALIDATE_INT))
        {
            throw new ValueError("L'id de l'utilisateur n'est pas un id valide");
        }
        
        if (mb_strlen($user->nom) < 2 || mb_strlen($user->nom) > 25)
        {
            throw new LengthException("Le nom de l'utilisateur doit contenir entre 2 et 25 caractères");
        }
    
        if (!filter_var($user->nom_utilisateur, FILTER_VALIDATE_EMAIL))
        {
            throw new InvalidArgumentException("L'adresse courriel de l'utilisateur doit être une adresse courriel valide");
        }
        
        $hashInfo = password_get_info($user->mot_de_passe);
        $passwordChanged = $hashInfo["algo"] != 0;
        
        if ($passwordChanged)
        {
            if (mb_strlen($user->mot_de_passe) < 6 || mb_strlen($user->mot_de_passe) > 20)
            {
                throw new LengthException("Le mot de passe doit contenir entre 6 et 20 caractères");
            }
        }
        
        $sql = "UPDATE utilisateur SET nom = ?, nom_utilisateur = ?, mot_de_passe = ?, date_naissance = ? WHERE id = ?;";
        
        $connection = DatabaseConnection::get_database_connection();
    
        $user->nom = mysqli_real_escape_string($connection, filter_var($user->nom, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $user->nom_utilisateur = mysqli_real_escape_string($connection, filter_var($user->nom_utilisateur, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        
        if ($passwordChanged)
        {
            $user->mot_de_passe = password_hash(mysqli_real_escape_string($connection, $user->mot_de_passe), PASSWORD_BCRYPT, ["cost" => 12]);
        }
        else
        {
            $user->mot_de_passe = mysqli_real_escape_string($connection, $user->mot_de_passe);
        }
        
        $user->date_naissance = mysqli_real_escape_string($connection, filter_var($user->date_naissance, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        
        mysqli_begin_transaction($connection);
        
        $statement = mysqli_prepare($connection, $sql);
    
        mysqli_stmt_bind_param($statement, "ssssi", $user->nom, $user->nom_utilisateur, $user->mot_de_passe, $user->date_naissance, $user->id);
        mysqli_stmt_execute($statement);
    
        mysqli_commit($connection);
    
        mysqli_stmt_close($statement);
        mysqli_close($connection);
        
        unset($hashInfo, $passwordChanged, $sql, $connection, $statement);
    }
    
    public static function delete_user(int $id)
    {
        $sql = "DELETE FROM utilisateur WHERE id = ?;";
        
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