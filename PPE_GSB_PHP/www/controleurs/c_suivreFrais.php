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
 * @version   GIT: <12>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

$lesFiches                = $pdo->getListeFicheFraisValidees();
$ficheChoisie             = isset($_POST['lstFiches']) ? filter_input(INPUT_POST, 'lstFiches', FILTER_SANITIZE_STRING) : $_SESSION['ficheChoisie'];
$_SESSION['ficheChoisie'] = $ficheChoisie;

if(isset($ficheChoisie)) {
    $ficheChoisie = explode('-', $ficheChoisie);  // On explode notre idVisiteur et Mois grâce au tiret mis dans le select (plus puissant et ergonomique qu'un double select)
    $idVisiteur = $ficheChoisie[0];
    $idMois =  $ficheChoisie[1];

    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $idMois);
    $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $idMois);
    $infosFiche = $pdo->getLesInfosFicheFrais($idVisiteur, $idMois); // A Revoir on a les bonnes fiches déjà
}

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
        if(isset($_POST['paiement'])) {
            switch($infosFiche['idEtat']) {
                case 'RB':
                    $_SESSION['flash'] = 'La fiche de frais est déjà remboursée, elle ne peut donc pas être mise en paiement !';
                break;

                case 'PA':
                    $_SESSION['flash'] = 'La fiche de frais est déjà payée, elle ne peut donc pas être mise en paiement !';
                break;

                // On ne met en paiement que des fiches validées
                case 'VA':
                    $pdo->majEtatFicheFrais($idVisiteur, $idMois, 'PA'); // PA pour mise en paiement
                    $_SESSION['flash'] = 'La fiche de frais a bien été mise en paiement';
                    break;

                default:
                    $_SESSION['flash'] = 'Erreur : La mise en paiement de la fiche est impossible. Vérifier que le workflow de celle-ci a été respecté.';
                break;
            }
        } elseif(isset($_POST['remboursement'])) {
            switch($infosFiche['idEtat']) {
                case 'VA':
                    $_SESSION['flash'] = 'La fiche de frais doit être mise en paiement avant d\'être remboursée !';
                    break;

                case 'RB':
                    $_SESSION['flash'] = 'La fiche de frais est déjà remboursée !';
                    break;

                // On ne rembourse que des fiches mises en paiement
                case 'PA':
                    $pdo->majEtatFicheFrais($idVisiteur, $idMois, 'RB');// RB pour remboursé
                    $_SESSION['flash'] = 'La fiche de frais a bien été classée comme remboursée.';
                    break;

                default:
                    $_SESSION['flash'] = 'Erreur : La remboursement de la fiche est impossible. Vérifier que le workflow de celle-ci a été respecté.';
                    break;
            }
        }
        header('Location: index.php?uc=suivreFrais');
    break;

case'selectionnerMois':
    // On supprime la fiche choisie pour laisser le choix au visiteur si il clique sur le menu et on le redirige pour bien afficher la page
    if($_SESSION['ficheChoisie']) {
        unset($_SESSION['ficheChoisie']);
        header('Location: index.php?uc=suivreFrais&action=selectionnerMois');
    }
break;

case 'export':
    /*var_dump($infosFiche);
    var_dump($lesFraisForfait);
    var_dump($lesFraisHorsForfait);
    var_dump($lesFiches);*/
    require 'vues/comptable/v_ficheFraisPdf.php';
    exit;
break;
}
if($lesFiches) {
    require 'vues/comptable/v_suivreFrais.php';
} else {
    require 'vues/comptable/v_suiviFraisVide.php';
}