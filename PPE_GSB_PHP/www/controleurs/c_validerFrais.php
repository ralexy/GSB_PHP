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
 * @version   GIT: <13>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

// On créé nos variable de session si elles n'existent pas
if(empty($_SESSION['moisChoisi']) || empty($_SESSION['idVisiteurChoisi'])) {
    $_SESSION['moisChoisi'] = '';
    $_SESSION['idVisiteurChoisi'] = '';
}

$idMembre              = $_SESSION['idMembre'];
$lesVisiteurs          = $pdo->getListeVisiteurs();
$action                = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$moisChoisi            = isset($_POST['lstMois']) ? filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING) : $_SESSION['moisChoisi']; // Permet de selectionner dans le select le bon mois
$idVisiteur            = isset($_POST['lstVisiteurs']) ? filter_input(INPUT_POST, 'lstVisiteurs', FILTER_SANITIZE_STRING) : $_SESSION['idVisiteurChoisi'];
$lesMoisDisponibles    = $pdo->getLesMoisDisponibles(false, 'CL');
$mois                  = substr($moisChoisi, 0, 4);
$annee                 = substr($moisChoisi, 4, 2);
$lesVehicules          = $pdo->getLesVehicules();
$infosFicheFrais       = $pdo->getLesInfosFicheFrais($idVisiteur, $moisChoisi);

$_SESSION['moisChoisi']       = $moisChoisi;
$_SESSION['idVisiteurChoisi'] = $idVisiteur;

