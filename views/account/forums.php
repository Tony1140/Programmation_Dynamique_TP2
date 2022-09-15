<?php
    require_once("lib/core.php");
    require_once("config/config.php");
    require_once("controllers/ForumsController.php");
    require_once("controllers/UsersController.php");
    require_once(AppConfig::MODEL_DIR . "/Forum.php");
    require_once(AppConfig::MODEL_DIR . "/User.php");
    
    if (!is_authenticated())
    {
        header("Location: index.php");
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && filter_var($_REQUEST["handler"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) == "edit")
    {
        array_walk_recursive($_POST, "trim_array");
        
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        $forumId = null;
        $forumTitle = null;
        $forumArticle = null;
        
        $forumValidationMessage = null;
        $forumTitleValidationMessage = null;
        $forumArticleValidationMessage = null;
        
        if (isset($_GET["id"]))
        {
            if (empty($_GET["id"]) || !filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT))
            {
                $forumValidationMessage = "Une erreur est survenue. Veuillez réessayer plus tard";
            }
            else
            {
                $forumId = intval(filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT));
            }
        }
        
        if (isset($_POST["forum-title"]))
        {
            if (empty($_POST["forum-title"]))
            {
                $forumTitleValidationMessage = "Un titre pour l'article est requis";
                
                unset($forumId);
            }
            else if (mb_strlen($_POST["forum-title"]) < 2 || mb_strlen($_POST["forum-title"]) > 25)
            {
                $forumTitleValidationMessage = "Le titre de l'article doit contenir entre 2 et 25 caractères";
                
                unset($forumId);
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
                $forumArticleValidationMessage = "Un message pour l'article est requis";
                
                unset($forumId, $forumTitle);
            }
            else if (mb_strlen($_POST["forum-article"]) > 1000)
            {
                $forumArticleValidationMessage = "Le contenu de l'article ne doit pas dépasser 1000 caractères";
                
                unset($forumId, $forumTitle);
            }
            else
            {
                $forumArticle = filter_input(INPUT_POST, "forum-article", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            
            unset($_POST["forum-article"]);
        }
        
        if (isset($forumId) && !is_int($forumId))
        {
            $forumId = intval($forumId);
        }
        
        $forum = null;
        
        if (isset($forumId))
        {
            $forum = ForumsController::get_forum($forumId);
        }
        
        if (isset($forum))
        {
            if (isset($forumTitle, $forumArticle))
            {
                $forum->titre = $forumTitle;
                $forum->article = $forumArticle;
            }
    
            ForumsController::update_forum($forum);
            
            unset($forumId, $forumTitle, $forumArticle, $forum);
            
            header("refresh: 0");
        }
    }
    else if ($_SERVER["REQUEST_METHOD"] == "POST" && filter_var($_REQUEST["handler"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) == "delete")
    {
        if (isset($_GET["id"]))
        {
            if (empty($_GET["id"]) || !filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT))
            {
                $forumValidationMessage = "Une erreur est survenue. Veuillez réessayer plus tard";
            }
            else
            {
                ForumsController::delete_forum(intval(filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT)));
            }
            
            header("refresh: 0");
        }
    }
    
    $userId = intval(filter_var($_SESSION["userId"], FILTER_SANITIZE_NUMBER_INT));
    $forums = ForumsController::get_all_forums_from_user($userId);
?>

<?php if (isset($forumValidationMessage)) { ?>
    <h4 class="text-center text-danger my-5"><?php echo($forumValidationMessage); ?></h4>
<?php } ?>

<?php if (count($forums) <= 0) { ?>
    <h3 class="text-center mt-5">Vous n'avez encore publié aucun article</h3>
<?php } else {
    foreach ($forums as $forum) {
?>
        <?php
            $containerClass = "container mx-auto border border-3 p-4 mt-5 w-50";
    
            $indexOfCurrentForum = array_search($forum, $forums);
            
            if ($indexOfCurrentForum == array_key_last($forums))
            {
                $containerClass = $containerClass . " mb-5";
            }
            
            unset($indexOfCurrentForum);
        ?>
        <div class="<?php echo($containerClass); unset($containerClass); ?>">
            <form id="delete-form" method="post" action="<?php echo(htmlspecialchars($_SERVER["PHP_SELF"] . "?module=account&action=forums&handler=delete&id=" . $forum->id)); ?>"></form>
            <form id="edit-form" method="post" action="<?php echo(htmlspecialchars($_SERVER["PHP_SELF"] . "?module=account&action=forums&handler=edit&id=" . $forum->id)); ?>">
                <div>
                    <input class="form-control" type="text" name="forum-title" minlength="5" maxlength="100" value="<?php echo($forum->titre); ?>">
                    <span class="form-text text-danger"><?php if(isset($forumTitleValidationMessage)) echo($forumTitleValidationMessage); ?></span>
                </div>
                <div class="my-4">
                    <textarea class="form-control" name="forum-article" maxlength="1000"><?php echo($forum->article); ?></textarea>
                    <span class="form-text text-danger"><?php if(isset($forumArticleValidationMessage)) echo($forumArticleValidationMessage); ?></span>
                </div>
                <div>
                    <p class="text-end"><?php echo("Date de publication: " . $forum->date_publication); ?></p>
                </div>
            </form>
            <div class="row mt-4">
                <div class="col-2 me-auto text-start">
                    <button class="btn btn-secondary" type="submit" form="edit-form">Modifier</button>
                </div>
                <div class="col-2 ms-auto text-end">
                    <button class="btn btn-danger" type="submit" form="delete-form">Supprimer</button>
                </div>
            </div>
        </div>
<?php } } ?>

<?php unset($forumTitleValidationMessage, $forumArticleValidationMessage, $forumValidationMessage, $forums); ?>