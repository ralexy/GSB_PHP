<?php
/**
 * Vue de la fiche de frais servant à la génération du PDF
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


setlocale(LC_TIME, "fr_FR");

require_once 'includes/fct.inc.php';
require_once 'includes/class.pdogsb.inc.php';

$pdo = PdoGsb::getPdoGsb();
$estConnecte = estConnecte();
$estComptable = (isset($_SESSION['rang']) && $_SESSION['rang'] == 'comptable') ? true : false;

if (!$estComptable) {
    header('Location: index.php');
}

/**
 * Initialisation des données de la fiche
 */
$donneesFiche = [
    'id'          => $idVisiteur,
    'nom'         => strtoupper($infosFiche['nom']). ' '. $infosFiche['prenom'],
    'mois'        => strftime("%B %Y", (new DateTime($infosFiche['mois']))->getTimestamp()),
    'total'       => $infosFiche['montantValide'],
    'indemnitekm' => $infosFiche['indemnitekm'],

    'fraisForfaitaires' => [],
    'fraisHorsForfait'  => []
];

foreach($lesFraisForfait as $leFraisForfait) {
    $donneesFiche['fraisForfaitaires'][] = [
        'id'              => $leFraisForfait['idfrais'],
        'titre'           => $leFraisForfait['libelle'],
        'quantite'        => $leFraisForfait['quantite'],
        'montantUnitaire' => $leFraisForfait['montant']
    ];
}

//var_dump($lesFraisHorsForfait);
//die;

foreach($lesFraisHorsForfait as $leFraisHorsForfait) {
    $donneesFiche['fraisHorsForfait'][] = [
        'date'            => $leFraisHorsForfait['date'],
        'libelle'         => $leFraisHorsForfait['libelle'],
        'montantUnitaire' => $leFraisHorsForfait['montant']
    ];
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <meta name="robots" content="none">
    <title>Remboursement de frais engagés</title>
    <style>
        table {
            border: 1px solid black;
            border-collapse: collapse;
            margin: 0 auto;
            min-width: 700px;
            width: 99%;
        }
        table th {
            text-transform: uppercase;
            border-bottom: 1px solid black;
            height: 35px;
            color: #1F497D;
        }
        table tr, table td {
            border: 1px solid #1F497D;
            height: 28px;
        }
        table tr:nth-child(2), table tr:nth-child(2) td,
        table tr:nth-child(3), table tr:nth-child(3) td,
        table tr:nth-child(4), table tr:nth-child(4) td,
        table tr:nth-child(5), table tr:nth-child(5) td {
            border: 0px !important;
            height: 30px;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .subth {
            border: inherit;
            font-weight: bold;
            font-style: italic;
            color: #1F497D;
        }
        table .totalSize {
            width: 100px;
        }
        table .noBorder {
            border: none !important;
        }
        .tableSize {
            min-width: 700px;
            width: 99%;
            margin: 70px auto -50px auto;
        }
    </style>
</head>
<body>

<p class="center"><img src="./images/logo.jpg" width="96" height="61"></p>
<table>
    <tr>
        <th colspan="4">Remboursement des frais engages</th>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td>Visiteur</td>
        <td><?php echo $donneesFiche['id']; ?></td>
        <td><?php echo $donneesFiche['nom']; ?></td>
    </tr>
    <tr>
        <td>Mois</td>
        <td><?php echo ucwords($donneesFiche['mois']); ?></td>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>

    <tr class="center subth">
        <td>Frais Forfaitaires</td>
        <td>Quantité</td>
        <td>Montant unitaire</td>
        <td class="totalsize">Total</td>
    </tr>

    <?php
    foreach($donneesFiche['fraisForfaitaires'] as $ligneFraisForfait)
    {
        ?>
        <tr>
            <td><?php echo $ligneFraisForfait['titre']; ?></td>
            <td class="right"><?php echo $ligneFraisForfait['quantite']; ?></td>
            <td class="right">
                <?php
                if($ligneFraisForfait['id'] != 'KM') {
                    echo $ligneFraisForfait['montantUnitaire'];

                    $total += $ligneFraisForfait['quantite'] * $ligneFraisForfait['montantUnitaire'];

                } else {
                    echo $donneesFiche['indemnitekm'];

                    $total += $ligneFraisForfait['quantite'] * $donneesFiche['indemnitekm'];
                }
                ?>
            </td>
            <td class="right totalSize"><?php echo number_format($ligneFraisForfait['quantite'] * $ligneFraisForfait['montantUnitaire'], 2); ?></td>
        </tr>
        <?php
    }
    ?>

    <tr class="noBorder">
        <td colspan="4" class="noBorder"></td>
    </tr>
    <tr class="subth">
        <td colspan="4" class="center subth">Autres frais</td>
    </tr>

    <tr>
    <tr class="center subth">
        <td>Date</td>
        <td colspan="2">Libellé</td>
        <td class="totalSize">Montant</td>
    </tr>

    <?php
    foreach($donneesFiche['fraisHorsForfait'] as $ligneFraisHf)
    {
        ?>
        <tr>
            <td><?php echo $ligneFraisHf['date']; ?></td>
            <td colspan="2"><?php echo $ligneFraisHf['libelle']; ?></td>
            <td class="right totalSize"><?php echo number_format($ligneFraisHf['montantUnitaire'], 2); ?></td>
        </tr>
        <?php
        // Si on n'a pas de mention "REFUSER :" dans notre libellé
        if($ligneFraisHf['libelle'] == nettoieLibelle($ligneFraisHf['libelle'])) {
            $total += $ligneFraisHf['montantUnitaire'];
        }
    }
    ?>
    <tr class="noBorder">
        <td colspan="4" class="noBorder"></td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td>Total <?php echo (new DateTime($infosFiche['mois']))->format('m/Y'); ?></td>
        <td class="right totalSize"><?php echo number_format($total, 2); ?></td>
    </tr>
    <tr class="noBorder">
        <td colspan="4" class="noBorder"></td>
    </tr>
</table>
<p class="right tableSize">
    Fait à Paris, le <?php echo ucwords(strftime("%d %B %G", strtotime("now"))); ?><br />
    Vu l'agent comptable
</p>
<p class="right tableSize"><img src="./images/signature.jpg"></p>
</body>
</html>