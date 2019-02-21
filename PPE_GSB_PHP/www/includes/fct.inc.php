<?php
/**
 * Fonctions pour l'application GSB
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @author    Alexy ROUSSEAU <contact@alexy-rousseau.com>
 * @copyright 2017-2019 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <11>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

/**
 * Teste si un quelconque membre est connecté
 *
 * @return vrai ou faux
 */
function estConnecte()
{
    return isset($_SESSION['idMembre']);
}

/**
 * Enregistre dans une variable session les infos d'un membre
 *
 * @param String $idMembre ID du membre
 * @param String $nom      Nom du membre
 * @param String $prenom   Prénom du membre
 * @param String $rang     Le rang du membre (visiteur ou comptable)
 *
 * @return null
 */
function connecter($idMembre, $nom, $prenom, $rang)
{
    $_SESSION['idMembre'] = $idMembre;
    $_SESSION['nom'] = $nom;
    $_SESSION['prenom'] = $prenom;
    $_SESSION['rang'] = $rang;
}

/**
 * Détruit la session active
 *
 * @return null
 */
function deconnecter()
{
    session_destroy();
}

/**
 * Transforme une date au format français jj/mm/aaaa vers le format anglais
 * aaaa-mm-jj
 *
 * @param String $maDate au format  jj/mm/aaaa
 *
 * @return Date au format anglais aaaa-mm-jj
 */
function dateFrancaisVersAnglais($maDate)
{
    @list($jour, $mois, $annee) = explode('/', $maDate);
    return date('Y-m-d', mktime(0, 0, 0, $mois, $jour, $annee));
}

/**
 * Transforme une date au format format anglais aaaa-mm-jj vers le format
 * français jj/mm/aaaa
 *
 * @param String $maDate au format  aaaa-mm-jj
 *
 * @return Date au format format français jj/mm/aaaa
 */
function dateAnglaisVersFrancais($maDate)
{
    @list($annee, $mois, $jour) = explode('-', $maDate);
    $date = $jour . '/' . $mois . '/' . $annee;
    return $date;
}

/**
 * Retourne le mois au format aaaamm selon le jour dans le mois
 *
 * @param String $date au format  jj/mm/aaaa
 *
 * @return String Mois au format aaaamm
 */
function getMois($date)
{
    @list($jour, $mois, $annee) = explode('/', $date);
    unset($jour);
    if (strlen($mois) == 1) {
        $mois = '0' . $mois;
    }
    return $annee . $mois;
}

/**
 * Retourne le mois en cours en français depuis une chaine aaaamm
 *
 * @param String $date au format  aaaamm
 *
 * @return String Date au format mm/aaaa
 */
function getMoisFrancais($date)
{
    $mois = substr($date, -2);
    $annee = substr($date, 0, 4);

    return $mois . '/' . $annee;
}

/* gestion des erreurs */

/**
 * Indique si une valeur est un entier positif ou nul
 *
 * @param Integer $valeur Valeur
 *
 * @return Boolean vrai ou faux
 */
function estEntierPositif($valeur)
{
    return preg_match('/[^0-9]/', $valeur) == 0;
}

/**
 * Indique si un tableau de valeurs est constitué d'entiers positifs ou nuls
 *
 * @param Array $tabEntiers Un tableau d'entier
 *
 * @return Boolean vrai ou faux
 */
function estTableauEntiers($tabEntiers)
{
    $boolReturn = true;
    foreach ($tabEntiers as $unEntier) {
        if (!estEntierPositif($unEntier)) {
            $boolReturn = false;
        }
    }
    return $boolReturn;
}

/**
 * Vérifie si une date est inférieure d'un an à la date actuelle
 *
 * @param String $dateTestee Date à tester
 *
 * @return Boolean vrai ou faux
 */
function estDateDepassee($dateTestee)
{
    $dateActuelle = date('d/m/Y');
    @list($jour, $mois, $annee) = explode('/', $dateActuelle);
    $annee--;
    $anPasse = $annee . $mois . $jour;
    @list($jourTeste, $moisTeste, $anneeTeste) = explode('/', $dateTestee);
    return ($anneeTeste . $moisTeste . $jourTeste < $anPasse);
}

