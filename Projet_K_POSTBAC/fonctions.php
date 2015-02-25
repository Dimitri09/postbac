<?php

require('connexion.php'); //Permet la connexion à la base de données
//header ('Content-type: text/html; charset=UTF-8');
ini_set('display_errors', 1); 
error_reporting(E_ALL); //Affiche toutes les erreurs









//---------------------------------------------------------------------------------//
//       Fonction pour l'intégration du fichier excel (converti en CSV)            //
//---------------------------------------------------------------------------------//											

//Fonction qui récupère un fichier CSV sous forme d'array 
function get_array($fileCsv) {

    if (($file = fopen($fileCsv, 'r')) !== FALSE){

      while (($line = fgetcsv($file,'',';')) !== FALSE) {

        $array_data[] = $line;

        }

    }

  fclose($file);

  return $array_data;

}

//Fonction qui prépare la requête CREATE TABLE avec la premiere ligne du fichier csv (titre) comme champs
//ATTENTION: IL FAUT QUE LES CHAMPS DE LA PREMIERE LIGNE DU FICHIER SOIENT CONCATENE 
function createTable($nomTable, $array_file, $primaryCles1){

	$req= 'CREATE TABLE if not exists '.$nomTable.' ( ';

	$i=0; 

	while ($i<sizeof($array_file[0])){

		if (is_numeric(str_replace ( ",", ".", $array_file[1][$i])))
		{
			if ($array_file[0][$i]=='Moyenne') // On force la colonne Moyenne a être un float
			{
				$req=$req.$array_file[0][$i].' FLOAT, ';
			}
			else
			{	
				$req=$req.$array_file[0][$i].' INTEGER, ';
			}
		}
	
		elseif(is_string(str_replace ( ",", ".", $array_file[1][$i])))
		{
			$req=$req.str_replace ( ",", ".", $array_file[0][$i]).' VARCHAR(50), ';
		}
		
		$i++;

	}

	//Définition des clés primaires 

	$req=$req.'PRIMARY KEY ('.$primaryCles1.'))';

	return $req;
}

// fonction qui prépare la requête d'insertion des fichier csv 
function prepareInsert($array_file, $nomTable, $line){

	if ($line>(count($array_file)-1))
	{
		return 'Erreur $line: cette ligne n\'existe pas';
	}

	$insert= 'INSERT INTO '.$nomTable.' VALUES( ';

	for($y=0; $y<count($array_file[0]);$y++)
	{
		if (is_numeric(str_replace ( ",", ".", $array_file[$line][$y]))==FALSE)
		{
			if($y==(count($array_file[0])-1))
			{
				if ($array_file[$line][$y]==NULL)
				{
					$insert=$insert.'NULL)';
				}
				else
				{
					if (strstr($array_file[$line][$y], "\"")) 
					{
						$chaine=str_replace ( "\"", " ' ", $array_file[$line][$y]);

						$insert=$insert."\"".$chaine."\"".' ) ';
					} 
					else
					{
						$insert=$insert."\"".$array_file[$line][$y]."\"".' ) ';			
					}
				}
			}
			else
			{
				if ($array_file[$line][$y]==NULL)
				{
					$insert=$insert.'NULL, ';
				}
				else
				{
					if (strstr($array_file[$line][$y], "\"")) 
					{
						$chaine=str_replace ( "\"", " ' ", $array_file[$line][$y]);

						$insert=$insert."\"".$chaine."\"".' , ';
					}
					else
					{
						$insert=$insert."\"".$array_file[$line][$y]."\"".', ';
					}
				}	
			}
		}
		else
		{
			if($y==(count($array_file[0])-1))
			{
				$insert=$insert.str_replace ( ",", ".", $array_file[$line][$y]).') ';
			}
			else
			{
				$insert=$insert.str_replace ( ",", ".", $array_file[$line][$y]).', ';
			}	
		}	
	}

	return $insert;
}

//Fonction qui insert toutes les lignes du fichier csv dans la base
function insert($bd, $nomTable, $array_file){

	for ($i=1;$i<count($array_file);$i++)
	{
		$insert=prepareInsert($array_file, $nomTable, $i);	

		$reqInsert = $bd->prepare($insert);

		$reqInsert->execute();
	}

	return TRUE;
}

