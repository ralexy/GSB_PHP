<?php
/**
 * Suivi des frais
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
 * @version   GIT: <9>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

### TEMPORAIRE FIX DES VALEURS POUR LE DEV ###
$lesFiches = $pdo->getListeFicheFraisValidees();
$ficheChoisie = filter_input(INPUT_POST, 'lstFiches', FILTER_SANITIZE_STRING);

if($ficheChoisie) {
    $ficheChoisie = explode('-', $ficheChoisie);  // On explode notre idVisiteur et Mois grâce au tiret mis dans le select (plus puissant et ergonomique qu'un double select)
    $idVisiteur = $ficheChoisie[0];
    $mois =  $ficheChoisie[1];

    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
    $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
}
###

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
switch ($action) {
case 'miseEnPaiementFiche':
        if(!$ficheChoisie) {
            continue;
        }
        /**
         * Inutile de filtrer les $_POST puisqu'on s'en sert uniquement pour le if / elseif
         * Pas de switch envisageable car nos 2 vars $_POST ne portent pas le même nom
         */
        $infosFiche = $pdo->getLesInfosFicheFrais($idVisiteur, $mois); // A Revoir on a les bonnes fiches déjà

        if(isset($_POST['paiement'])) {
            // Si la fiche est déjà remboursée on ne la remet pas en paiement
            if($infosFiche['idEtat'] == 'RB') {
                $_SESSION['flash'] = 'La fiche de frais est déjà remboursée, elle ne peut donc pas être mise en paiement !';
                continue(1);
            }
            $pdo->majEtatFicheFrais($idVisiteur, $mois, 'PA'); // PA pour mise en paiement
            $_SESSION['flash'] = 'La fiche de frais a bien été mise en paiement';
        } elseif(isset($_POST['remboursement'])) {
            // Si on a déjà remboursé la fiche on affiche une alerte et on ne modifie pas notre tuple
            if ($infosFiche['idEtat'] == 'RB') {
                $_SESSION['flash'] = 'La fiche de frais est déjà remboursée !';
                continue(1);
            }
            $pdo->majEtatFicheFrais($idVisiteur, $mois, 'RB'); // RB pour remboursé
            $_SESSION['flash'] = 'La fiche de frais a bien été classée comme remboursée.';
        }
    break;
}
if($lesFiches) {
    require 'vues/comptable/v_suivreFrais.php';
} else {
    require 'vues/comptable/v_suiviFraisVide.php';
}