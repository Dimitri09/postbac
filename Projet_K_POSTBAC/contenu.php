<?php
	session_start();
	require_once('debut.php');
	require_once('menu.php');
	require_once('connexion.php');
	require_once('fonctions.php');
	//require_once('Integration.php');	


?>




<!--<h2>Formulaire d'ajout d'enseignants</h2>-->
<br>
<center><p id='textAccueil'> <strong>Cet espace permet la gestion des différents enseignants qui traiteront les dossiers des candidats</strong></p></center> 

<?php

if ($_SESSION['admin']==1){

 echo '<center><div style="margin-left: auto; margin-right: auto; width: 20%; ">
 		<a href="FormulaireProf.php">
 			<button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary">Ajouter Enseignant</button>
 		</a>
 	</div></center>';

}

?>



<?php

echo "<center>";
afficheProf($bd);
echo "</center>";

?> 







<?php
require_once('fin.php');
?>
