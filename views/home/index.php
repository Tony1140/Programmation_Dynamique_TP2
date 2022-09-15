<?php
    require_once("config/config.php");
    require_once("lib/core.php");
    require_once("controllers/UsersController.php");
    require_once("controllers/ForumsController.php");
    require_once(AppConfig::MODEL_DIR . "/User.php");
    require_once(AppConfig::MODEL_DIR . "/Forum.php");
    
    $globalMessage = null;
    
    $forums = [];
    
    try
    {
        $forums = ForumsController::get_all_forums();
    }
    finally
    {
        if (count($forums) <= 0)
        {
            $globalMessage = "Il n'y a aucun article à afficher";
        }
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && is_authenticated())
    {
        array_walk_recursive($_POST, "trim_array");
        
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        $forumTitle = null;
        $forumArticle = null;
    
        $forumTitleValidationMessage = null;
        $forumArticleValidationMessage = null;
    
        if (isset($_POST["forum-title"]))
        {
            if (empty($_POST["forum-title"]))
            {
                $forumTitleValidationMessage = "Vous devez entrer un titre pour l'article";
            }
            else if (mb_strlen($_POST["forum-title"]) < 5 || mb_strlen($_POST["forum-title"]) > 100)
            {
                $forumTitleValidationMessage = "Le titre de l'article doit contenir entre 5 et 100 caractères";
            }
            else
            {
                $forumTitle = filter_input(INPUT_POST, "forum-title", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            
            unset($_POST["forum-title"]);
        }
    
        if (isset($_POST["forum-article"]))
        {
            if (empty($_POST["forum-article"]))
            {
                $forumArticleValidationMessage = "Vous devez entrer un contenu pour l'article";
                
                unset($forumTitle);
            }
            else if (mb_strlen($_POST["forum-article"]) > 1000)
            {
                $forumArticleValidationMessage = "Le contenu de l'article ne doit pas contenir plus de 1000 caractères";
                
                unset($forumTitle);
            }
            else
            {
                $forumArticle = filter_input(INPUT_POST, "forum-article", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            
            unset($_POST["forum-article"]);
        }
    
        if (isset($forumTitle, $forumArticle))
        {
            $forum = new Forum;
        
            $forum->id_utilisateur = filter_var($_SESSION["userId"], FILTER_SANITIZE_NUMBER_INT);
            $forum->titre = $forumTitle;
            $forum->article = $forumArticle;
            $forum->date_publication = (new DateTime())->format("Y-m-d");
        
            try
            {
                ForumsController::create_forum($forum);
                
                header("refresh: 0");
            }
            finally
            {
                unset($forum, $forumTitle, $forumArticle);
            }
        }
    }
?>

<?php if (is_authenticated()) { ?>
    <div class="container mx-auto w-50 mt-4">
        <form method="post" action="<?php echo(htmlspecialchars($_SERVER["PHP_SELF"] . "?module=home&action=index")); ?>">
            <div>
                <input class="form-control" type="text" id="forum-title" name="forum-title" placeholder="Titre" minlength="5" maxlength="100" required>
                <span class="form-text text-danger"><?php if (isset($forumTitleValidationMessage)) echo($forumTitleValidationMessage); ?></span>
            </div>
            <div class="my-3">
                <textarea class="form-control" id="forum-article" name="forum-article" maxlength="1000" placeholder="Article" rows="8" cols="15" required></textarea>
                <span class="form-text text-danger"><?php if (isset($forumArticleValidationMessage)) echo($forumArticleValidationMessage); ?></span>
            </div>
            <div class="text-center">
                <button class="btn btn-secondary w-25" type="submit">Publier</button>
            </div>
        </form>
    </div>
<?php } ?>

<?php if (isset($globalMessage)) { ?>
    <h3 class="text-center mt-5"><?php echo($globalMessage); ?></h3>
<?php } ?>

<?php if (count($forums) > 0) { ?>
    <?php foreach ($forums as $forum) { ?>
        <?php
            $cardClass = "card w-50 mx-auto mt-5";
            
            $indexOfCurrentForum = array_search($forum, $forums);
            
            if ($indexOfCurrentForum == array_key_last($forums))
            {
                $cardClass = $cardClass . " mb-5";
            }
            
            unset($indexOfCurrentForum);
        ?>
        <div class="<?php echo($cardClass); unset($cardClass); ?>">
            <h5 class="card-header text-center"><?php echo($forum->titre); ?></h5>
            <div class="card-body text-center">
                <p class="card-text"><?php echo($forum->article); ?></p>
            </div>
            <div class="card-footer text-end"><?php echo("Date de publication: " . $forum->date_publication); ?></div>
        </div>
    <?php } ?>
<?php } ?>

<?php unset($globalMessage, $forumTitleValidationMessage, $forumArticleValidationMessage, $forums); ?>