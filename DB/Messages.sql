-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 09. Oktober 2007 um 22:04
-- Server Version: 5.0.32
-- PHP-Version: 5.2.0-8+etch7
-- 
-- Datenbank: `Himmel`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `Messages`
-- 

DROP TABLE IF EXISTS `Messages`;
CREATE TABLE IF NOT EXISTS `Messages` (
  `Datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `SUID` int(11) NOT NULL default '0',
  `RUID` int(11) NOT NULL default '0',
  `isRead` char(1) NOT NULL default 'N',
  `Text` text NOT NULL,
  PRIMARY KEY  (`Datum`,`SUID`,`RUID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Fuers interen Communikationssystem';

