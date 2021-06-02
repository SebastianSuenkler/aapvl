-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 14. Aug 2018 um 11:16
-- Server-Version: 10.1.28-MariaDB
-- PHP-Version: 5.6.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `aapvl_install_mysql`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cases`
--

CREATE TABLE `cases` (
  `pk_cases` int(10) UNSIGNED NOT NULL COMMENT 'PK for caces',
  `id_cases` varchar(255) DEFAULT NULL COMMENT 'Assigned Case name',
  `type_cases` smallint(5) UNSIGNED NOT NULL COMMENT 'Type of case (so far 1 = Unternehmensrecherche, 2 = Auftragsrecherche, 3 = Logoerkennung)',
  `sub_type_cases` smallint(5) DEFAULT NULL COMMENT 'subtype of cases (e. g. Unternehmensreche = Lebensmittel, Futtermittel; Logoerkennung = Bio, Herkunftsangaben',
  `fk_users` smallint(5) UNSIGNED NOT NULL COMMENT 'Id of User who openend the case',
  `comment_cases` varchar(1024) DEFAULT NULL COMMENT 'Comment for case',
  `date_cases` date NOT NULL COMMENT 'Date when the case was opened',
  `ip_cases` varchar(64) NOT NULL COMMENT 'IP of user who opened the case',
  `config_json_cases` varchar(1024) NOT NULL COMMENT 'JSON Object for configuartions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table for Cases';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `jobs_scrapers`
--

CREATE TABLE `jobs_scrapers` (
  `pk_scrapers` int(11) NOT NULL COMMENT 'ID for Table "scrapers"',
  `fk_cases` int(11) NOT NULL COMMENT 'FK to table "cases"',
  `fk_queries` int(11) NOT NULL COMMENT 'FK to table "queries"',
  `name_scrapers` varchar(255) NOT NULL COMMENT 'Name of Scraper',
  `query_queries` varchar(255) NOT NULL COMMENT 'Query from Table queries',
  `searchstring_scrapers` varchar(255) NOT NULL COMMENT 'Searchstring to scrape results',
  `xpath_results_scrapers` varchar(255) NOT NULL COMMENT 'xpath to scrape the content',
  `next_serp_scrapers` varchar(255) NOT NULL COMMENT 'XPATH to get next serp',
  `date_scrapers` varchar(32) DEFAULT NULL COMMENT 'Date when scraping process shall start',
  `status_scrapers` int(11) DEFAULT NULL COMMENT 'Status of job (NULL = not assigned, 1 = done, 0 = active)',
  `max_pages` tinyint(4) DEFAULT NULL,
  `attempts` tinyint(4) DEFAULT NULL COMMENT 'Counter for attempts. If counter = 3: scraping_status = 1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Job Tabelle für die Scraper';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `jobs_to_assign`
--

CREATE TABLE `jobs_to_assign` (
  `pk_jobs` int(11) NOT NULL COMMENT 'Primary key of jobs table',
  `status_jobs` tinyint(4) DEFAULT NULL COMMENT 'Status of job (active, finished)',
  `modules_jobs` varchar(255) NOT NULL COMMENT 'Analysis modules for the job separated by comma',
  `path_resources` varchar(255) NOT NULL COMMENT 'Path to resources to work on',
  `screenshot_resources` varchar(255) DEFAULT NULL,
  `fk_resources` int(11) NOT NULL,
  `parent_resources` int(11) DEFAULT NULL COMMENT 'Foreign Key of parent domain',
  `url_resources` varchar(255) NOT NULL COMMENT 'web url of resources',
  `fk_cases` int(11) NOT NULL COMMENT 'Foreign key to table cases for results report'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Job Tabelle für das Backend';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `jobs_urls`
--

CREATE TABLE `jobs_urls` (
  `pk_jobs_urls` int(11) NOT NULL,
  `fk_cases` int(11) NOT NULL,
  `fk_urls` int(11) NOT NULL,
  `elements_urls_path` text NOT NULL,
  `date_jobs_urls` varchar(32) NOT NULL,
  `status_jobs_urls` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_results`
