<?php
/**
 * Classe d'accès aux données.
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL - CNED <jgil@ac-nice.fr>
 * @author    Alexy ROUSSEAU <contact@alexy-rousseau.com>
 * @copyright 2017-2019 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <13>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

/**
 * Classe d'accès aux données.
 *
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO
 * $monPdoGsb qui contiendra l'unique instance de la classe
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
 * @version   GIT: <13>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

class PdoGsb
{
    private static $serveur = 'mysql:host=localhost';
    private static $bdd = 'dbname=gsb_frais';
    private static $user = 'root';
    private static $mdp = 'root';
    private static $monPdo;
    private static $monPdoGsb = null;
    private static $cost = 12; // Nb d'itérations pour le chiffrement du MDP, plus on en a plus c'est sécurisé (et plus ça demande de ressources)

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct()
    {
        try {
            PdoGsb::$monPdo = new PDO(
                PdoGsb::$serveur . ';' . PdoGsb::$bdd,
                PdoGsb::$user,
                PdoGsb::$mdp
            );
            PdoGsb::$monPdo->query('SET CHARACTER SET utf8');

            /**
             * Appel de cette méthode uniquement une fois pour chiffrer les mdp en clair
             */
            //$this->securiseMotsDePasse();
        } catch(Exception $e) {
            die('Erreur : '. $e->getMessage());
        }
    }

    /**
     * Getter permettant de récupérer la valeur cost pour l'algorithme de chiffrement BCRYPT
     * Ainsi on a qu'à modifier la variable $cost dans cette classe pour renforcer ou affaiblir le nombre
     * de passes de l'algorithme
     * @return int
     */
    public function getCost() {
        return self::$cost;
    }

    /**
     * Méthode destructeur appelée dès qu'il n'y a plus de référence sur un
     * objet donné, ou dans n'importe quel ordre pendant la séquence d'arrêt.
     */
    public function __destruct()
    {
        PdoGsb::$monPdo = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     *
     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb()
    {
        if (PdoGsb::$monPdoGsb == null) {
            PdoGsb::$monPdoGsb = new PdoGsb();
        }
        return PdoGsb::$monPdoGsb;
    }

    /**
     * Retourne les informations d'un membre
     *
     * @param String $login Login du membre
     * @param String $mdp   Mot de passe du membre
     *
     * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
    public function getInfosMembre($login, $mdp)
    {
        // On effectue une requête pour aller chercher l'info dans la table membre & aussi rang
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT membre.id AS id, membre.nom AS nom, membre.mdp as mdp, '
            . 'membre.prenom AS prenom, rang.libelle as rang '
            . 'FROM membre LEFT JOIN rang '
            . 'ON membre.idrang = rang.id '
            . 'WHERE membre.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();
        $donnees = $requetePrepare->fetch();

        /**
         * Condition permettant de vérifier que le mdp saisi correspond bien au mdp stocké chiffré en DB
         */
        if(password_verify($mdp, $donnees['mdp'])) {
            unset($donnees['mdp']); // On supprime cette information du tableau par sécurité
            return $donnees;
        }

        return false;
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * hors forfait concernées par les deux arguments.
     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return tous les champs des lignes de frais hors forfait sous la forme
     * d'un tableau associatif
     */
    public function getLesFraisHorsForfait($idMembre, $mois)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT * FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.idmembre = :unIdMembre '
            . 'AND lignefraishorsforfait.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesLignes = $requetePrepare->fetchAll();
        for ($i = 0, $iMax = count($lesLignes); $i < $iMax; $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = dateAnglaisVersFrancais($date);
        }
        return $lesLignes;
    }

    /**
     * Retourne le nombre de justificatif d'un membre pour un mois donné
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return le nombre entier de justificatifs
     */
    public function getNbjustificatifs($idMembre, $mois)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT fichefrais.nbjustificatifs as nb FROM fichefrais '
            . 'WHERE fichefrais.idmembre = :unIdMembre '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['nb'];
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * au forfait concernées par les deux ou trois arguments
     *
     * @param String $idMembre ID du membre
     * @param String $mois     Mois sous la forme aaaamm
     *
     * @return l'id, le libelle et la quantité sous la forme d'un tableau
     * associatif
     */
    public function getLesFraisForfait($idMembre, $mois)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'SELECT fraisforfait.id as idfrais, '
            . 'fraisforfait.libelle as libelle, '
            . 'fraisforfait.montant as montant, '
            . 'lignefraisforfait.quantite as quantite '
            . 'FROM lignefraisforfait '
            . 'INNER JOIN fraisforfait '
            . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
            . 'WHERE lignefraisforfait.idmembre = :unIdMembre '
            . 'AND lignefraisforfait.mois = :unMois '
            . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne tous les id de la table FraisForfait
     *
     * @return un tableau associatif
     */
    public function getLesIdFrais()
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT fraisforfait.id as idfrais '
            . 'FROM fraisforfait ORDER BY fraisforfait.id'
        );
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne tous les véhicules avec leur indemnité kilométrique
     *
     * @return un tableau associatif
     */
    public function getLesVehicules()
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT vehicule.id as id, vehicule.nom as nom, vehicule.indemnitekm as indemnitekm '
            . 'FROM vehicule ORDER BY vehicule.id'
        );

        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Met à jour la table ligneFraisForfait
     * Met à jour la table ligneFraisForfait pour un membre et
     * un mois donné en enregistrant les nouveaux montants
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     * @param Array  $lesFrais   tableau associatif de clé idFrais et
     *                           de valeur la quantité pour ce frais
     *
     * @return null
     */
    public function majFraisForfait($idMembre, $mois, $lesFrais)
    {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $requetePrepare = PdoGSB::$monPdo->prepare(
                'UPDATE lignefraisforfait '
                . 'SET lignefraisforfait.quantite = :uneQte '
                . 'WHERE lignefraisforfait.idmembre = :unIdMembre '
                . 'AND lignefraisforfait.mois = :unMois '
                . 'AND lignefraisforfait.idfraisforfait = :idFrais'
            );
            $requetePrepare->bindParam(':uneQte', $qte, PDO::PARAM_INT);
            $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Met à jour la table fichefrais en lui ajoutant l'id du véhicule sélectionné dans la table vehicule
     *
     * @param $idMembre
     * @param $mois
     * @param $idVehicule
     *
     *
     * @return null
     */
    public function majVehicule($idMembre, $mois, $idVehicule)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'UPDATE fichefrais '
            . 'SET fichefrais.idvehicule = :unIdVehicule '
            . 'WHERE fichefrais.idmembre = :unIdMembre '
            . 'AND fichefrais.mois = :unMois'
        );

        $requetePrepare->bindParam(':unIdVehicule', $idVehicule, PDO::PARAM_INT);
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Met à jour la table ligneFraisHorsForfait
     * Met à jour la table ligneFraisHorsForfait pour un membre et
     * un mois donné en ajoutant au libellé "ACCEPTE :" et en ignorant ceux qui commencent par "REFUSE :"
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     *
     *
     * @return null
     */
    public function validerFraisHorsForfait($idMembre, $mois)
    {
        $unLibelleAccepte = 'ACCEPTE : ';
        $unLibelleDejaRefuse = 'REFUSE%';

        $requetePrepare = PdoGSB::$monPdo->prepare(
            'UPDATE lignefraishorsforfait '
            . 'SET lignefraishorsforfait.libelle = CONCAT(:unLibelleAccepte, lignefraishorsforfait.libelle) '
            . 'WHERE idmembre = :unIdMembre '
            . 'AND mois = :unMois '
            . 'AND lignefraishorsforfait.libelle NOT LIKE :unLibelleAccepte'
            . 'AND lignefraishorsforfait.libelle NOT LIKE :unLibelleRefuse'
        );
        $requetePrepare->bindParam(':unLibelleAccepte', $unLibelleAccepte, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unLibelleRefuse', $unLibelleDejaRefuse, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le membre concerné
     *
     * @param String  $idMembre        ID du membre
     * @param String  $mois            Mois sous la forme aaaamm
     * @param Integer $nbJustificatifs Nombre de justificatifs
     *
     * @return null
     */
    public function majNbJustificatifs($idMembre, $mois, $nbJustificatifs)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'UPDATE fichefrais '
            . 'SET nbjustificatifs = :unNbJustificatifs '
            . 'WHERE fichefrais.idmembre = :unIdMembre '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(
            ':unNbJustificatifs',
            $nbJustificatifs,
            PDO::PARAM_INT
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Teste si un membre possède une fiche de frais pour le mois passé en argument
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return vrai ou faux
     */
    public function estPremierFraisMois($idMembre, $mois)
    {
        $boolReturn = false;
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT fichefrais.mois FROM fichefrais '
            . 'WHERE fichefrais.mois = :unMois '
            . 'AND fichefrais.idmembre = :unIdMembre'
        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->execute();
        if (!$requetePrepare->fetch()) {
            $boolReturn = true;
        }
        return $boolReturn;
    }

    /**
     * Retourne le dernier mois en cours d'un membre
     *
     * @param String $idMembre ID du membre
     *
     * @return le mois sous la forme aaaamm
     */
    public function dernierMoisSaisi($idMembre)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT MAX(mois) as dernierMois '
            . 'FROM fichefrais '
            . 'WHERE fichefrais.idmembre = :unIdMembre'
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        $dernierMois = $laLigne['dernierMois'];
        return $dernierMois;
    }

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait
     * pour un membre et un mois donnés
     *
     * Récupère le dernier mois en cours de traitement, met à 'CL' son champs
     * idEtat, crée une nouvelle fiche de frais avec un idEtat à 'CR' et crée
     * les lignes de frais forfait de quantités nulles
     *
     * @param String $idMembre ID du membre
     * @param String $mois     Mois sous la forme aaaamm
     *
     * @return null
     */
    public function creeNouvellesLignesFrais($idMembre, $mois)
    {
        $dernierMois = $this->dernierMoisSaisi($idMembre);
        $laDerniereFiche = $this->getLesInfosFicheFrais($idMembre, $dernierMois);
        if ($laDerniereFiche['idEtat'] == 'CR') {
            $this->majEtatFicheFrais($idMembre, $dernierMois, 'CL');
        }
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'INSERT INTO fichefrais (idmembre,mois,nbjustificatifs,'
            . 'montantvalide,datemodif,idetat) '
            . "VALUES (:unIdMembre,:unMois,0,0,now(),'CR')"
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesIdFrais = $this->getLesIdFrais();
        foreach ($lesIdFrais as $unIdFrais) {
            $requetePrepare = PdoGsb::$monPdo->prepare(
                'INSERT INTO lignefraisforfait (idmembre,mois,'
                . 'idfraisforfait,quantite) '
                . 'VALUES(:unIdMembre, :unMois, :idFrais, 0)'
            );
            $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(
                ':idFrais',
                $unIdFrais['idfrais'],
                PDO::PARAM_STR
            );
            $requetePrepare->execute();
        }
    }

    /**
     * Crée un nouveau frais hors forfait pour un membre un mois donné
     * à partir des informations fournies en paramètre
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $libelle    Libellé du frais
     * @param String $date       Date du frais au format français jj//mm/aaaa
     * @param Float  $montant    Montant du frais
     *
     * @return null
     */
    public function creeNouveauFraisHorsForfait(
        $idMembre,
        $mois,
        $libelle,
        $date,
        $montant
    ) {
        $dateFr = dateFrancaisVersAnglais($date);
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'INSERT INTO lignefraishorsforfait '
            . 'VALUES (null, :unIdMembre,:unMois, :unLibelle, :uneDateFr,'
            . ':unMontant) '
        );
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unLibelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':uneDateFr', $dateFr, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMontant', $montant, PDO::PARAM_INT);
        $requetePrepare->execute();
    }

    /**
     * Met à jour le frais hors forfait dont l'id et la valeur sont passés en arguments
     *
     * @param String $idFrais ID du frais
     * @param String $libelle Libellé du frais
     *
     * @return null
     */
    public function majFraisHorsForfait($idFrais, $libelle)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'UPDATE lignefraishorsforfait '
            . 'SET libelle = :libelle '
            . 'WHERE id = :unIdFrais'
        );
        $requetePrepare->bindParam(':libelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_INT);
        $requetePrepare->execute();
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     *
     * @param String $idFrais ID du frais
     *
     * @return null
     */
    public function supprimerFraisHorsForfait($idFrais)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'DELETE FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.id = :unIdFrais'
        );
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne les mois pour lesquel un membre a une fiche de frais
     *
     * @param String $idMembre l'id du Membre
     * @param String $idEtat Etat de la fiche, Clôturée par défaut
     *
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getLesMoisDisponibles($idMembre = false, $idEtat = false)
    {
        if(!$idMembre && $idEtat) {
            $requetePrepare = PdoGSB::$monPdo->prepare(
                'SELECT DISTINCT fichefrais.mois AS mois FROM fichefrais '
                . 'WHERE fichefrais.idetat = :unIdEtat '
                . 'ORDER BY fichefrais.mois DESC'
            );
            $requetePrepare->bindParam(':unIdEtat', $idEtat, PDO::PARAM_STR);
            $requetePrepare->execute();
        } elseif($idMembre && !$idEtat) {
            $requetePrepare = PdoGSB::$monPdo->prepare(
                'SELECT DISTINCT fichefrais.mois AS mois FROM fichefrais '
                . 'WHERE fichefrais.idmembre = :unIdMembre '
                . 'ORDER BY fichefrais.mois DESC'
            );
            $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
            $requetePrepare->execute();
        } else {
            $requetePrepare = PdoGSB::$monPdo->prepare(
                'SELECT DISTINCT fichefrais.mois AS mois FROM fichefrais '
                . 'WHERE fichefrais.idetat = :unIdEtat '
                . 'AND fichefrais.idmembre = :unIdMembre '
                . 'ORDER BY fichefrais.mois DESC'
            );
            $requetePrepare->bindParam(':unIdEtat', $idEtat, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
        foreach($requetePrepare->fetchAll() as $laLigne) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois[] = array(
                'mois' => $mois,
                'numAnnee' => $numAnnee,
                'numMois' => $numMois
            );
        }

        if(isset($lesMois)) {
            return $lesMois;
        }
    }

    /**
     * Retourne les informations d'une fiche de frais d'un membre pour un
     * mois et un etat (facultatif) donnés
     *
     * @param String $idMembre   ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $idEtat     Etat de la fiche de frais (facultatif)
     *
     * @return un tableau avec des champs de jointure entre une fiche de frais
     *         et la ligne d'état
     */
    public function getLesInfosFicheFrais($idMembre, $mois, $idEtat = null)
    {
        $rawSql = 'SELECT fichefrais.idetat as idEtat, '
            . 'fichefrais.datemodif as dateModif, '
            . 'fichefrais.nbjustificatifs as nbJustificatifs, '
            . 'fichefrais.montantvalide as montantValide, '
            . 'fichefrais.mois as mois, '
            . 'fichefrais.idvehicule as idvehicule, '
            . 'etat.libelle as libEtat, '
            . 'membre.nom as nom, '
            . 'membre.prenom as prenom, '
            . 'vehicule.indemnitekm as indemnitekm '
            . 'FROM fichefrais '
            . 'INNER JOIN etat ON fichefrais.idetat = etat.id '
            . 'INNER JOIN membre ON fichefrais.idmembre = membre.id '
            . 'INNER JOIN vehicule ON fichefrais.idvehicule = vehicule.id '
            . 'WHERE fichefrais.idmembre = :unIdMembre '
            . 'AND fichefrais.mois = :unMois';

        if($idEtat) { $rawSql .= ' AND fichefrais.idetat = :unIdEtat'; }

        $requetePrepare = PdoGSB::$monPdo->prepare($rawSql);
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        if($idEtat) { $requetePrepare->bindParam(':unIdEtat', $idEtat, PDO::PARAM_STR); }
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();

        return $laLigne;
    }

    /**
     * Modifie le montant des frais validés pour un membre
     *
     * @param String $idMembre      ID du membre
     * @param String $mois          Mois sous la forme aaaamm
     * @param String $montantvalide Le montant à MAJ
     *
     * @return null
     */
    public function majFraisValideFicheFrais($idMembre, $mois, $montantvalide)
    {
      $requetePrepare = PdoGSB::$monPdo->prepare(
          'UPDATE ficheFrais '
          . 'SET montantvalide = :montantvalide '
          . 'WHERE fichefrais.idmembre = :unIdMembre '
          . 'AND fichefrais.mois = :unMois'
      );
      $requetePrepare->bindParam(':montantvalide', $montantvalide, PDO::PARAM_STR);
      $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
      $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
      $requetePrepare->execute();
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais.
     * Modifie le champ idEtat et met la date de modif à aujourd'hui.
     *
     * @param String $idMembre ID du membre
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $etat       Nouvel état de la fiche de frais
     *
     * @return null
     */
    public function majEtatFicheFrais($idMembre, $mois, $etat)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'UPDATE ficheFrais '
            . 'SET idetat = :unEtat, datemodif = now() '
            . 'WHERE fichefrais.idmembre = :unIdMembre '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unEtat', $etat, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdMembre', $idMembre, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Récupère la la liste des membres pour le comptable
     *
     * @return array $lesVisiteurs Tableau contenant les infos des visiteurs
     */
    public function getListeVisiteurs()
    {
        $requetePrepare = PdoGSB::$monPdo->query(
            'SELECT membre.id AS id, '
            . 'membre.nom  AS nom, '
            . 'membre.prenom AS prenom '
            . 'FROM membre INNER JOIN rang '
            . 'ON membre.idrang = rang.id '
            . ' WHERE rang.libelle = "visiteur"'
        );

        return $requetePrepare->fetchAll();
    }

    /**
     * Récupère la liste des fiches de frais clôturées
     * @return array $listeSuiviFrais Tableau associatif à plusieurs dimensions
     * contenant toutes les fiches et ses infos rattachées
     */
    public function getListeFicheFraisCloturees()
    {
        $requetePrepare = PdoGSB::$monPdo->query(
            'SELECT membre.id  AS id, '
            . 'membre.nom  AS nom, '
            . 'membre.prenom AS prenom '
            . 'FROM membre JOIN fichefrais '
            . 'ON membre.id = fichefrais.idmembre '
            . 'WHERE fichefrais.idetat = "CL" '
            . 'GROUP BY membre.id'
        );

        return $requetePrepare->fetchAll(\PDO::FETCH_ASSOC|\PDO::FETCH_PROPS_LATE); // Utile pour que le GROUP BY fonctionne
    }

    /**
     * Récupère la liste des fiches de frais à mettre en paiement ou à rembourser
     * @return array $listeSuiviFrais Tableau associatif à plusieurs dimensions
     * contenant toutes les fiches et ses infos rattachées
     */
    public function getListeFicheFraisValidees()
    {
        $requetePrepare = PdoGSB::$monPdo->query(
            'SELECT fichefrais.mois  AS mois, '
            . 'membre.id  AS id, '
            . 'membre.nom  AS nom, '
            . 'membre.prenom AS prenom '
            . 'FROM fichefrais JOIN membre '
            . 'ON fichefrais.idmembre = membre.id '
            . 'WHERE fichefrais.idetat IN("VA", "PA")'
        );

        return $requetePrepare->fetchAll();
    }

    /**
     * Méthode permettant d'encrypter les mots de passe via BCRYPT dans la DB
     * On gardera une colonne "oldmdp" uniquement à des fins pédagogiques
     * En contexte réel aucun mdp ne sera sauvegardé en clair dans la DB
     *
     * @return null
     */
    public function securiseMotsDePasse() {
        /**
         * On désactive le processus d'arrêt de programme trop long
         * Le chiffrement étant justement très vorace en ressources il prend du temps
         */
        ini_set('max_execution_time', 0);

        $q1 = PDOGsb::$monPdo->query('SELECT * FROM membre');
        $data = $q1->fetchAll();
        foreach($data as $ligne) {
            $q2 = PDOGsb::$monPdo->prepare('UPDATE membre SET mdp = :unMdp WHERE id = :unId');

            /**
             * Chiffrement du mdp via l'algorithme Bcrypt
             * Cost = nb d'itérations (pourra être modifié au fur et à mesure de l'évolution des ressources du serveur)
             */
            $mdpSecurise = password_hash($ligne['ancienmdp'], PASSWORD_BCRYPT, ['cost' => PDOGsb::$cost]);

            $q2->bindParam(':unMdp', $mdpSecurise);
            $q2->bindParam(':unId', $ligne['id']);

            $q2->execute();

            $q1->closeCursor();
            $q2->closeCursor();
        }
    }
}
