-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 22. Feb 2024 um 21:08
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `flugplanung`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `chatbox`
--

CREATE TABLE `chatbox` (
  `pilot_id` int(11) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `dienste`
--

CREATE TABLE `dienste` (
  `flugtag` date DEFAULT NULL,
  `pilot_id` int(11) DEFAULT NULL,
  `windenfahrer` tinyint(1) NOT NULL,
  `startleiter` tinyint(1) NOT NULL,
  `id` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dienste_wuensche`
--

CREATE TABLE `dienste_wuensche` (
  `pilot_id` int(11) NOT NULL DEFAULT 0,
  `datum` date NOT NULL,
  `wunsch` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mitglieder`
--

CREATE TABLE `mitglieder` (
  `pilot_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `verein` int(11) NOT NULL DEFAULT 0,
  `fluggeraet` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'G' COMMENT 'G = Gleitschirm, D = Drachen, S = Sonstiges, mehrfaches möglich',
  `windenfahrer` tinyint(1) NOT NULL,
  `dienste_admin` tinyint(1) DEFAULT 0,
  `password` varchar(64) DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `avatar` smallint(6) NOT NULL DEFAULT 1,
  `max_dienste_halbjahr` smallint(6) DEFAULT NULL COMMENT 'Maximum number of duties per half-year for windenfahrer, NULL = no limit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `flugtage`
--

CREATE TABLE `flugtage` (
  `datum` date NOT NULL,
  `aufbau` time NOT NULL DEFAULT '10:00:00',
  `betrieb_ngl` tinyint(1) DEFAULT 0,
  `betrieb_hrp` tinyint(1) NOT NULL DEFAULT 0,
  `betrieb_amd` tinyint(1) NOT NULL DEFAULT 0,
  `abgesagt` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tagesplanung`
--

CREATE TABLE `tagesplanung` (
  `pilot_id` int(11) NOT NULL,
  `Kommentar` varchar(128) NOT NULL,
  `NGL` int(11) NOT NULL,
  `HRP` int(11) NOT NULL,
  `AMD` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `flugtag` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `password_resets`
--

CREATE TABLE `password_resets` (
  `token` varchar(64) NOT NULL,
  `pilot_id` int(11) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`token`),
  KEY `idx_pilot_id` (`pilot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `chatbox`
--
ALTER TABLE `chatbox`
  ADD PRIMARY KEY (`datetime`);

--
-- Indizes für die Tabelle `dienste`
--
ALTER TABLE `dienste`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `dienste_wuensche`
--
ALTER TABLE `dienste_wuensche`
  ADD PRIMARY KEY (`pilot_id`,`datum`);

--
-- Indizes für die Tabelle `mitglieder`
--
ALTER TABLE `mitglieder`
  ADD PRIMARY KEY (`pilot_id`);

--
-- Indizes für die Tabelle `flugtage`
--
ALTER TABLE `flugtage`
  ADD PRIMARY KEY (`datum`);

--
-- Indizes für die Tabelle `tagesplanung`
--
ALTER TABLE `tagesplanung`
  ADD PRIMARY KEY (`flugtag`,`pilot_id`) USING BTREE;

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `mitglieder`
--
ALTER TABLE `mitglieder`
  MODIFY `pilot_id` int(11) NOT NULL AUTO_INCREMENT;
 
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `reparaturen`
--

CREATE TABLE `reparaturen` (
  `key` int(11) NOT NULL,
  `fluggebiet` enum('HRP','NGL','AMD') NOT NULL,
  `text` longtext NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT 0,
  `closed` tinyint(1) NOT NULL DEFAULT 0,
  `solvedText` longtext DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indizes für die Tabelle `reparaturen`
--
ALTER TABLE `reparaturen`
  ADD PRIMARY KEY (`key`);

--
-- AUTO_INCREMENT für Tabelle `reparaturen`
--
ALTER TABLE `reparaturen`
  MODIFY `key` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