//----------------------------------------------------------------------------------//
//       Fonction pour la créaction, la gestion et l'affichage des enseignants     //
//--------------------------------------------------------------------------------//	

//Creation de la table des enseignants 
function createTableID($bd){

	$req=$bd->prepare(' CREATE TABLE IF NOT EXISTS  identification  (
 `id` INTEGER ,
 `nom` VARCHAR( 50 ) NOT NULL ,
 `prenom` VARCHAR( 50 ) NOT NULL ,
  `email` VARCHAR( 50 ) NOT NULL ,
  `mdp` VARCHAR( 50 ) NOT NULL ,
 `matiere` VARCHAR( 50 ) NOT NULL ,
 `admin` INTEGER NOT NULL,
  PRIMARY KEY (id))');
	$req->execute();
}

//Nombre d'enseignant enregister dans la base 
function nbEnseignant($bd){
	
	$query='SELECT COUNT( * ) FROM identification';
	$req=$bd->prepare($query);
	
	if($req->execute()){

		$rep = $req->fetch(PDO::FETCH_NUM);
		return $rep[0];

	} 
}

//Insertion des données de l'enseignant a partir du formulaire 
function insertDataEnseignants($bd){

	$nb=nbEnseignant($bd);

    if(isset($_GET['nom']) && trim($_GET['nom']!=NULL) && isset($_GET['prenom']) && trim($_GET['prenom']!=NULL) && isset($_GET['matiere']) && trim($_GET['matiere']!=NULL) && isset($_GET['email']) && trim($_GET['email']))
    {
        $query='INSERT INTO identification VALUE ( :id, :nom, :prenom, :email ,:mdp, :matiere, 0)';
        $req=$bd->prepare($query);
        $req->bindValue('id', $nb);
        $req->bindValue(':nom', $_GET['nom']);
        $req->bindValue(':prenom', $_GET['prenom']);

        $req->bindValue(':email', $_GET['email']);

        $req->bindValue(':mdp', 'bonjour');
        $req->bindValue(':matiere', $_GET['matiere']);
       
        if($req->execute())
        {
        	//mail( $_GET['email'], 'Identifiant et Mot de passe PostBac', 'le message', null, 'karine.ouldbraham@gmail.com');
        	echo '<div style="margin-left: auto; margin-right: auto; width: 28%; "><p style="color:red;"><strong>'. $_GET['nom'] .' '. $_GET['prenom'] .' à été enregistré !</strong></p></div>';
        };
    }
}

// Récupération des enseignant dans la table et affichage du tableau 
function afficheProf($bd){

	$query="SELECT * FROM identification";
	$req=$bd->prepare($query);
	$req->execute();

	$rep = $req->fetch(PDO::FETCH_ASSOC);
	
	//Affiche le tableau des profs en fonction de si l'utilisateur est administrateur 
	if ($_SESSION['admin']==1){

		if (empty($rep))
		{
			echo '
			<table class="pure-table-horizontal" border="1" CELLPADDING="15" style="margin-left: 19%; margin-top: 3%; width: 57%;">
			<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
			<tr>
			<th>Nom</th>
			<th>Prénom</th>
			<th>Matière</th>
			</tr>';
		}
		else
		{
			$tmp= array_keys($rep);

			echo '
			<table class="pure-table" border="1" CELLPADDING="15" style="margin-left: 19%; margin-top: 3%; width: 57%;">
			<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
			<tr class="pure-table-odd">
			<th>Nom</th>
			<th>Prénom</th>
			<th>Matière</th>
			<th>Modifier</th>
			<th>Supprimer</th>
			</tr>';

			while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
			{
				echo '
				<tr>
				<td style="text-align:center;">'.$tmp1['nom'].'</td>
				<td style="text-align:center;">'.$tmp1['prenom'].'</td>
				<td style="text-align:center;">'.$tmp1['matiere'].'</td>
				<td style="text-align:center;"><a href="modifProf.php"><i style="padding-left:2em;" class="fa fa-file-o"></i></a></td>
				<td style="text-align:center;"><a href="supprimeProf.php"><i style="padding-left:2em;" class="fa fa-trash-o"></i></a></td>
				</tr>';

			}
			echo '</table>';
		}

	}
	else 
	{
		if (empty($rep))
		{
			echo '
			<table class="pure-table-horizontal" border="1" CELLPADDING="15" style="margin-left: 19%; margin-top: 3%; width: 57%;">
			<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
			<tr>
			<th>Nom</th>
			<th>Prénom</th>
			<th>Matière</th>
			</tr>';
		}
		else
		{
		
			$tmp= array_keys($rep);

			echo '
			<table class="pure-table" border="1" CELLPADDING="15" style="margin-left: 19%; margin-top: 3%; width: 57%;">
			<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
			<tr class="pure-table-odd">
			<th>Nom</th>
			<th>Prénom</th>
			<th>Matière</th>
			</tr>';

			while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
			{

				echo '
				<tr>
				<td style="text-align:center;">'.$tmp1['id'].'</td>
				<td style="text-align:center;">'.$tmp1['prenom'].'</td>
				<td style="text-align:center;">'.$tmp1['matiere'].'</td>
				</tr>';
			}
			echo '</table>';
		}
	}
}

