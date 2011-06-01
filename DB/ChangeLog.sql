-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 19. April 2006 um 14:07
-- Server Version: 4.0.24
-- PHP-Version: 4.3.10-15
-- 
-- Datenbank: `Himmel`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `ChangeLog`
-- 

DROP TABLE IF EXISTS `ChangeLog`;
CREATE TABLE IF NOT EXISTS `ChangeLog` (
  `Time` timestamp NOT NULL,
  `UID` int(11) NOT NULL default 0,
  `Commend` text NOT NULL,
  `SQLCommad` text NOT NULL
) ENGINE=MyISAM;