/**
 * Vérifie la validité du format d'une date française jj/mm/aaaa
 *
 * @param String $date Date à tester
 *
 * @return Boolean vrai ou faux
 */
function estDateValide($date)
{
    $tabDate = explode('/', $date);
    $dateOK = true;
    if (count($tabDate) != 3) {
        $dateOK = false;
    } else {
        if (!estTableauEntiers($tabDate)) {
            $dateOK = false;
        } else {
            if (!checkdate($tabDate[1], $tabDate[0], $tabDate[2])) {
                $dateOK = false;
            }
        }
    }
    return $dateOK;
}

/**
 * Vérifie que le tableau de frais ne contient que des valeurs numériques
 *
 * @param Array $lesFrais Tableau d'entier
 *
 * @return Boolean vrai ou faux
 */
function lesQteFraisValides($lesFrais)
{
    return estTableauEntiers($lesFrais);
}

/**
 * Vérifie la validité des trois arguments : la date, le libellé du frais
 * et le montant
 *
 * Des message d'erreurs sont ajoutés au tableau des erreurs
 *
 * @param String $dateFrais Date des frais
 * @param String $libelle   Libellé des frais
 * @param Float  $montant   Montant des frais
 *
 * @return null
 */
function valideInfosFrais($dateFrais, $libelle, $montant)
{
    if ($dateFrais == '') {
        ajouterErreur('Le champ date ne doit pas être vide');
    } else {
        if (!estDatevalide($dateFrais)) {
            ajouterErreur('Date invalide');
        } else {
            if (estDateDepassee($dateFrais)) {
                ajouterErreur(
                    "date d'enregistrement du frais dépassé, plus de 1 an"
                );
            }
        }
    }
    if ($libelle == '') {
        ajouterErreur('Le champ description ne peut pas être vide');
    }
    if ($montant == '') {
        ajouterErreur('Le champ montant ne peut pas être vide');
    } elseif (!is_numeric($montant)) {
        ajouterErreur('Le champ montant doit être numérique');
    }
}

/**
 * Ajoute le libellé d'une erreur au tableau des erreurs
 *
 * @param String $msg Libellé de l'erreur
 *
 * @return null
 */
function ajouterErreur($msg)
{
    if (!isset($_REQUEST['erreurs'])) {
        $_REQUEST['erreurs'] = array();
    }
    $_REQUEST['erreurs'][] = $msg;
}

/**
 * Retoune le nombre de lignes du tableau des erreurs
 *
 * @return Integer le nombre d'erreurs
 */
function nbErreurs()
{
    if (!isset($_REQUEST['erreurs'])) {
        return 0;
    } else {
        return count($_REQUEST['erreurs']);
    }
}

/**
 * "Nettoie" un libellé de Frais HF en retirant "ACCEPTE :" ou "REFUSE :"
 *
 * @param String $libelle Libellé à nettoyer
 * @param Int $length la taille maximale de la chaîne à "nettoyer" (100 caractères par défaut)
 *
 * @return String le libellé
 */
function nettoieLibelle($libelle, $length = 100)
{
    /**
     * On supprime les messages possiblement ajoutés au libellé si on le modifie plusieurs fois (évite d'avoir plusieurs fois accepté ou refusé)
     */
    $msg[0] = 'ACCEPTE : ';
    $msg[1] = 'REFUSE : ';

    // Si on trouve "ACCEPTE : " ou "REFUSE :" dans notre libellé on les supprime
    while(strpos($libelle, $msg[0]) !== false || strpos($libelle, $msg[1]) !== false)
    {
        $libelle = str_replace($msg[0], '', $libelle);
        $libelle = str_replace($msg[1], '', $libelle);
    }

    /**
    * On tronque la chaîne si elle dépasse les $length caractères (comme demandé en sur la fiche descriptive)
    * $length -5 pour prendre en compte "(...)" qui fait 5 caractères
    */
    $libelle = (strlen($libelle) < $length) ? $libelle : substr($libelle, 0, $length - 5). '(...)';

    return $libelle;
}