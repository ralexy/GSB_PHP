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
 * @version   GIT: <6>
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
}