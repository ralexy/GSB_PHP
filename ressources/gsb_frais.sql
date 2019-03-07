-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 02, 2019 at 04:42 PM
-- Server version: 5.7.23
-- PHP Version: 7.1.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `gsb_frais`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `archiverFicheFrais` ()  MODIFIES SQL DATA
    COMMENT 'Disable old visitor sheets'
BEGIN
DECLARE v_numMois VARCHAR(6);
-- On récupère l'année et le mois courant sous la forme YYYYMM (comme dans nos deux tables ci-dessous)
	SELECT DATE_FORMAT(NOW(),'%Y%m') INTO v_numMois;
    
    -- On archive nos tuples si des fiches sont encore ouvertes du mois précédent
    UPDATE ficheFrais SET idetat = 'CL' WHERE mois < v_numMois;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `etat`
--

CREATE TABLE `etat` (
  `id` char(2) NOT NULL,
  `libelle` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `etat`
--

INSERT INTO `etat` (`id`, `libelle`) VALUES
('CL', 'Saisie clôturée'),
('CR', 'Fiche créée, saisie en cours'),
('PA', 'Mise en paiement'),
('RB', 'Remboursée'),
('VA', 'Validée');

-- --------------------------------------------------------

--
-- Table structure for table `fichefrais`
--

CREATE TABLE `fichefrais` (
  `idmembre` char(4) NOT NULL,
  `mois` char(6) NOT NULL,
  `nbjustificatifs` int(11) DEFAULT NULL,
  `montantvalide` decimal(10,2) DEFAULT NULL,
  `datemodif` date DEFAULT NULL,
  `idetat` char(2) DEFAULT 'CR',
  `idvehicule` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fichefrais`
--

INSERT INTO `fichefrais` (`idmembre`, `mois`, `nbjustificatifs`, `montantvalide`, `datemodif`, `idetat`, `idvehicule`) VALUES
('a131', '201901', 0, '0.00', '2019-02-01', 'CL', 1),
('a131', '201902', 0, '0.00', '2019-03-01', 'CL', 1),
('a131', '201903', 0, '0.00', '2019-03-01', 'CL', 3),
('a17', '201901', 0, '0.00', '2019-02-15', 'VA', 1),
('a17', '201902', 0, '0.00', '2019-02-14', 'RB', 1),
('a17', '201903', 2, '9800.00', '2019-02-27', 'VA', 1),
('a93', '201903', 1, '666.90', '2019-03-02', 'VA', 2),
('b16', '201902', 1, '120.00', '2019-02-14', 'RB', 1),
('b50', '201902', 0, '0.00', '2019-02-04', 'CL', 1),
('c3', '201901', 0, '16606.38', '2019-02-28', 'VA', 1),
('c3', '201902', 1, '0.00', '2019-02-21', 'VA', 1),
('c3', '201903', 1, '0.00', '2019-03-01', 'CL', 1),
('c3', '201904', 1, '0.00', '2019-03-02', 'VA', 1);

-- --------------------------------------------------------

--
-- Table structure for table `fraisforfait`
--

CREATE TABLE `fraisforfait` (
  `id` char(3) NOT NULL,
  `libelle` char(20) DEFAULT NULL,
  `montant` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fraisforfait`
--

INSERT INTO `fraisforfait` (`id`, `libelle`, `montant`) VALUES
('ETP', 'Forfait Etape', '110.00'),
('KM', 'Frais Kilométrique', '0.00'),
('NUI', 'Nuitée Hôtel', '80.00'),
('REP', 'Repas Restaurant', '25.00');

--
-- Triggers `fraisforfait`
--
DELIMITER $$
CREATE TRIGGER `archiverFicheFrais` BEFORE INSERT ON `fraisforfait` FOR EACH ROW CALL archiverFicheFrais()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lignefraisforfait`
--

CREATE TABLE `lignefraisforfait` (
  `idmembre` char(4) NOT NULL,
  `mois` char(6) NOT NULL,
  `idfraisforfait` char(3) NOT NULL,
  `quantite` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lignefraisforfait`
--

INSERT INTO `lignefraisforfait` (`idmembre`, `mois`, `idfraisforfait`, `quantite`) VALUES
('a131', '201901', 'ETP', 9),
('a131', '201901', 'KM', 10),
('a131', '201901', 'NUI', 10),
('a131', '201901', 'REP', 70),
('a131', '201902', 'ETP', 0),
('a131', '201902', 'KM', 0),
('a131', '201902', 'NUI', 0),
('a131', '201902', 'REP', 0),
('a131', '201903', 'ETP', 0),
('a131', '201903', 'KM', 55),
('a131', '201903', 'NUI', 0),
('a131', '201903', 'REP', 0),
('a17', '201901', 'ETP', 50),
('a17', '201901', 'KM', 49),
('a17', '201901', 'NUI', 0),
('a17', '201901', 'REP', 12),
('a17', '201902', 'ETP', 0),
('a17', '201902', 'KM', 0),
('a17', '201902', 'NUI', 0),
('a17', '201902', 'REP', 0),
('a17', '201903', 'ETP', 88),
('a17', '201903', 'KM', 0),
('a17', '201903', 'NUI', 0),
('a17', '201903', 'REP', 0),
('a93', '201903', 'ETP', 3),
('a93', '201903', 'KM', 55),
('a93', '201903', 'NUI', 1),
('a93', '201903', 'REP', 9),
('b16', '201902', 'ETP', 50),
('b16', '201902', 'KM', 99),
('b16', '201902', 'NUI', 20),
('b16', '201902', 'REP', 0),
('b50', '201902', 'ETP', 55),
('b50', '201902', 'KM', 99),
('b50', '201902', 'NUI', 22),
('b50', '201902', 'REP', 0),
('c3', '201901', 'ETP', 55),
('c3', '201901', 'KM', 99),
('c3', '201901', 'NUI', 119),
('c3', '201901', 'REP', 39),
('c3', '201902', 'ETP', 0),
('c3', '201902', 'KM', 0),
('c3', '201902', 'NUI', 0),
('c3', '201902', 'REP', 0),
('c3', '201903', 'ETP', 0),
('c3', '201903', 'KM', 55),
('c3', '201903', 'NUI', 0),
('c3', '201903', 'REP', 0),
('c3', '201904', 'ETP', 0),
('c3', '201904', 'KM', 0),
('c3', '201904', 'NUI', 0),
('c3', '201904', 'REP', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lignefraishorsforfait`
--

CREATE TABLE `lignefraishorsforfait` (
  `id` int(11) NOT NULL,
  `idmembre` char(4) NOT NULL,
  `mois` char(6) NOT NULL,
  `libelle` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lignefraishorsforfait`
--

INSERT INTO `lignefraishorsforfait` (`id`, `idmembre`, `mois`, `libelle`, `date`, `montant`) VALUES
(20, 'b16', '201902', 'Repas d&#39;affaires M DUPONT', '2019-02-03', '120.00'),
(21, 'b50', '201902', 'Test', '2019-01-01', '201.00'),
(25, 'a93', '201903', 'Repas avec la Princesse de Monaco', '2019-01-25', '219.00'),
(26, 'c3', '201904', 'REFUSE : Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget(...)', '2019-04-01', '89.00');

-- --------------------------------------------------------

--
-- Table structure for table `membre`
--

CREATE TABLE `membre` (
  `id` char(4) NOT NULL,
  `nom` char(30) DEFAULT NULL,
  `prenom` char(30) DEFAULT NULL,
  `login` char(20) DEFAULT NULL,
  `mdp` char(60) NOT NULL,
  `ancienmdp` char(20) DEFAULT NULL,
  `adresse` char(30) DEFAULT NULL,
  `cp` char(5) DEFAULT NULL,
  `ville` char(30) DEFAULT NULL,
  `dateembauche` date DEFAULT NULL,
  `idrang` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `membre`
--

INSERT INTO `membre` (`id`, `nom`, `prenom`, `login`, `mdp`, `ancienmdp`, `adresse`, `cp`, `ville`, `dateembauche`, `idrang`) VALUES
('a131', 'Villechalane', 'Louis', 'comptable', '$2a$12$cmdpJ4SYnllZiSyRaX.PWeV9DyHqogpuZZhImSdBN6Y41g0UFKi0a', 'comptable', '8 rue des Charmes', '46000', 'Cahors', '2005-12-21', 2),
('a17', 'Andre', 'David', 'dandre', '$2y$12$aWPGbix3UsWC0i3z5dcjAk.Gwc6FemyPJBB3O8isGmHHV36G4wQL8G', 'oppg5', '1 rue Petit', '46200', 'Lalbenque', '1998-11-23', 1),
('a55', 'Bedos', 'Christian', 'cbedos', '$2y$12$7m1EjR9yOJ/pHIxoLVDkVOKHElmw0npgeVyDVxH.3LksLtlqATRHe', 'gmhxd', '1 rue Peranud', '46250', 'Montcuq', '1995-01-12', 1),
('a93', 'Tusseau', 'Louis', 'ltusseau', '$2y$12$ttt8TYlrmW1Tu.9zlnO6puJoPMRQmRwKThKu15GowSHJsvkzbw33m', 'ktp3s', '22 rue des Ternes', '46123', 'Gramat', '2000-05-01', 1),
('b13', 'Bentot', 'Pascal', 'pbentot', '$2y$12$OU/OgIhiLjzyGFDX.2ciXOUFEdR3a/51inXX5MWUCuGEUrvamHZNO', 'doyw1', '11 allée des Cerises', '46512', 'Bessines', '1992-07-09', 1),
('b16', 'Bioret', 'Luc', 'lbioret', '$2y$12$n17ZwmPuWZVHebex.zUCyOxuLjuDEZMFEqq5XlS2jgZpRszUBudsm', 'hrjfs', '1 Avenue gambetta', '46000', 'Cahors', '1998-05-11', 1),
('b19', 'Bunisset', 'Francis', 'fbunisset', '$2y$12$aJE0KtI7pvokz6CrJeP24uq2JX82s3FzrJUiu8LoACyCbjZk6mmoG', '4vbnd', '10 rue des Perles', '93100', 'Montreuil', '1987-10-21', 1),
('b25', 'Bunisset', 'Denise', 'dbunisset', '$2y$12$q7BcTYA.KUBsjPyeeeACm.iNTe89YACxNgBYFDUuBmevzmz0Mqvya', 's1y1r', '23 rue Manin', '75019', 'paris', '2010-12-05', 1),
('b28', 'Cacheux', 'Bernard', 'bcacheux', '$2y$12$vNSBhq4nHH6Ff.uu63jKBOz/8o2rQ.VzU5.4xBFz1a9I4AadOYgCu', 'uf7r3', '114 rue Blanche', '75017', 'Paris', '2009-11-12', 1),
('b34', 'Cadic', 'Eric', 'ecadic', '$2y$12$jRum.JqUZIQLckmnlWsonOczC54Dd/ASAdQW63jKIUsPsB1awlOcq', '6u8dc', '123 avenue de la République', '75011', 'Paris', '2008-09-23', 1),
('b4', 'Charoze', 'Catherine', 'ccharoze', '$2y$12$9KLkWwd3bB.OXKOx53QB/uS2avBBGgQX.AZsEzY0PewMbiyBxPgCm', 'u817o', '100 rue Petit', '75019', 'Paris', '2005-11-12', 1),
('b50', 'Clepkens', 'Christophe', 'cclepkens', '$2y$12$pscjqFml/hO8c0B0HO0rc.WEVeToJmHdGNlz8qloVRANUeniU0WoS', 'bw1us', '12 allée des Anges', '93230', 'Romainville', '2003-08-11', 1),
('b59', 'Cottin', 'Vincenne', 'vcottin', '$2y$12$M//0Sj8a6KYQ.sHkNWcI8uNTnuh/ErJmyV984IxMSMxjyWEB8m3HK', '2hoh9', '36 rue Des Roches', '93100', 'Monteuil', '2001-11-18', 1),
('c14', 'Daburon', 'François', 'fdaburon', '$2y$12$ntzlD.MKNei2PlLvI4/8ru6rD4hwEE9GumvUGy3xtwSMoOn9famZm', '7oqpv', '13 rue de Chanzy', '94000', 'Créteil', '2002-02-11', 1),
('c3', 'De', 'Philippe', 'pde', '$2y$12$Fle68zABeIHUfHihJ0ZIyOjFKsdDMr9Iqa2RLXSr.AVH66Xjg8SCy', 'gk9kx', '13 rue Barthes', '94000', 'Créteil', '2010-12-14', 1),
('c54', 'Debelle', 'Michel', 'mdebelle', '$2y$12$3qJX4tcIugtmjE4hkAYjqeiZI3oGA4QXWabVcpe.X8kPLaWbfCl1G', 'od5rt', '181 avenue Barbusse', '93210', 'Rosny', '2006-11-23', 1),
('d13', 'Debelle', 'Jeanne', 'jdebelle', '$2y$12$xJJuP/LwTjw4oMQ1XDRaM.o2c4vLQeD2rqBVNXTG5UD1HJhrY/IqW', 'nvwqq', '134 allée des Joncs', '44000', 'Nantes', '2000-05-11', 1),
('d51', 'Debroise', 'Michel', 'mdebroise', '$2y$12$btazZIKtLbTIuBhbu7MUdePXqLup5fNa9xHBMh86x0WScGyRw714.', 'sghkb', '2 Bld Jourdain', '44000', 'Nantes', '2001-04-17', 1),
('e22', 'Desmarquest', 'Nathalie', 'ndesmarquest', '$2y$12$W1/5fiOsVyKLRJp5KYsFeeoX2.5tmHUFF/YhwN.8mPf9rzU1BGvsy', 'f1fob', '14 Place d Arc', '45000', 'Orléans', '2005-11-12', 1),
('e24', 'Desnost', 'Pierre', 'pdesnost', '$2y$12$hxicLv6Hl1hSom/8TaXKA.mioW7f8Wwerttn2CaLEr6yPfl2wvYUK', '4k2o5', '16 avenue des Cèdres', '23200', 'Guéret', '2001-02-05', 1),
('e39', 'Dudouit', 'Frédéric', 'fdudouit', '$2y$12$5do/pz47Pn6LrO09Hvn0sut.na93gDyYpO1qFDb3ZG59m/tB7zcm6', '44im8', '18 rue de l église', '23120', 'GrandBourg', '2000-08-01', 1),
('e49', 'Duncombe', 'Claude', 'cduncombe', '$2y$12$eMqXgMEsY1pVQ8Ztk9E6t.PNYYuKyURr6Ru7Vid00peDn4n5E.UG6', 'qf77j', '19 rue de la tour', '23100', 'La souteraine', '1987-10-10', 1),
('e5', 'Enault-Pascreau', 'Céline', 'cenault', '$2y$12$zRwvP/CS62QduBw5x94PP.1sTii50z1dIetZf55jQcB/MoQnnUw4W', 'y2qdu', '25 place de la gare', '23200', 'Gueret', '1995-09-01', 1),
('e52', 'Eynde', 'Valérie', 'veynde', '$2y$12$fwNqkI/wMCFqxaNSzS1aKuxcpYjpKdVzjbozr0i608wyV6R1ncys2', 'i7sn3', '3 Grand Place', '13015', 'Marseille', '1999-11-01', 1),
('f21', 'Finck', 'Jacques', 'jfinck', '$2y$12$4h6VhcQXLev59BM16koLaOoObyFZDVKvSiSvtvUeURzkZj8Gl6QJW', 'mpb3t', '10 avenue du Prado', '13002', 'Marseille', '2001-11-10', 1),
('f39', 'Frémont', 'Fernande', 'ffremont', '$2y$12$S0W/AY1sKCYlmxa6XY9.PuK/dK3Wnlqbnm0tuVwSSdpYVWeeknuDi', 'xs5tq', '4 route de la mer', '13012', 'Allauh', '1998-10-01', 1),
('f4', 'Gest', 'Alain', 'agest', '$2y$12$PJv/zZofUR/MqFBUK3FxUeWMk2ouCpGzzs2jtnkdkX1Lf2kB4JTNq', 'dywvt', '30 avenue de la mer', '13025', 'Berre', '1985-11-01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rang`
--

CREATE TABLE `rang` (
  `id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL DEFAULT 'visiteur'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rang`
--

INSERT INTO `rang` (`id`, `libelle`) VALUES
(1, 'visiteur'),
(2, 'comptable');

-- --------------------------------------------------------

--
-- Table structure for table `vehicule`
--

CREATE TABLE `vehicule` (
  `id` int(11) NOT NULL,
  `nom` varchar(25) NOT NULL,
  `indemnitekm` decimal(2,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vehicule`
--

INSERT INTO `vehicule` (`id`, `nom`, `indemnitekm`) VALUES
(1, '4CV Diesel', '0.52'),
(2, '5/6CV Diesel', '0.58'),
(3, '4CV Essence', '0.62'),
(4, '5/6CV Essence', '0.67');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `etat`
--
ALTER TABLE `etat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fichefrais`
--
ALTER TABLE `fichefrais`
  ADD PRIMARY KEY (`idmembre`,`mois`),
  ADD KEY `idetat` (`idetat`),
  ADD KEY `idvehicule` (`idvehicule`);

--
-- Indexes for table `fraisforfait`
--
ALTER TABLE `fraisforfait`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lignefraisforfait`
--
ALTER TABLE `lignefraisforfait`
  ADD PRIMARY KEY (`idmembre`,`mois`,`idfraisforfait`),
  ADD KEY `idfraisforfait` (`idfraisforfait`);

--
-- Indexes for table `lignefraishorsforfait`
--
ALTER TABLE `lignefraishorsforfait`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idvisiteur` (`idmembre`,`mois`);

--
-- Indexes for table `membre`
--
ALTER TABLE `membre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRang` (`idrang`);

--
-- Indexes for table `rang`
--
ALTER TABLE `rang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicule`
--
ALTER TABLE `vehicule`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lignefraishorsforfait`
--
ALTER TABLE `lignefraishorsforfait`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `rang`
--
ALTER TABLE `rang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicule`
--
ALTER TABLE `vehicule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fichefrais`
--
ALTER TABLE `fichefrais`
  ADD CONSTRAINT `fichefrais_ibfk_1` FOREIGN KEY (`idetat`) REFERENCES `etat` (`id`),
  ADD CONSTRAINT `fichefrais_ibfk_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`id`),
  ADD CONSTRAINT `fichefrais_ibfk_3` FOREIGN KEY (`idvehicule`) REFERENCES `vehicule` (`id`);

--
-- Constraints for table `lignefraisforfait`
--
ALTER TABLE `lignefraisforfait`
  ADD CONSTRAINT `lignefraisforfait_ibfk_1` FOREIGN KEY (`idmembre`,`mois`) REFERENCES `fichefrais` (`idmembre`, `mois`),
  ADD CONSTRAINT `lignefraisforfait_ibfk_2` FOREIGN KEY (`idfraisforfait`) REFERENCES `fraisforfait` (`id`);

--
-- Constraints for table `lignefraishorsforfait`
--
ALTER TABLE `lignefraishorsforfait`
  ADD CONSTRAINT `lignefraishorsforfait_ibfk_1` FOREIGN KEY (`idmembre`,`mois`) REFERENCES `fichefrais` (`idmembre`, `mois`);

--
-- Constraints for table `membre`
--
ALTER TABLE `membre`
  ADD CONSTRAINT `membre_ibfk_1` FOREIGN KEY (`idrang`) REFERENCES `rang` (`id`);