function afficheEleve($f,$bd)//Affiche les eleves en fonction de $f (les boutons Alternance/initiale au-dessus de la liste )
{							 // avec une checkbox avec comme valeur le num de l'eleve
	if ($_SESSION['admin']==1){
		
		echo '<div style="margin-left: auto; margin-right: auto; width: 19%; padding-top:2em;">';
		echo '<form action="dossierATraiter.php" method = "post">
		<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" value="Attribuer"/>';
		echo '</div>';

		echo '<table class="pure-table-horizontal" border="1" CELLPADDING="15" style="margin-left: 5%; margin-top: 3%; width: 57%;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr><th>Nom</th><th>Prénom</th><th>Numero</th><th>Bac</th><th>Moyenne</th><th>BonusMalus</th><th>AvisCE</th><th>Selectionner</th></tr> ';

		if ($f == 'fi')
		{
			$req = $bd->prepare('select * from AtraiterFI');
			$req->execute();
		}
		else
		{
			$req = $bd->prepare('select * from AtraiterFA');
			$req->execute();
			
		}

		
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
			<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
			'</td><td><input type="checkbox" name="selection[]" value="'.$rep['Numero'].'"/></td></tr>';
		}
		

	}
	else{

		echo '<div style="margin-left: auto; margin-right: auto; width: 19%; padding-top:2em;"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-disabled">Attribuer</button></div>';

		echo '<table class="pure-table-horizontal" border="1" CELLPADDING="15" style="margin-left: 10%; margin-top: 3%; width: 57%;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr><th>Nom</th> <th>Prénom</th><th>Numero</th><th>Bac</th><th>Moyenne</th><th>BonusMalus</th><th>AvisCE</th>';
 		echo '</div>';

		if ($f == 'fi')
		{
			$req = $bd->prepare('select * from AtraiterFI');
			$req->execute();
		}
		else
		{
			$req = $bd->prepare('select * from AtraiterFA');
			$req->execute();
			
		}
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
			<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
			'</td></tr>';
		}
	}

}

