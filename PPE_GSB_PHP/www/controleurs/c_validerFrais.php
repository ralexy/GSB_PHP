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
 * @version   GIT: <8>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

$idMembre = $_SESSION['idMembre'];
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
    $numAnnee = substr($mois, 0, 4);
    $numMois = substr($mois, 4, 2);


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
    $success = true; // Variable permettant d'afficher la popup comme quoi la modification a bien été prise en compte
    if (lesQteFraisValides($lesFrais)) {
        $pdo->majFraisForfait($idVisiteur, $mois, $lesFrais);
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
    $libelleHF = filter_input(INPUT_POST, 'txtLibelleHF', FILTER_SANITIZE_STRING);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // On récupère l'action du formulaire (Valider, Refuser ou Reporter)
    $idVisiteurSelectionne = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_STRING);
    $mois = filter_input(INPUT_POST, 'numMois', FILTER_SANITIZE_STRING);
    $montantHF = filter_input(INPUT_POST, 'txtMontantHF', FILTER_SANITIZE_STRING);

    $occRemplacee = -1; // Par défaut à -1, si cette valeur n'évolue pas on a rien remplacé donc on Valide ou Refuse pour la première fois ce frais

    // On supprime les messages possiblement ajoutés au libellé si on le modifie plusieurs fois (évite d'avoir plusieurs fois accepté ou refusé)
    $msg[0] = 'ACCEPTE : ';
    $msg[1] = 'REFUSE : ';

    for ($i = 0; $i < 2; $i++) {
        $libelleHF = str_replace($msg[$i], '', $libelleHF, $count);

        // Si $count > 0 on a remplacé du texte dans la chaîne, on sauvegarde la valeur de $i pour des tests ultérieurs
        if($count > 0) {
            $occRemplacee = $i;
            continue;
        }
    }

    if($action == 'Valider' || $action == 'Refuser') {
        //var_dump($occRemplacee);

        /*
         * Booléen qui permet d'éviter de soustraire ou additionner plusieurs fois le même frais dans la table fraisforfait
         * On joue avec l'action et la chaîne remplacée (-1 = première modif, 0
         */
        switch ($action) {
        case 'Valider':
            $actionDifferente = ($occRemplacee == -1 || $occRemplacee == 1) ? true : false;
            break;

        case 'Refuser':
            $actionDifferente = ($occRemplacee == 0) ? true : false;
            break;

        default:
            $actionDifferente = false;
            break;
        }

        /**
        * Le libellé du message est différent si on l'accepte ou on le refuse, on met à jour la variable en fonction du contexte
        * L'usage de ternaire permet de réaliser cette affectation le plus simplement possible
        */
        $libelleHF = ($action == 'Valider') ? $msg[0] . $libelleHF : $msg[1] . $libelleHF;

        $pdo->majFraisHorsForfait($idLigneHF, $libelleHF); // On finit par mettre à jour la ligne si elle a été acceptée ou refusée...

        // Attaquons maintenant le montant validé de la fiche de frais
        $ficheAmodifier = $pdo->getLesInfosFicheFrais($idVisiteurSelectionne, $mois);

        $montantValide = ($action == 'Valider') ? $ficheAmodifier['montantValide'] + $montantHF : $ficheAmodifier['montantValide'] - $montantHF;

        if($actionDifferente) {
            $pdo->majFraisValideFicheFrais($idVisiteurSelectionne, $mois, $montantValide);
        }
    }
    elseif($action == 'Reporter') {

        // On passe par l'objet DateTime pour manipuler la date, c'est beaucoup plus simple et plus puissant que de jongler avec les méthodes de PHP qui, au final font la même chose de façon plus verbeuse
        $dateMoisSuivant = (new DateTime('first day of this month'))->modify('+1 month')
                                         ->format('d/m/Y');
        $moisSuivant = getMois($dateMoisSuivant); // On finit par utiliser notre méthode getMois pour avoir la date au format souhaité

        // Si la fiche n'existe pas on la crée
        if(!$pdo->getLesFraisForfait($idVisiteurSelectionne, $moisSuivant)) {
            $pdo->creeNouvellesLignesFrais($idVisiteurSelectionne, $moisSuivant);
        }

        // On créé la nouvelle ligne HF
        $pdo->creeNouveauFraisHorsForfait($idVisiteurSelectionne, $moisSuivant, $libelleHF, $dateMoisSuivant, $montantHF);

        // On supprime l'ancien frais HF
        $pdo->supprimerFraisHorsForfait($idLigneHF);
    }

    break;

case 'validerFicheFrais':
    // On récupère l'id du visiteur selectionné et du mois de la fiche via 2 posts hidden
    $idVisiteurSelectionne = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_STRING);
    $mois                  = filter_input(INPUT_POST, 'numMois', FILTER_SANITIZE_STRING);
    $nbJustificatifs       = (int) filter_input(INPUT_POST, 'txtNbHF', FILTER_SANITIZE_STRING);

    // 1. On met à jour le nb de frais validés
    // 2. On met à jour la fiche en "validée" et sa date de modification
    $pdo->majNbJustificatifs($idVisiteurSelectionne, $mois, $nbJustificatifs);
    $pdo->majEtatFicheFrais($idVisiteurSelectionne, $mois, 'VA'); // VA pour validé
    break;
}