--

CREATE TABLE `log_results` (
  `id_results_log` int(11) NOT NULL,
  `results` int(11) NOT NULL,
  `users` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `in_progress` int(1) NOT NULL,
  `manual_judgement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Logdaten für die Bewertungen';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_users`
--

CREATE TABLE `log_users` (
  `pk_log_users` smallint(6) NOT NULL COMMENT 'PK of table',
  `pk_users_log_users` int(11) NOT NULL COMMENT 'PK of users table as foreign key',
  `ip_log_users` varchar(64) NOT NULL COMMENT 'Extracted IP Adress',
  `timestamp_log_users` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of last login'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Logfiles zu der Nutzung des Tools';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `queries`
--

CREATE TABLE `queries` (
  `pk_queries` int(11) UNSIGNED NOT NULL COMMENT 'Primary Key for table',
  `query_queries` varchar(255) NOT NULL COMMENT 'Search terms for a query',
  `date_queries` date NOT NULL COMMENT 'Date when query shall be conducted',
  `fk_cases` smallint(5) UNSIGNED NOT NULL COMMENT 'Foreign key to cases table',
  `interval_days_queries` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Interval in days to repeat search query',
  `interval_completion_queries` varchar(16) DEFAULT NULL COMMENT 'Limit of repetition'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabelle für die Suchanfragen';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `resources`
--

CREATE TABLE `resources` (
  `pk_resources` int(11) UNSIGNED NOT NULL,
  `parent_resources` int(11) UNSIGNED NOT NULL,
  `url_resources` varchar(255) NOT NULL,
  `protocol_resources` varchar(15) NOT NULL,
  `date_resources` date NOT NULL,
  `html_resources` varchar(1024) DEFAULT NULL,
  `screenshot_resources` varchar(255) DEFAULT NULL,
  `contact_crawler_progress` tinyint(4) DEFAULT NULL,
  `ip_resources` varchar(255) DEFAULT NULL,
  `screenshot_date_resources` date DEFAULT NULL,
  `resources_progress` int(11) DEFAULT NULL,
  `fk_cases` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabelle für die gespeicherten Webseiten';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `results`
--

CREATE TABLE `results` (
  `pk_results` int(11) NOT NULL COMMENT 'Primary key of table results',
  `analysis_results` text COMMENT 'Analysis results of text analysis in JSON',
  `manual_results` text COMMENT 'Manual judgement (standard value is a copy of auto analysis) ',
  `validation_results` tinyint(2) NOT NULL COMMENT 'Flag, if result need to be validated',
  `path_resources` varchar(255) NOT NULL COMMENT 'path to resources to work on',
  `screenshot_resources` varchar(255) DEFAULT NULL,
  `fk_resources` int(11) NOT NULL,
  `parent_resources` int(11) DEFAULT NULL COMMENT 'FK of parent resources',
  `url_resources` varchar(255) NOT NULL COMMENT 'web url of resources',
  `fk_cases` int(11) DEFAULT NULL COMMENT 'Foreign Key to table cases',
  `updated_results` tinyint(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Metatabelle für die Analyseergebnisse';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `urls`
--

CREATE TABLE `urls` (
  `pk_urls` int(11) NOT NULL COMMENT 'PK for the table',
  `id_urls` varchar(255) NOT NULL COMMENT 'Name for the url list',
  `date_urls` date NOT NULL COMMENT 'Date when urls shall be crawled',
  `fk_cases` smallint(5) NOT NULL COMMENT 'Foreign key to cases table',
  `interval_days_urls` smallint(5) NOT NULL DEFAULT '0' COMMENT 'Interval in days to repeat url crawling',
  `interval_completion_urls` varchar(16) DEFAULT NULL COMMENT 'Limit of repetition',
  `elements_urls_path` text COMMENT 'Path to JSON Object file'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `pk_users` smallint(6) NOT NULL COMMENT 'PK for users table',
  `name_users` varchar(255) NOT NULL COMMENT 'Username',
  `password_users` varchar(255) NOT NULL COMMENT 'Password',
  `mail_users` varchar(255) DEFAULT NULL COMMENT 'E-Mail',
  `symbol_users` varchar(32) NOT NULL COMMENT 'Symbol for the user',
  `first_name_users` varchar(255) DEFAULT NULL COMMENT 'First name',
  `last_name_users` varchar(255) DEFAULT NULL COMMENT 'Last name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table for users of the app';

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`pk_users`, `name_users`, `password_users`, `mail_users`, `symbol_users`, `first_name_users`, `last_name_users`) VALUES
(12, 'test', 'test', NULL, 'tt', NULL, NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`pk_cases`);

--
-- Indizes für die Tabelle `jobs_scrapers`
--
ALTER TABLE `jobs_scrapers`
  ADD PRIMARY KEY (`pk_scrapers`),
  ADD UNIQUE KEY `fk_cases` (`fk_cases`,`fk_queries`,`name_scrapers`,`date_scrapers`);

--
-- Indizes für die Tabelle `jobs_to_assign`
--
ALTER TABLE `jobs_to_assign`
  ADD PRIMARY KEY (`pk_jobs`);

--
-- Indizes für die Tabelle `jobs_urls`
--
ALTER TABLE `jobs_urls`
  ADD PRIMARY KEY (`pk_jobs_urls`),
  ADD UNIQUE KEY `fk_cases` (`fk_cases`,`fk_urls`,`date_jobs_urls`);

--
-- Indizes für die Tabelle `log_results`
--
ALTER TABLE `log_results`
  ADD PRIMARY KEY (`id_results_log`);

--
-- Indizes für die Tabelle `log_users`
--
ALTER TABLE `log_users`
  ADD PRIMARY KEY (`pk_log_users`);

--
-- Indizes für die Tabelle `queries`
--
ALTER TABLE `queries`
  ADD PRIMARY KEY (`pk_queries`),
  ADD UNIQUE KEY `UC_Queries` (`query_queries`,`date_queries`);

--
-- Indizes für die Tabelle `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`pk_resources`),
  ADD UNIQUE KEY `resources_unique` (`url_resources`,`date_resources`,`fk_cases`);

--
-- Indizes für die Tabelle `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`pk_results`);

--
-- Indizes für die Tabelle `urls`
--
ALTER TABLE `urls`
  ADD PRIMARY KEY (`pk_urls`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`pk_users`),
  ADD UNIQUE KEY `symbol_users` (`symbol_users`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cases`
--
ALTER TABLE `cases`
  MODIFY `pk_cases` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK for caces', AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT für Tabelle `jobs_scrapers`
--
ALTER TABLE `jobs_scrapers`
  MODIFY `pk_scrapers` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID for Table "scrapers"', AUTO_INCREMENT=6222;

--
-- AUTO_INCREMENT für Tabelle `jobs_to_assign`
--
ALTER TABLE `jobs_to_assign`
  MODIFY `pk_jobs` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key of jobs table', AUTO_INCREMENT=313414;

--
-- AUTO_INCREMENT für Tabelle `jobs_urls`
--
ALTER TABLE `jobs_urls`
  MODIFY `pk_jobs_urls` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `log_results`
--
ALTER TABLE `log_results`
  MODIFY `id_results_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `log_users`
--
ALTER TABLE `log_users`
  MODIFY `pk_log_users` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'PK of table', AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT für Tabelle `queries`
--
ALTER TABLE `queries`
  MODIFY `pk_queries` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key for table', AUTO_INCREMENT=2301;

--
-- AUTO_INCREMENT für Tabelle `resources`
--
ALTER TABLE `resources`
  MODIFY `pk_resources` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=686517;

--
-- AUTO_INCREMENT für Tabelle `results`
--
ALTER TABLE `results`
  MODIFY `pk_results` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key of table results', AUTO_INCREMENT=311428;

--
-- AUTO_INCREMENT für Tabelle `urls`
--
ALTER TABLE `urls`
  MODIFY `pk_urls` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK for the table', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `pk_users` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'PK for users table', AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
