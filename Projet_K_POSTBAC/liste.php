<?php
		
		session_start();
		ini_set('memory_limit','64M');
		/* require_once('connexion.php'); */
		require('debut.php');
		require('menu.php'); 
		require('fonctions.php');

?>


<br>
<center><p id='textAccueil'><strong>Cet espace permet l'affichage des différents candidats, veuillez selectionner mode d'affichage :</strong></p></center> 
<br>
	<center>
	<form method="GET">
			
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="affiche" value="Filière initiale"/>
		
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="affiche" value="Filière alternance"/>
			
	</form>
	</center>



<?php



if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['affiche']) && $_GET['affiche']=='Filière initiale'){

	echo "<center>";
	afficheEleve('fi',$bd); // à la place de 'fi', on mettra le $_POST du bouton, 
	echo "</center>";					//celui qui decide si on affiche les alternants ou les initiales
}
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['affiche']) && $_GET['affiche']=='Filière alternance'){
	
	echo "<center>";
	afficheEleve('fa',$bd);
	echo "</center>";

}




?>

<?php

echo '</table>';
echo '</form>';

?>


	








<?php
		require('fin.php'); 
?>
