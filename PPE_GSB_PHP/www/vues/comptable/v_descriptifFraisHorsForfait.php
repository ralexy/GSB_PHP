<?php
/**
 * Vue Descriptif des frais HF
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

namespace gsb;
?>
<hr>
<div class="row">
    <div class="panel panel-info">
        <div class="panel-heading">Descriptif des éléments hors forfait</div>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th class="date">Date</th>
                    <th class="libelle">Libellé</th>
                    <th class="montant">Montant</th>
                    <th class="action">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                    $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                    $date = $unFraisHorsForfait['date'];
                    $montant = $unFraisHorsForfait['montant'];
                    $id = $unFraisHorsForfait['id']; ?>
                    <tr>
                        <form method="post" action="index.php?uc=validerFrais&action=validerMajFraisHF">
                            <td><input type="text" name="txtDateHF" value="<?php echo $date ?>" placeholder="Date"></td>
                            <td><input type="text" name="txtLibelleHF" value="<?php echo $libelle ?>" placeholder="Libellé" maxlength="100"></td>
                            <td><input type="text" name="txtMontantHF" value="<?php echo $montant ?>" placeholder="Montant"> </td>
                            <td>
                                <input type="hidden" name="idLigneHF" value="<?php echo $id; ?>">
                                <input type="submit" name="action" value="Refuser" class="btn btn-danger">
                                <input type="submit" name="action" value="Reporter" class="btn btn-info">
                            </td>
                        </form>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <form method="post" action="index.php?uc=validerFrais&action=validerFicheFrais">
        <label for="txtNbHF">Nombre de justificatif(s) :</label>
        <input type="text" name="txtNbHF" size="2" value="<?php echo count($lesFraisHorsForfait); ?>">
        <div id="form-inline">
            <input type="submit" name="action" value="Valider les frais" class="btn btn-success">
        </div>
    </form>
</div>