//Création des tables des etudiant FA et FI avec leurs moyennes
function tableEtudiantAvecMoyenne($bd){

	/////////////////////CAS FA/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('CREATE TABLE IF NOT EXISTS EtudiantFA AS 
	
			SELECT  MoyenneFA.RangProvisoire, MoyenneFA.Numero, MoyenneFA.Nom, MoyenneFA.Prénom, MoyenneFA.InfosDiplôme, 
			
			MoyenneFA.Moyenne, MoyenneFA.NombreDeBonusMalusAppliqués, fichierFA.AvisDuCE

			FROM MoyenneFA, fichierFA WHERE MoyenneFA.Numero = fichierFA.Numero ORDER BY Moyenne DESC ');
	$req->execute();

	$req = $bd->prepare('ALTER TABLE EtudiantFA ADD PRIMARY KEY (Numero)');
	$req->execute();
	echo 'EtudiantFA TABLE CREATE';

	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('CREATE TABLE IF NOT EXISTS EtudiantFI AS 
	
			SELECT  MoyenneFI.RangProvisoire, MoyenneFI.Numero, MoyenneFI.Nom, MoyenneFI.Prénom, MoyenneFI.InfosDiplôme, 
			
			MoyenneFI.Moyenne, MoyenneFI.NombreDeBonusMalusAppliqués, fichierFI.AvisDuCE

			FROM MoyenneFI, fichierFI WHERE MoyenneFI.Numero = fichierFI.Numero ORDER BY Moyenne DESC ');
	$req->execute();

	$req = $bd->prepare('ALTER TABLE EtudiantFI ADD PRIMARY KEY (Numero)');
	$req->execute();
	echo 'EtudiantFI TABLE CREATE';

}

//On créer des vues sur le élèves sélectionné au premier tour
function eleveSelectionner($bd){
	/////////////////////CAS FA/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('Select count(*) from EtudiantFA');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4
	
	$req = $bd->prepare('CREATE VIEW SelectionneFA (RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE)
						AS SELECT 
						RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFA ORDER BY Moyenne DESC LIMIT '.$calcul);//on insere ds la nouvelle table les eleves du 1° quart qui sont admis d'office
	$req->execute();
	echo 'SelectionneFA VIEW CREATE';
	
	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////
	
	$req = $bd->prepare('Select count(*) from EtudiantFI');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4

	$req = $bd->prepare('CREATE VIEW SelectionneFI (RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE)
						AS SELECT 
						RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFI ORDER BY Moyenne DESC LIMIT '.$calcul);//on insere ds la nouvelle table les eleves du 1° quart qui sont admis d'office
	$req->execute();
	echo 'SelectionneFI VIEW CREATE';

}

//Génére les differentes tables et vues pour le traitement des élèves
function eleveATraiter($bd)
{	
	
	/////////////////////CAS FA/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('Select count(*) from EtudiantFA');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	 $calcul = $rep[0]/4;
	 $req = $bd->prepare('CREATE TABLE IF NOT EXISTS AtraiterFA  
	 					  AS SELECT  
	 					  RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFA 
	 					  ORDER BY Moyenne DESC LIMIT :limite, :offset');

	 $req->bindValue(':limite', $calcul, PDO::PARAM_INT);
	 $calcul = $calcul*3;
	 $req->bindValue(':offset', $calcul, PDO::PARAM_INT);
	 $req->execute();
	  echo 'AtraiterFA TABLE CREATE';
	
	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////
	
	$req = $bd->prepare('Select count(*) from EtudiantFI');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4

	$req = $bd->prepare('CREATE TABLE IF NOT EXISTS AtraiterFI  
	 					  AS SELECT  
	 					  RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFI 
	 					  ORDER BY Moyenne DESC LIMIT :limite, :offset');

	 $req->bindValue(':limite', $calcul, PDO::PARAM_INT);
	 $calcul = $calcul*3;
	 $req->bindValue(':offset', $calcul, PDO::PARAM_INT);
	 $req->execute();
	 echo 'AtraiterFI TABLE CREATE';
	
}

//On créer une vue pour les élèves qui ont postulé dans les deux filieres (438)
function elevePostuleFAFI($bd)
{
	$req = $bd->prepare('CREATE VIEW EtudiantFIFA (Numero, Nom, Prénom, Moyenne, InfosDiplôme, NombreDeBonusMalusAppliqués, AvisDuCE) 
						AS SELECT EtudiantFI.Numero, EtudiantFI.Nom, EtudiantFI.Prénom, EtudiantFI.Moyenne, EtudiantFI.InfosDiplôme, EtudiantFI.NombreDeBonusMalusAppliqués, EtudiantFI.AvisDuCE
						FROM EtudiantFI, EtudiantFA
						WHERE EtudiantFI.Numero = EtudiantFA.Numero
						ORDER BY Moyenne DESC');
	$req->execute();
}


function bonusMalusTotal($bd, $ine, $bn) //applique les bonus/malus aux eleves ON A BESOIN DU FORMULAIRE BORDEL DE MERDE!!!!!!
{
	$req = $bd->prepare('UPDATE m SET Moyenne = Moyenne+ :bn  WHERE Numero = :ine');//On icrémente la moyenne de l'eleve de la valeur de bn
	$req->bindValue(':bn', $bn);
	$req->bindValue(':ine', $ine);
	$req->execute();
}

/*liste des fonction



->fonctions des malus automatiques

->fonctions des bonus automatiques



->Affiche touts les etudiants


*/





?>
