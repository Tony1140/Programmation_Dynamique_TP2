<?php

final class User
{
    public $id;
    public $nom;
    public $nom_utilisateur;
    public $mot_de_passe;
    public $date_naissance;
    
    public static function from_mysqli_row(array $row): User
    {
        $user = new User;
    
        $user->id = intval($row["id"]);
        $user->nom = $row["nom"];
        $user->nom_utilisateur = $row["nom_utilisateur"];
        $user->mot_de_passe = $row["mot_de_passe"];
        $user->date_naissance = $row["date_naissance"];
    
        return $user;
    }
}