<?php

final class Forum
{
    public $id;
    public $id_utilisateur;
    public $titre;
    public $article;
    public $date_publication;
    
    public static function from_mysqli_row(array $row): Forum
    {
        $forum = new Forum;
        
        $forum->id = $row["id"];
        $forum->id_utilisateur = $row["id_utilisateur"];
        $forum->titre = $row["titre"];
        $forum->article = $row["article"];
        $forum->date_publication = $row["date_publication"];
        
        return $forum;
    }
}