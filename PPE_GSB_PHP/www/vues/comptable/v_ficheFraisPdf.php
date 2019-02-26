<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <meta name="robots" content="none">
    <title>Remboursement de frais engagés</title>
    <style>
        body
        {
            font-family: "Times New Roman", "Sans-Serif";
        }
        table {
            border: 1px solid black;
            border-collapse: separate;
            margin: 0 auto;
            min-width: 700px;
        }
        table th
        {
            text-transform: uppercase;
            border-bottom: 1px solid black;
            height: 35px;
            color: #1F497D;
            text-align: center;
            font-size: 1.2em;
        }
        table tr, table td {
            border: 1px solid #1F497D;
            height: 28px;
        }
        table tr:nth-child(2), table tr:nth-child(2) td,
        table tr:nth-child(3), table tr:nth-child(3) td,
        table tr:nth-child(4), table tr:nth-child(4) td,
        table tr:nth-child(5), table tr:nth-child(5) td{
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
        .tableSize
        {
            width: 700px;
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
        <td colspan="4"></td>
    </tr>
    <tr>
        <td>Visiteur</td>
        <td><?php echo $idVisiteur ;?></td>
        <td>
            <?php echo $lesFiches[array_search($idVisiteur, array_column($lesFiches, 'id'))]['prenom']; ?>
            <?php echo strtoupper($lesFiches[array_search($idVisiteur, array_column($lesFiches, 'id'))]['nom']); ?>
        </td>
    </tr>
    <tr>
        <td>Mois</td>
        <td><?php echo getMoisFrancais($idMois); ?></td>
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
    <tr>
        <td>Nuitée</td>
        <td class="right"><?php echo $lesFraisForfait[2]['quantite']; ?></td>
        <td class="right">80</td>
        <td class="right totalSize">630</td>
    </tr>
    <tr>
        <td>Repas Midi</td>
        <td class="right"><?php echo $lesFraisForfait[2]['quantite']; ?></td>
        <td class="right">80</td>
        <td class="right totalSize">630</td>
    </tr>
    <tr>
        <td>Véhicule</td>
        <td class="right"><?php echo $lesFraisForfait[1]['quantite']; ?></td>
        <td class="right">80</td>
        <td class="right totalSize">630</td>
    </tr>
    <tr class="noBorder">
        <td colspan="4" class="noBorder"></td>
    </tr>
    <tr class="subth">
        <td colspan="4" class="center subth">Autres frais</td>
    </tr>
    <tr class="center subth">
        <td>Date</td>
        <td colspan="2">Libellé</td>
        <td class="totalSize">Montant</td>
    </tr>

    <?php
    foreach($lesFraisHorsForfait as $leFraisHorsForfait) { ?>
        <tr>
            <td><?php echo $leFraisHorsForfait['date'] ?></td>
            <td colspan="2"><?php echo $leFraisHorsForfait['libelle'] ?></td>
            <td class="right totalSize"><?php echo $leFraisHorsForfait['montant'] ?></td>
        </tr>
    <?php } ?>
    <tr class="noBorder">
        <td colspan="4" class="noBorder"></td>
    </tr>
    <tr class="noBorder">
        <td colspan="2" class="noBorder"></td>
        <td>TOTAL 07/2017</td>
        <td class="right totalSize">1756.80</td>
    </tr>
    <tr class="noBorder">
        <td colspan="4" class="noBorder"></td>
    </tr>
</table>
<p class="right tableSize">
    Fait à Paris, le 7 Janvier 2011<br />
    Vu l'agent comptable
</p>
<p class="right tableSize"><img src="./images/signature.png"></p>

<!-- Scripts de génération du PDF -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.0.272/jspdf.debug.js"></script>
<script>
    let doc = new jsPDF('p','pt','a4');

    doc.addHTML(document.body,function() {
        doc.save('html.pdf');
    });
</script>
</body>
</html>