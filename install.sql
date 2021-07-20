-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 20. Dez 2018 um 16:14
-- Server-Version: 10.1.26-MariaDB-0+deb9u1
-- PHP-Version: 7.0.33-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `torstatus`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Bandwidth`
--

CREATE TABLE `Bandwidth` (
  `id` int(11) NOT NULL,
  `fingerprint` tinytext NOT NULL,
  `write` blob NOT NULL,
  `read` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Bandwidth1`
--

CREATE TABLE `Bandwidth1` (
  `id` int(11) NOT NULL,
  `fingerprint` tinytext NOT NULL,
  `write` blob NOT NULL,
  `read` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Bandwidth2`
--

CREATE TABLE `Bandwidth2` (
  `id` int(11) NOT NULL,
  `fingerprint` tinytext NOT NULL,
  `write` blob NOT NULL,
  `read` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Descriptor1`
--

CREATE TABLE `Descriptor1` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Fingerprint` varchar(256) DEFAULT NULL,
  `Name` varchar(256) DEFAULT NULL,
  `LastDescriptorPublished` datetime DEFAULT NULL,
  `IP` varchar(256) DEFAULT NULL,
  `ORPort` int(10) UNSIGNED DEFAULT NULL,
  `DirPort` int(10) UNSIGNED DEFAULT NULL,
  `Platform` varchar(256) DEFAULT NULL,
  `Contact` varchar(256) DEFAULT NULL,
  `Uptime` bigint(20) UNSIGNED DEFAULT NULL,
  `BandwidthMAX` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthBURST` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthOBSERVED` int(10) UNSIGNED DEFAULT NULL,
  `OnionKey` varchar(1024) DEFAULT NULL,
  `SigningKey` varchar(1024) DEFAULT NULL,
  `WriteHistoryLAST` datetime DEFAULT NULL,
  `WriteHistoryINC` int(10) UNSIGNED DEFAULT NULL,
  `WriteHistorySERDATA` text,
  `ReadHistoryLAST` datetime DEFAULT NULL,
  `ReadHistoryINC` int(10) UNSIGNED DEFAULT NULL,
  `ReadHistorySERDATA` text,
  `ExitPolicySERDATA` text,
  `FamilySERDATA` text,
  `Hibernating` tinyint(1) UNSIGNED DEFAULT NULL,
  `DescriptorSignature` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Descriptor2`
--

CREATE TABLE `Descriptor2` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Fingerprint` varchar(256) DEFAULT NULL,
  `Name` varchar(256) DEFAULT NULL,
  `LastDescriptorPublished` datetime DEFAULT NULL,
  `IP` varchar(256) DEFAULT NULL,
  `ORPort` int(10) UNSIGNED DEFAULT NULL,
  `DirPort` int(10) UNSIGNED DEFAULT NULL,
  `Platform` varchar(256) DEFAULT NULL,
  `Contact` varchar(256) DEFAULT NULL,
  `Uptime` bigint(20) UNSIGNED DEFAULT NULL,
  `BandwidthMAX` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthBURST` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthOBSERVED` int(10) UNSIGNED DEFAULT NULL,
  `OnionKey` varchar(1024) DEFAULT NULL,
  `SigningKey` varchar(1024) DEFAULT NULL,
  `WriteHistoryLAST` datetime DEFAULT NULL,
  `WriteHistoryINC` int(10) UNSIGNED DEFAULT NULL,
  `WriteHistorySERDATA` text,
  `ReadHistoryLAST` datetime DEFAULT NULL,
  `ReadHistoryINC` int(10) UNSIGNED DEFAULT NULL,
  `ReadHistorySERDATA` text,
  `ExitPolicySERDATA` text,
  `FamilySERDATA` text,
  `Hibernating` tinyint(1) UNSIGNED DEFAULT NULL,
  `DescriptorSignature` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hostnames`
--

CREATE TABLE `hostnames` (
  `id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `hostname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Mirrors`
--

CREATE TABLE `Mirrors` (
  `id` int(11) NOT NULL,
  `mirrors` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `missing_countries`
--

CREATE TABLE `missing_countries` (
  `country_code` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `missing_flags`
--

CREATE TABLE `missing_flags` (
  `country_code` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `NetworkStatus1`
--

CREATE TABLE `NetworkStatus1` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Fingerprint` varchar(256) DEFAULT NULL,
  `Name` varchar(256) DEFAULT NULL,
  `LastDescriptorPublished` datetime DEFAULT NULL,
  `DescriptorHash` varchar(256) DEFAULT NULL,
  `IP` varchar(256) DEFAULT NULL,
  `Hostname` varchar(256) DEFAULT NULL,
  `ORPort` int(10) UNSIGNED DEFAULT NULL,
  `DirPort` int(10) UNSIGNED DEFAULT NULL,
  `CountryCode` varchar(4) DEFAULT NULL,
  `FAuthority` tinyint(1) UNSIGNED DEFAULT NULL,
  `FBadDirectory` tinyint(1) UNSIGNED DEFAULT NULL,
  `FBadExit` tinyint(1) UNSIGNED DEFAULT NULL,
  `FExit` tinyint(1) UNSIGNED DEFAULT NULL,
  `FFast` tinyint(1) UNSIGNED DEFAULT NULL,
  `FGuard` tinyint(1) UNSIGNED DEFAULT NULL,
  `FNamed` tinyint(1) UNSIGNED DEFAULT NULL,
  `FStable` tinyint(1) UNSIGNED DEFAULT NULL,
  `FRunning` tinyint(1) UNSIGNED DEFAULT NULL,
  `FValid` tinyint(1) UNSIGNED DEFAULT NULL,
  `FV2Dir` tinyint(1) UNSIGNED DEFAULT NULL,
  `FHSDir` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `NetworkStatus2`
--

CREATE TABLE `NetworkStatus2` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Fingerprint` varchar(256) DEFAULT NULL,
  `Name` varchar(256) DEFAULT NULL,
  `LastDescriptorPublished` datetime DEFAULT NULL,
  `DescriptorHash` varchar(256) DEFAULT NULL,
  `IP` varchar(256) DEFAULT NULL,
  `Hostname` varchar(256) DEFAULT NULL,
  `ORPort` int(10) UNSIGNED DEFAULT NULL,
  `DirPort` int(10) UNSIGNED DEFAULT NULL,
  `CountryCode` varchar(4) DEFAULT NULL,
  `FAuthority` tinyint(1) UNSIGNED DEFAULT NULL,
  `FBadDirectory` tinyint(1) UNSIGNED DEFAULT NULL,
  `FBadExit` tinyint(1) UNSIGNED DEFAULT NULL,
  `FExit` tinyint(1) UNSIGNED DEFAULT NULL,
  `FFast` tinyint(1) UNSIGNED DEFAULT NULL,
  `FGuard` tinyint(1) UNSIGNED DEFAULT NULL,
  `FNamed` tinyint(1) UNSIGNED DEFAULT NULL,
  `FStable` tinyint(1) UNSIGNED DEFAULT NULL,
  `FRunning` tinyint(1) UNSIGNED DEFAULT NULL,
  `FValid` tinyint(1) UNSIGNED DEFAULT NULL,
  `FV2Dir` tinyint(1) UNSIGNED DEFAULT NULL,
  `FHSDir` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `NetworkStatusSource`
--

CREATE TABLE `NetworkStatusSource` (
  `ID` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `Fingerprint` varchar(256) DEFAULT NULL,
  `Name` varchar(256) DEFAULT NULL,
  `LastDescriptorPublished` datetime DEFAULT NULL,
  `IP` varchar(256) DEFAULT NULL,
  `ORPort` int(10) UNSIGNED DEFAULT NULL,
  `DirPort` int(10) UNSIGNED DEFAULT NULL,
  `Platform` varchar(256) DEFAULT NULL,
  `Contact` varchar(256) DEFAULT NULL,
  `Uptime` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthMAX` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthBURST` int(10) UNSIGNED DEFAULT NULL,
  `BandwidthOBSERVED` int(10) UNSIGNED DEFAULT NULL,
  `OnionKey` varchar(1024) DEFAULT NULL,
  `SigningKey` varchar(1024) DEFAULT NULL,
  `WriteHistoryLAST` datetime DEFAULT NULL,
  `WriteHistoryINC` int(10) UNSIGNED DEFAULT NULL,
  `WriteHistorySERDATA` varchar(8192) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `ReadHistoryLAST` datetime DEFAULT NULL,
  `ReadHistoryINC` int(10) UNSIGNED DEFAULT NULL,
  `ReadHistorySERDATA` varchar(8192) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `ExitPolicySERDATA` varchar(8192) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `FamilySERDATA` varchar(8192) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `Hibernating` tinyint(1) UNSIGNED DEFAULT NULL,
  `DescriptorSignature` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Status`
--

CREATE TABLE `Status` (
  `ID` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateElapsed` int(10) UNSIGNED DEFAULT NULL,
  `ActiveNetworkStatusTable` varchar(256) DEFAULT NULL,
  `ActiveDescriptorTable` varchar(256) DEFAULT NULL,
  `ActiveORAddressesTable` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ORAddresses1`
--

CREATE TABLE `ORAddresses1` (
  `id` int(11) NOT NULL,
  `descriptor_id` int(11) NOT NULL,
  `address` varchar(100) NOT NULL,
  `port` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ORAddresses2`
--

CREATE TABLE `ORAddresses2` (
  `id` int(11) NOT NULL,
  `descriptor_id` int(11) NOT NULL,
  `address` varchar(100) NOT NULL,
  `port` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Bandwidth`
--
ALTER TABLE `Bandwidth`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Bandwidth1`
--
ALTER TABLE `Bandwidth1`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Bandwidth2`
--
ALTER TABLE `Bandwidth2`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Descriptor1`
--
ALTER TABLE `Descriptor1`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Index_Fingerprint` (`Fingerprint`),
  ADD KEY `Index_Bandwidth` (`BandwidthOBSERVED`),
  ADD KEY `Index_Uptime` (`Uptime`),
  ADD KEY `Index_Platform` (`Platform`),
  ADD KEY `Index_Contact` (`Contact`),
  ADD KEY `Name` (`Name`);

--
-- Indizes für die Tabelle `Descriptor2`
--
ALTER TABLE `Descriptor2`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Index_Fingerprint` (`Fingerprint`),
  ADD KEY `Index_Bandwidth` (`BandwidthOBSERVED`),
  ADD KEY `Index_Uptime` (`Uptime`),
  ADD KEY `Index_Platform` (`Platform`),
  ADD KEY `Index_Contact` (`Contact`),
  ADD KEY `Name` (`Name`);

--
-- Indizes für die Tabelle `hostnames`
--
ALTER TABLE `hostnames`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostnames_ip_idx` (`ip`);

--
-- Indizes für die Tabelle `Mirrors`
--
ALTER TABLE `Mirrors`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `missing_countries`
--
ALTER TABLE `missing_countries`
  ADD PRIMARY KEY (`country_code`),
  ADD UNIQUE KEY `country_code` (`country_code`);

--
-- Indizes für die Tabelle `missing_flags`
--
ALTER TABLE `missing_flags`
  ADD PRIMARY KEY (`country_code`);

--
-- Indizes für die Tabelle `NetworkStatus1`
--
ALTER TABLE `NetworkStatus1`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Index_Fingerprint` (`Fingerprint`),
  ADD KEY `Index_Name` (`Name`),
  ADD KEY `Index_CountryCode` (`CountryCode`),
  ADD KEY `Index_LastDescriptorPublished` (`LastDescriptorPublished`),
  ADD KEY `Index_IP` (`IP`),
  ADD KEY `Index_Hostname` (`Hostname`),
  ADD KEY `Index_ORPort` (`ORPort`),
  ADD KEY `Index_DirPort` (`DirPort`);

--
-- Indizes für die Tabelle `NetworkStatus2`
--
ALTER TABLE `NetworkStatus2`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Index_Fingerprint` (`Fingerprint`),
  ADD KEY `Index_Name` (`Name`),
  ADD KEY `Index_CountryCode` (`CountryCode`),
  ADD KEY `Index_LastDescriptorPublished` (`LastDescriptorPublished`),
  ADD KEY `Index_IP` (`IP`),
  ADD KEY `Index_Hostname` (`Hostname`),
  ADD KEY `Index_ORPort` (`ORPort`),
  ADD KEY `Index_DirPort` (`DirPort`);

--
-- Indizes für die Tabelle `NetworkStatusSource`
--
ALTER TABLE `NetworkStatusSource`
  ADD PRIMARY KEY (`ID`);

--
-- Indizes für die Tabelle `ORAddresses1`
--
ALTER TABLE `ORAddresses1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address` (`address`),
  ADD KEY `descriptor_id` (`descriptor_id`);

--
-- Indizes für die Tabelle `ORAddresses2`
--
ALTER TABLE `ORAddresses1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address` (`address`),
  ADD KEY `descriptor_id` (`descriptor_id`);

--
-- Indizes für die Tabelle `Status`
--
ALTER TABLE `Status`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `Bandwidth`
--
ALTER TABLE `Bandwidth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `Bandwidth1`
--
ALTER TABLE `Bandwidth1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `Bandwidth2`
--
ALTER TABLE `Bandwidth2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `Descriptor1`
--
ALTER TABLE `Descriptor1`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `Descriptor2`
--
ALTER TABLE `Descriptor2`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hostnames`
--
ALTER TABLE `hostnames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `Mirrors`
--
ALTER TABLE `Mirrors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `NetworkStatus1`
--
ALTER TABLE `NetworkStatus1`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `NetworkStatus2`
--
ALTER TABLE `NetworkStatus2`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ORAddresses1`
--
ALTER TABLE `ORAddresses1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ORAddresses2`
--
ALTER TABLE `ORAddresses2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- 
-- Insert the required rows into the database
--
INSERT INTO `NetworkStatusSource` (`ID`,`Fingerprint`,`Name`,`LastDescriptorPublished`,`IP`,`ORPort`,`DirPort`,`Platform`,`Contact`,`Uptime`,`BandwidthMAX`,`BandwidthBURST`,`BandwidthOBSERVED`,`OnionKey`,`SigningKey`,`WriteHistoryLAST`,`WriteHistoryINC`,`WriteHistorySERDATA`,`ReadHistoryLAST`,`ReadHistoryINC`,`ReadHistorySERDATA`,`ExitPolicySERDATA`,`FamilySERDATA`,`Hibernating`,`DescriptorSignature`) VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

INSERT INTO `Status` (`ID`,`LastUpdate`,`LastUpdateElapsed`,`ActiveNetworkStatusTable`,`ActiveDescriptorTable`) VALUES (1,'2000-01-01 00:00:00',NULL,NULL,NULL);

INSERT INTO `Mirrors` (`id`,`mirrors`) VALUES (1,'');
