<?php
    require_once("lib/core.php");
    
    $fullName = null;
    $emailAddress = null;
    $birthDate = null;
    $password = null;
    $confirmPassword = null;
    
    $fullNameValidationMessage = null;
    $emailAddressValidationMessage = null;
    $birthDateValidationMessage = null;
    $passwordValidationMessage = null;
    $confirmPasswordValidationMessage = null;
    
    $registerMessage = null;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        array_walk_recursive($_POST, "trim_array");
        
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if (isset($_POST["full-name"]))
        {
            if (empty($_POST["full-name"]))
            {
                $fullNameValidationMessage = "Votre nom complet est requis";
            }
            else
            {
                $fullName = filter_input(INPUT_POST, "full-name", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                
                if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 25)
                {
                    $fullNameValidationMessage = "Votre nom doit contenir entre 2 et 25 caractères";
                    
                    unset($fullName);
                }
            }
    
            unset($_POST["full-name"]);
        }
        
        if (isset($_POST["email"]))
        {
            if (empty($_POST["email"]))
            {
                $emailAddressValidationMessage = "Une adresse courriel est requise";
                
                unset($fullName);
            }
            else if (!filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL))
            {
                $emailAddressValidationMessage = "Vous devez entrer une adresse courriel valide";
                
                unset($fullName);
            }
            else
            {
                $emailAddress = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
            }
            
            unset($_POST["email"]);
        }
        
        if (isset($_POST["birth-date"]))
        {
            if (empty($_POST["birth-date"]))
            {
                $birthDateValidationMessage = "Votre date de naissance est requise";
                
                unset($fullName, $emailAddress);
            }
            else
            {
                $birthDate = filter_input(INPUT_POST, "birth-date", FILTER_SANITIZE_NUMBER_INT);
            }
            
            unset($_POST["birth-date"]);
        }
        
        if (isset($_POST["new-passwd"]))
        {
            if (empty($_POST["new-passwd"]))
            {
                $passwordValidationMessage = "Un mot de passe est requis";
                
                unset($fullName, $emailAddress, $birthDate);
            }
            else
            {
                $password = filter_input(INPUT_POST, "new-passwd", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                
                if (mb_strlen($password) < 6 || mb_strlen($password) > 20)
                {
                    $passwordValidationMessage = "Le mot de passe doit contenir entre 6 et 20 caractères";
                    
                    unset($fullName, $emailAddress, $birthDate, $password);
                }
            }
            
            unset($_POST["new-passwd"]);
        }
        
        if (isset($_POST["confirm-passwd"]))
        {
            if (empty($_POST["confirm-passwd"]))
            {
                $confirmPasswordValidationMessage = "Vous devez confirmer votre mot de passe";
                
                unset($fullName, $emailAddress, $birthDate, $password);
            }
            else
            {
                $confirmPassword = filter_input(INPUT_POST, "confirm-passwd", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                
                if (!isset($password) || $confirmPassword !== $password)
                {
                    $confirmPasswordValidationMessage = "Les mots de passe ne correspondent pas";
                    
                    unset($fullName, $emailAddress, $birthDate, $password, $confirmPassword);
                }
            }
            
            unset($_POST["confirm-passwd"]);
        }
    }
    
    if (isset($fullName, $emailAddress, $birthDate, $password, $confirmPassword))
    {
        require_once("config/config.php");
        require_once(AppConfig::MODEL_DIR . "/User.php");
        require_once("controllers/UsersController.php");
        
        $newUser = new User;
        
        $newUser->nom = $fullName;
        $newUser->nom_utilisateur = $emailAddress;
        $newUser->date_naissance = $birthDate;
        $newUser->mot_de_passe = $password;
        
        try
        {
            UsersController::create_user($newUser);
            
            $registerMessage = "Votre compte a été crée avec succès";
        }
        catch (Exception $exception)
        {
            $registerMessage = "Une erreur est survenue. Veuillez réessayer plus tard";
            
            unset($exception);
        }
        finally
        {
            unset($fullName, $emailAddress, $birthDate, $password, $confirmPassword, $newUser);
        }
    }
?>

<div class="container mx-auto w-50 mt-5">
    <form method="post" action="<?php echo(htmlspecialchars($_SERVER["PHP_SELF"] . "?module=account&action=register")); ?>">
        <div>
            <h5 class="text-center my-5"><?php if (isset($registerMessage)) echo($registerMessage); ?></h5>
        </div>
        <div>
            <label class="form-label" for="full-name">Nom complet</label>
            <input class="form-control" type="text" id="full-name" name="full-name" autocomplete="name" required>
            <span class="form-text text-danger"><?php echo($fullNameValidationMessage); ?></span>
        </div>
        <div class="my-4">
            <label class="form-label" for="email">Nom d'utilisateur (adresse courriel)</label>
            <input class="form-control" type="email" id="email" name="email" autocomplete="email" required>
            <span class="form-text text-danger"><?php echo($emailAddressValidationMessage); ?></span>
        </div>
        <div>
            <label class="form-label" for="birth-date">Date de naissance</label>
            <input class="form-control" type="date" id="birth-date" name="birth-date" autocomplete="bday" required>
            <span class="form-text text-danger"><?php echo($birthDateValidationMessage); ?></span>
        </div>
        <div class="my-4">
            <label class="form-label" for="new-passwd">Mot de passe</label>
            <input class="form-control" type="password" id="new-passwd" name="new-passwd" autocomplete="new-password" required>
            <span class="form-text text-danger"><?php echo($passwordValidationMessage); ?></span>
        </div>
        <div>
            <label class="form-label" for="confirm-passwd">Confirmer le mot de passe</label>
            <input class="form-control" type="password" id="confirm-passwd" name="confirm-passwd" autocomplete="new-password" required>
            <span class="form-text text-danger"><?php echo($confirmPasswordValidationMessage); ?></span>
        </div>
        <div class="mt-4 text-center">
            <button class="btn btn-primary w-25" type="submit">S'inscrire</button>
        </div>
    </form>
</div>

<?php unset($registerMessage, $fullNameValidationMessage, $emailAddressValidationMessage, $birthDateValidationMessage, $passwordValidationMessage, $confirmPasswordValidationMessage); ?>