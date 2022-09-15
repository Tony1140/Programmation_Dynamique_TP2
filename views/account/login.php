<?php
    require_once("lib/core.php");
    
    $emailAddress = null;
    $password = null;
    
    $emailAddressValidationMessage = null;
    $passwordValidationMessage = null;
    
    $loginMessage = null;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        array_walk_recursive($_POST, "trim_array");
        
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if (isset($_POST["username"]))
        {
            if (empty($_POST["username"]))
            {
                $emailAddressValidationMessage = "Votre adresse courriel est requise";
            }
            else if (!filter_input(INPUT_POST, "username", FILTER_VALIDATE_EMAIL))
            {
                $emailAddressValidationMessage = "Vous devez entrer une adresse courriel valide";
            }
            else
            {
                $emailAddress = filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
            }
    
            unset($_POST["username"]);
        }
        
        if (isset($_POST["passwd"]))
        {
            if (empty($_POST["passwd"]))
            {
                $passwordValidationMessage = "Votre mot de passe est requis";
                
                unset($emailAddress);
            }
            else
            {
                if (isset($emailAddress))
                {
                    $password = filter_input(INPUT_POST, "passwd", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                }
            }
            
            unset($_POST["passwd"]);
        }
    }
    
    
    if (isset($emailAddress, $password)) {
        require_once("config/config.php");
        require_once(AppConfig::CONNEX_DIR);
        require_once(AppConfig::MODEL_DIR . "/User.php");
        
        $sql = "SELECT * FROM utilisateur WHERE nom_utilisateur = ?;";
        
        $connection = DatabaseConnection::get_database_connection();
        
        $emailAddress = mysqli_real_escape_string($connection, $emailAddress);
        $password = mysqli_real_escape_string($connection, $password);
        
        $statement = mysqli_prepare($connection, $sql);
        
        mysqli_stmt_bind_param($statement, "s", $emailAddress);
        mysqli_stmt_execute($statement);
        
        $result = mysqli_stmt_get_result($statement);
        $rowCount = mysqli_num_rows($result);
        
        if ($rowCount == 1)
        {
            $queryResult = mysqli_fetch_assoc($result);
            
            $user = User::from_mysqli_row($queryResult);
            
            $validPassword = password_verify($password, $user->mot_de_passe);
            
            if ($validPassword)
            {
                session_regenerate_id();
                
                $_SESSION["userId"] = $user->id;
                $_SESSION["name"] = $user->nom;
                $_SESSION["fingerprint"] = hash("sha512", $_SERVER["HTTP_USER_AGENT"] . $_SERVER["REMOTE_ADDR"]);
                
                session_write_close();
                
                mysqli_stmt_close($statement);
                mysqli_close($connection);
    
                unset($sql, $connection, $statement, $result, $rowCount, $queryResult, $user, $validPassword, $emailAddress, $password);
                
                header("Location: index.php");
            }
            else
            {
                $loginMessage = "Nom d'utilisateur ou mot de passe invalide";
    
                mysqli_stmt_close($statement);
                mysqli_close($connection);
    
                unset($sql, $connection, $statement, $result, $rowCount, $queryResult, $user, $validPassword, $emailAddress, $password);
            }
        }
        else
        {
            $loginMessage = "Nom d'utilisateur ou mot de passe invalide";
    
            mysqli_stmt_close($statement);
            mysqli_close($connection);
        }
        
        unset($sql, $connection, $statement, $result, $rowCount, $emailAddress, $password);
    }

?>

<div class="container mx-auto mt-5 w-50">
    <form method="post" action="<?php echo(htmlspecialchars($_SERVER["PHP_SELF"] . "?module=account&action=login")); ?>">
        <div>
            <h5 class="text-center my-5"><?php if (isset($loginMessage)) echo($loginMessage); ?></h5>
        </div>
        <div>
            <label class="form-label" for="username">Nom d'utilisateur (adresse courriel)</label>
            <input class="form-control" type="email" id="username" name="username" autocomplete="username" required>
            <span class="form-text text-danger"><?php echo($emailAddressValidationMessage); ?></span>
        </div>
        <div class="my-4">
            <label class="form-label" for="passwd">Mot de passe</label>
            <input class="form-control" type="password" id="passwd" name="passwd" autocomplete="current-password">
            <span class="form-text text-danger"><?php echo($passwordValidationMessage); ?></span>
        </div>
        <div class="text-center">
            <button class="btn btn-primary w-25" type="submit">Se connecter</button>
        </div>
    </form>
</div>

<?php unset($loginMessage, $emailAddressValidationMessage, $passwordValidationMessage); ?>