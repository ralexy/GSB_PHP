<?php
/**
 * Validations des frais par le comptable
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @author    Alexy ROUSSEAU <contact@alexy-rousseau.com>
 * @copyright 2017-2019 Réseau CERTA
 * @license   Réseau CERTA
*  @version   GIT: <7>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

$idMembre = $_SESSION['idMembre'];
$mois = getMois(date('d/m/Y'));
$numAnnee = substr($mois, 0, 4);
$numMois = substr($mois, 4, 2);
$lesVisiteurs = $pdo->getListeVisiteurs();
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);


// A voir où le placer pour être le plus propre possible, ajoute les mois du visiteur à ses infos pour leur parcours par le comptable
for($i = 0; $i < count($lesVisiteurs); $i++) {
    $lesVisiteurs[$i]['lesMoisDisponibles'] = $pdo->getLesMoisDisponibles($lesVisiteurs[$i]['id']);
}

require 'vues/comptable/v_listeVisiteurs.php';

switch ($action) {

case 'voirFraisVisiteur':
//    require 'vues/comptable/v_listeVisiteurs.php';
    break;

case 'validerSaisieFraisVisiteur';

    // On récupère l'id du visiteur selectionné dans le <select>
    $idVisiteurSelectionne = filter_input(INPUT_POST, 'lstVisiteurs', FILTER_SANITIZE_STRING);
    $mois                  = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING);

    // On fait une recherche de la clé du tableau associatif correspondant à notre visiteur pour le sélectionner dans notre variable $leVisiteur
    $matchedKey = array_search($idVisiteurSelectionne, array_column($lesVisiteurs, 'id'));
    $leVisiteur = $lesVisiteurs[$matchedKey];

    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteurSelectionne, $mois);
    $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteurSelectionne, $mois);

    if($lesFraisForfait) {
        require 'vues/comptable/v_validerFrais.php';
        require 'vues/comptable/v_descriptifFraisHorsForfait.php';
    } else {
        require 'vues/comptable/v_fraisHorsForfaitVide.php';
    }
    break;

case 'validerMajFraisForfait':
    $lesFrais = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
    $idVisiteur = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_STRING);
    if (lesQteFraisValides($lesFrais)) {
        $pdo->majFraisForfait($idVisiteur, $mois, $lesFrais);

        //header('Location : index.php?uc=validerFrais&action=voirFraisVisiteur'); // On ne peut pas faire joujou avec les headers visiblement
    } else {
        ajouterErreur('Les valeurs des frais doivent être numériques');
        include 'vues/v_erreurs.php';
    }
    break;

    /** Todo
     *  Revoir le fonctionnement de ce "bloc" et de la vue pour faire tout ça plus proprement (sans input hidden et peut être plus intelligemment pour le if else)
     */
    case 'validerMajFraisHF':
        $idLigneHF = filter_input(INPUT_POST, 'idLigneHF', FILTER_SANITIZE_STRING);
        $valider = filter_input(INPUT_POST, 'valider', FILTER_SANITIZE_STRING) ? true : false; // On récupère l'action du formulaire : true si il accepte la ligne de frais HF, false dans le cas contraire
        $libelleHF = filter_input(INPUT_POST, 'txtLibelleHF', FILTER_SANITIZE_STRING);

        // On supprime les messages possiblement ajoutés au libellé si on le modifie plusieurs fois (évite d'avoir plusieurs fois accepté ou refusé)
        $msg[0] = 'ACCEPTE : ';
        $msg[1] = 'REFUSE : ';

        for($i = 0; $i < 2; $i++)
        {
            $libelleHF = str_replace($msg[$i], '', $libelleHF);
        }

        /**
         * Le libellé du message est différent si on l'accepte ou on le refuse, on met à jour la variable en fonction du contexte
         * L'usage de ternaire permet de réaliser cette affectation le plus simplement possible
         */
        $libelleHF = $valider ? $msg[0] . $libelleHF : $msg[1] . $libelleHF;

        $pdo->majFraisHorsForfait($idLigneHF, $libelleHF); // On finit par mettre à jour la ligne si elle a été acceptée ou refusée...
        break;
}