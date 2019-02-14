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
 * @version   GIT: <10>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

$idMembre              = $_SESSION['idMembre'];
$lesVisiteurs          = $pdo->getListeVisiteurs();
$action                = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$moisASelectionner     = isset($_POST['lstMois']) ? filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING) : $_SESSION['moisASelectionner']; // Permet de selectionner dans le select le bon mois
$idVisiteurSelectionne = isset($_POST['lstVisiteurs']) ? filter_input(INPUT_POST, 'lstVisiteurs', FILTER_SANITIZE_STRING) : $_SESSION['idVisiteurSelectionne'];
$lesMoisDisponibles    = $pdo->getLesMoisDisponibles(false, 'CL');

$_SESSION['moisASelectionner'] = $moisASelectionner;
$_SESSION['idVisiteurSelectionne'] = $idVisiteurSelectionne;

switch ($action) {
case 'validerSaisieFraisVisiteur';
    // On récupère l'id du visiteur selectionné dans le <select>
    $mois     = $moisASelectionner;
    $numAnnee = substr($mois, 0, 4);
    $numMois  = substr($mois, 4, 2);

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
    $mois       = filter_input(INPUT_POST, 'mois', FILTER_DEFAULT, FILTER_SANITIZE_STRING);
    $lesFrais   = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
    $idVisiteur = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_STRING);
    $success = true; // Variable permettant d'afficher la popup comme quoi la modification a bien été prise en compte

    if (lesQteFraisValides($lesFrais)) {
        $pdo->majFraisForfait($idVisiteur, $mois, $lesFrais);
        $_SESSION['flash'] = 'Les "frais forfait" ont bien été mis à jour !';

        header('Location: index.php?uc=validerFrais&action=validerSaisieFraisVisiteur');

    } else {
        ajouterErreur('Les valeurs des frais doivent être numériques');
        include 'vues/v_erreurs.php';
    }
    break;

    /** Todo
     *  Revoir le fonctionnement de ce "bloc" et de la vue pour faire tout ça plus proprement (sans input hidden et peut être plus intelligemment pour le if else)
     */
case 'validerMajFraisHF':
    $idLigneHF             = filter_input(INPUT_POST, 'idLigneHF', FILTER_SANITIZE_STRING);
    $libelleHF             = filter_input(INPUT_POST, 'txtLibelleHF', FILTER_SANITIZE_STRING);
    $action                = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // On récupère l'action du formulaire (Valider, Refuser ou Reporter)
    $idVisiteurSelectionne = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_STRING);
    $mois                  = filter_input(INPUT_POST, 'numMois', FILTER_SANITIZE_STRING);
    $montantValide         = filter_input(INPUT_POST, 'txtMontant', FILTER_SANITIZE_STRING);
    $montantHF             = filter_input(INPUT_POST, 'txtMontantHF', FILTER_SANITIZE_STRING);

    if($action == 'Refuser') {
        $occRemplacee  = (count($libelleHF) > count(nettoieLibelle($libelleHF))) ? true : false;
        $montantValide = $montantValide - $montantHF;
        $libelleHF     = 'REFUSE : ' . nettoieLibelle($libelleHF);

        $pdo->majFraisHorsForfait($idLigneHF, $libelleHF); // On finit par mettre à jour la ligne si elle a été acceptée ou refusée...

        // Attaquons maintenant le montant validé de la fiche de frais
        $ficheAmodifier = $pdo->getLesInfosFicheFrais($idVisiteurSelectionne, $mois);

        if($occRemplacee) {
            $pdo->majFraisValideFicheFrais($idVisiteurSelectionne, $mois, $montantValide);
        }

        $_SESSION['flash'] = 'Le frais HF a bien été refusé.';
        header('Location: index.php?uc=validerFrais&action=validerSaisieFraisVisiteur');
    }
    elseif($action == 'Reporter') {

        /**
         * On passe par l'objet DateTime pour manipuler la date,
         * c'est beaucoup plus simple et plus puissant que de jongler avec les méthodes de PHP qui,
         * au final font la même chose de façon plus verbeuse
         */
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

        $_SESSION['flash'] = 'La fiche de frais a bien été reportée.';
        header('Location: index.php?uc=validerFrais&action=validerSaisieFraisVisiteur');
    }
    break;

case 'validerFicheFrais':
    $idVisiteurSelectionne = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_STRING);
    $mois                  = filter_input(INPUT_POST, 'numMois', FILTER_SANITIZE_STRING);
    $nbJustificatifs       = (int) filter_input(INPUT_POST, 'txtNbHF', FILTER_SANITIZE_STRING);
    $montantValide         = 0;

    /**
     * On commence par nettoyer les frais HF pour éviter de mettre plusieurs fois "ACCEPTE :"
     * Si le frais a été préalablement "REFUSE : " on ne le prend pas en compte
     * On MAJ aussi le montant validé des Frais HF
     */
    $lesFraisHF = $pdo->getLesFraisHorsForfait($idVisiteurSelectionne, $mois);

    for($i = 0; $i < count($lesFraisHF); $i++) {
        if(!strpos('REFUSE :', $lesFraisHF[$i]['libelle'])) {
            $lesFraisHF[$i]['libelle'] = nettoieLibelle($lesFraisHF[$i]['libelle']);
            $montantValide += $lesFraisHF[$i]['montant'];
        }
    }

    /**
     * 1. On valide les frais HF
     * 2. On met à jour le nb de frais validés
     * 3. On met à jour le montant validé de la FDF
     * 4. On met à jour la fiche en "validée" et sa date de modification
     */
    $pdo->validerFraisHorsForfait($idVisiteurSelectionne, $mois);
    $pdo->majNbJustificatifs($idVisiteurSelectionne, $mois, $nbJustificatifs);
    $pdo->majFraisValideFicheFrais($idVisiteurSelectionne, $mois, $montantValide);
    $pdo->majEtatFicheFrais($idVisiteurSelectionne, $mois, 'VA'); // VA pour validé

    $_SESSION['flash'] = 'Le fiche de frais a bien été validée.';
    header('Location: index.php?uc=suivreFrais');
    break;
}

require 'vues/comptable/v_listeVisiteurs.php';