<?php


    require_once('debut.php');
    require_once('menu.php');
    require_once('connexion.php');
    require_once('fonctions.php');




?>
<p id='textAccueil'> <strong>Cet espace permet la gestion des différents enseignants qui traiteront les dossiers des candidats</strong></p> 

<?php
insertDataEnseignants($bd);
?>

<div style="margin-left: auto; margin-right: auto; width: 30%; padding-top:3em;"><a href="formulaireProf.php"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary">Ajouter Enseignant</button></a></div>

<div style="margin-left: auto; margin-right: auto; width: 31%; padding-top:3em;"><a href="contenu.php"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary">Liste des Enseignants</button></a></div>



