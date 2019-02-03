<?php
/**
 * Vue Liste des frais au forfait
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
?>
<div class="row">
    <h2>Valider la fiche de frais
        <?php echo $numMois . '-' . $numAnnee ?>
    </h2>
    <h3>Eléments forfaitisés</h3>
    <div class="col-md-4">
        <?php if(isset($success)) { ?>
            <!-- TODO : S'occuper de l'alerte -->
            <div class="alert alert-success" role="alert" data-dismiss="alert">
                Les modifications ont bien été enregistrées !
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>

        <form method="post"
              action="index.php?uc=validerFrais&action=validerMajFraisForfait"
              role="form">
            <input type="hidden" name="mois" value="<?php echo $moisASelectionner; ?>">
            <input type="hidden" name="idVisiteur" value="<?php echo $idVisiteurSelectionne;?>">
            <fieldset>
                <?php
                foreach ($lesFraisForfait as $unFrais) {
                    $idFrais = $unFrais['idfrais'];
                    $libelle = htmlspecialchars($unFrais['libelle']);
                    $quantite = $unFrais['quantite']; ?>
                    <div class="form-group row col-md-10 col-lg-7">
                        <label for="idFrais"><?php echo $libelle ?></label>
                        <input type="text" id="idFrais"
                               name="lesFrais[<?php echo $idFrais ?>]"
                               size="4" maxlength="5"
                               value="<?php echo $quantite ?>"
                               class="form-control">
                    </div>
                    <?php
                }
                ?>
                <div class="row col-md-10 col-lg-9">
                    <button class="btn btn-success" type="submit">Corriger</button>
                    <button class="btn btn-danger" type="reset">Réinitialiser</button>
                </div>
            </fieldset>
        </form>
    </div>
</div>
