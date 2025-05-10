-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 09 mai 2025 à 18:14
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dinner`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `genre` enum('H','F') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `genre`, `photo`, `user_id`) VALUES
(5, 'dantouma', 'H', 'photo_6814e92ec1d3d.png', 1),
(6, 'ABib', 'H', 'photo_6814ebd8c14c6.jpg', 3),
(7, 'bob', 'F', 'photo_6818828971ca0.png', 4);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `phone`, `password`, `created_at`) VALUES
(1, 'Dantouma', '778730944', '$2y$10$2eFcrvr9J5r8.xu0bBtV1OApTIGDgZZJ7XSJyu2fjcoF6OK8anz2a', '2025-05-01 21:51:11'),
(2, 'Abib', '778730945', '$2y$10$mvOSjifJ3Ul9fBhQpuLFYOkU.D/IGM9G0WWGJ9RS6aseYpsPCGyCe', '2025-05-02 11:17:43'),
(3, 'test', '778730946', '$2y$10$HAfQ79HcHveR60sm6w4OVOjM34FnLgFUMRyIn7EP5KQwYfLbscaNm', '2025-05-02 15:53:53'),
(4, 'bob', '778730947', '$2y$10$gHZfhkoqyrGrCMQt1WjP.uwljmIcYls/qTu4tsfXk60CCKHsbKx86', '2025-05-05 09:18:02'),
(5, 'Mafe', '777777777', '$2y$10$i09fYfhoDHSomokhlcKOveNMbTyAj8WSMkB5rUhcXMiOTdBQr.9Yq', '2025-05-09 15:48:39');

-- --------------------------------------------------------

--
-- Structure de la table `vote`
--

CREATE TABLE `vote` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `vote`
--

INSERT INTO `vote` (`id`, `user_id`, `candidate_id`, `voted_at`) VALUES
(32, 2, 5, '2025-05-02 15:53:06'),
(46, 3, 5, '2025-05-04 16:21:38'),
(48, 3, 6, '2025-05-04 16:21:40'),
(64, 4, 5, '2025-05-09 14:59:03'),
(66, 4, 6, '2025-05-09 14:59:06'),
(76, 1, 6, '2025-05-09 15:40:30'),
(78, 1, 7, '2025-05-09 15:40:33'),
(79, 5, 5, '2025-05-09 15:49:07'),
(80, 5, 7, '2025-05-09 15:49:12');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Index pour la table `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`user_id`,`candidate_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `vote`
--
ALTER TABLE `vote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vote_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