switch ($action) {
    case 'validerSaisieFraisVisiteur';
        require 'vues/comptable/v_listeVisiteurs.php';

        // On n'affiche que les fiches à l'état clôturé
        if($pdo->getLesInfosFicheFrais($idVisiteur, $moisChoisi, 'CL')) {
            // On fait une recherche de la clé du tableau associatif correspondant à notre visiteur pour le sélectionner dans notre variable $leVisiteur
            $matchedKey = array_search($idVisiteur, array_column($lesVisiteurs, 'id'));
            $leVisiteur = $lesVisiteurs[$matchedKey];

            $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $moisChoisi);
            $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $moisChoisi);

            if ($lesFraisForfait) {
                require 'vues/comptable/v_validerFrais.php';
                require 'vues/comptable/v_descriptifFraisHorsForfait.php';
            } else {
                require 'vues/comptable/v_fraisHorsForfaitVide.php';
            }
        } else {
            require 'vues/comptable/v_fraisHorsForfaitVide.php';
        }
        break;

    case 'validerMajFraisForfait':
        $lesFrais = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);

        if(lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($idVisiteur, $moisChoisi, $lesFrais);
            $pdo->majVehicule($idVisiteur, $moisChoisi, $lesFrais['VEH']);
            $_SESSION['flash'] = 'Les "frais forfait" ont bien été mis à jour !';

            header('Location: index.php?uc=validerFrais&action=validerSaisieFraisVisiteur');

        } else {
            ajouterErreur('Les valeurs des frais doivent être numériques');
            include 'vues/v_erreurs.php';
        }
        break;

    case 'validerMajFraisHF':
        $idLigneHF     = filter_input(INPUT_POST, 'idLigneHF', FILTER_SANITIZE_STRING);
        $libelleHF     = filter_input(INPUT_POST, 'txtLibelleHF', FILTER_SANITIZE_STRING);
        $formAction    = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // On récupère l'action du formulaire (Valider ou Refuser)
        $montantValide = filter_input(INPUT_POST, 'txtMontant', FILTER_SANITIZE_STRING);
        $montantHF     = filter_input(INPUT_POST, 'txtMontantHF', FILTER_SANITIZE_STRING);

        if ($formAction == 'Refuser') {
            $occRemplacee = (count($libelleHF) > count(nettoieLibelle($libelleHF))) ? true : false;
            $montantValide = $montantValide - $montantHF;
            $libelleHF = LABEL_REFUSE . nettoieLibelle($libelleHF, 89);

            $pdo->majFraisHorsForfait($idLigneHF, $libelleHF); // On finit par mettre à jour la ligne si elle a été acceptée ou refusée...

            // Attaquons maintenant le montant validé de la fiche de frais
            $ficheAmodifier = $pdo->getLesInfosFicheFrais($idVisiteur, $mois);

            // Si on refuse pour la première fois la fiche on soustrait son montant au montant validé
            if ($occRemplacee) {
                $pdo->majFraisValideFicheFrais($idVisiteur, $mois, $montantValide);
            }

            $_SESSION['flash'] = 'Le frais HF a bien été refusé.';
        } elseif ($formAction == 'Reporter') {
            /**
             * On passe par l'objet DateTime pour manipuler la date,
             * c'est beaucoup plus simple et plus puissant que de jongler avec les méthodes de PHP qui,
             * au final font la même chose de façon plus verbeuse
             */
            $dateMoisSuivant = (new DateTime('first day of this month'))->modify('+1 month')
                ->format('d/m/Y');
            $moisSuivant = getMois($dateMoisSuivant); // On finit par utiliser notre méthode getMois pour avoir la date au format souhaité

            // Si la fiche n'existe pas on la crée
            if (!$pdo->getLesFraisForfait($idVisiteur, $moisSuivant)) {
                $pdo->creeNouvellesLignesFrais($idVisiteur, $moisSuivant);
            }

            // On créé la nouvelle ligne HF
            $pdo->creeNouveauFraisHorsForfait($idVisiteur, $moisSuivant, $libelleHF, $dateMoisSuivant, $montantHF);

            // On supprime l'ancien frais HF
            $pdo->supprimerFraisHorsForfait($idLigneHF);

            $_SESSION['flash'] = 'La fiche de frais a bien été reportée.';
        }
        header('Location: index.php?uc=validerFrais&action=validerSaisieFraisVisiteur');
        break;

    case 'validerFicheFrais':
        $nbJustificatifs = filter_input(INPUT_POST, 'txtNbHF', FILTER_SANITIZE_NUMBER_INT);
        $montantValide = 0;

        /**
         * On commence par nettoyer les frais HF pour éviter de mettre plusieurs fois "ACCEPTE :"
         * Si le frais a été préalablement "REFUSE : " on ne le prend pas en compte
         * On MAJ aussi le montant validé des Frais HF
         */
        $lesFraisHF = $pdo->getLesFraisHorsForfait($idVisiteur, $moisChoisi);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $moisChoisi);
        $infosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $moisChoisi);

        // On commence par ajouter les frais HF validés au montant à rembourser
        for ($i = 0; $i < count($lesFraisHF); $i++) {
            if (strpos($lesFraisHF[$i]['libelle'], LABEL_REFUSE) === false) {
                $lesFraisHF[$i]['libelle'] = nettoieLibelle($lesFraisHF[$i]['libelle']);
                $montantValide += $lesFraisHF[$i]['montant'];
            }
        }

        /**
         * Et on finit par les frais forfait
         * On multiplie la quantité de frais par le montant fixé en DB
         */
        foreach($lesFraisForfait as $key => $value) {
            $montantValide += $value['quantite'] * $value['montant'];

            // Particularité de l'indemnité KM gérée différemment (en fct du type de véhicule)
            if($value['idfrais'] == 'KM' && $value['quantite'] > 0) {
                $montantValide += $value['quantite'] * $infosFicheFrais['indemnitekm'];
            }
        }

        /**
         * 1. On valide les frais HF
         * 2. On met à jour le nb de frais validés
         * 3. On met à jour le montant validé de la FDF
         * 4. On met à jour la fiche en "validée" et sa date de modification
         */
        $pdo->validerFraisHorsForfait($idVisiteur, $moisChoisi);
        $pdo->majNbJustificatifs($idVisiteur, $moisChoisi, $nbJustificatifs);
        $pdo->majFraisValideFicheFrais($idVisiteur, $moisChoisi, $montantValide);
        $pdo->majEtatFicheFrais($idVisiteur, $moisChoisi, 'VA'); // VA pour validé

        $_SESSION['flash'] = 'Le fiche de frais a bien été validée.';
        header('Location: index.php?uc=suivreFrais');
        break;

    default:
        require 'vues/comptable/v_listeVisiteurs.php';
        break;
}