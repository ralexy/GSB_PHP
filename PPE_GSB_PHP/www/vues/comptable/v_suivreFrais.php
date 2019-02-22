<?php
/**
 * Vue Suivi des frais
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
 * @version   GIT: <11>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

namespace gsb;
?>
<div id="contenu">
    <h2>
        Sélection des fiches de frais
    </h2>
    <form action="index.php?uc=suivreFrais"
          method="post" role="form">
        <div class="form-group">
            <label for="lstFiches" accesskey="n">Fiche de frais : </label>
            <select id="lstFiches" name="lstFiches" class="form-control">
                <?php
                foreach ($lesFiches as $uneFiche) {
                    $id = $uneFiche['id'];
                    $nom = $uneFiche['nom'];
                    $prenom = $uneFiche['prenom'];
                    $mois = $uneFiche['mois'];
                    if ($id == $idVisiteur && $mois == $idMois) {
                        ?>
                        <option selected value="<?php echo $id . '-' . $mois;  ?>">
                            <?php echo strtoupper($nom) . ' ' . $prenom . ' - '. getMoisFrancais($mois); ?> </option>
                        <?php
                    } else {
                        ?>
                        <option value="<?php echo $id . '-' . $mois; ?>">
                            <?php echo strtoupper($nom) . ' ' . $prenom . ' - '. getMoisFrancais($mois); ?> </option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
        <input id="ok" type="submit" value="Valider" class="btn btn-success"
               role="button">
    </form>

    <?php
    if($ficheChoisie) {
        ?>

        <hr>

        <div class="panel panel-info">
            <div class="panel-heading">Eléments forfaitisés</div>
            <table class="table table-bordered table-responsive">
                <tr>
                    <?php
                    foreach ($lesFraisForfait as $unFraisForfait) {
                        $libelle = $unFraisForfait['libelle']; ?>
                        <th> <?php echo htmlspecialchars($libelle) ?></th>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <?php
                    foreach ($lesFraisForfait as $unFraisForfait) {
                        $quantite = $unFraisForfait['quantite']; ?>
                        <td class="qteForfait"><?php echo $quantite ?> </td>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </div>
        <div class="panel panel-info">
            <div class="panel-heading">Descriptif des éléments hors forfait -
                <?php echo $infosFiche['nbJustificatifs'] ?> justificatifs reçus
            </div>
            <table class="table table-bordered table-responsive">
                <tr>
                    <th class="date">Date</th>
                    <th class="libelle">Libellé</th>
                    <th class='montant'>Montant</th>
                </tr>
                <?php
                foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                    $date = $unFraisHorsForfait['date'];
                    $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                    $montant = $unFraisHorsForfait['montant']; ?>
                    <tr>
                        <td><?php echo $date ?></td>
                        <td><?php echo $libelle ?></td>
                        <td><?php echo $montant ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <form method="post" action="index.php?uc=suivreFrais&action=miseEnPaiementFiche">
            <input type="submit" name="paiement" value="Mettre en paiement" class="btn btn-info">
            <input type="submit" name="remboursement" value="Mettre en remboursement" class="btn btn-success">
        </form>
        <?php
    }
    ?>
</div>