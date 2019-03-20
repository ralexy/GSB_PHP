<?php
/**
 * Vue Accueil
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

namespace gsb;
?>
<div id="accueil">
    <h2>
        <!-- TODO Switcher la vue selon comptable ou visiteur -->
        Gestion des frais<small> - <?php echo ucfirst($_SESSION['rang']); ?> :
            <?php 
            echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']
            ?></small>
    </h2>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="glyphicon glyphicon-bookmark"></span>
                    Navigation
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-md-12 text-center">
                        <?php
                        switch ($_SESSION['rang']) {
                            case 'comptable':
                                ?>
                                <a href="index.php?uc=validerFrais&action=voirFraisVisiteur"
                                   class="btn btn-success btn-lg" role="button">
                                    <span class="glyphicon glyphicon-ok"></span>
                                    <br>Valider les fiches de frais</a>
                                <a href="index.php?uc=suivreFrais&action=selectionnerMois"
                                   class="btn btn-primary btn-lg" role="button">
                                    <span class="glyphicon glyphicon-euro"></span>
                                    <br>Suivre le paiement des fiches de frais</a>
                                <?php
                            break;

                            default:
                                ?>
                                <a href="index.php?uc=gererFrais&action=saisirFrais"
                                   class="btn btn-success btn-lg" role="button">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                    <br>Renseigner la fiche de frais</a>
                                <a href="index.php?uc=etatFrais&action=selectionnerMois"
                                   class="btn btn-primary btn-lg" role="button">
                                    <span class="glyphicon glyphicon-list-alt"></span>
                                    <br>Afficher mes fiches de frais</a>
                                <?php
                                break;
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>