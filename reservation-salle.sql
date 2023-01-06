--
-- Base de données : `reservation-salle`
--

CREATE DATABASE reservation-salle;

-- --------------------------------------------------------

--
-- Structure de la table `demande_reservation_salle`
--

CREATE TABLE `demande_reservation_salle` (
  `code` bigint(20) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `date_demande` datetime NOT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `itemid` varchar(255) DEFAULT NULL,
  `cb` varchar(255) DEFAULT NULL,
  `requestid` varchar(255) DEFAULT NULL,
  `erreur` varchar(2048) DEFAULT NULL,
  `bsupprime` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour la table `demande_reservation_salle`
--
ALTER TABLE `demande_reservation_salle`
  ADD PRIMARY KEY (`code`);
