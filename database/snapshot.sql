-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Czas generowania: 12 Lis 2021, 17:14
-- Wersja serwera: 10.6.4-MariaDB-1:10.6.4+maria~focal
-- Wersja PHP: 7.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `car_rental`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `archive`
--

CREATE TABLE `archive` (
  `ID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `carID` int(11) DEFAULT NULL,
  `borrow_start` datetime NOT NULL,
  `borrow_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `body_types`
--

CREATE TABLE `body_types` (
  `ID` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `body_types`
--

INSERT INTO `body_types` (`ID`, `name`) VALUES
(1, 'hatchback'),
(2, 'sedan'),
(3, 'kombi'),
(4, 'coupe'),
(5, 'SUV'),
(6, 'pick-up');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `cars`
--

CREATE TABLE `cars` (
  `ID` int(11) NOT NULL,
  `makeID` int(11) NOT NULL,
  `model` varchar(40) COLLATE utf8mb4_polish_ci NOT NULL,
  `body_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `drive_id` int(11) NOT NULL,
  `power` int(11) NOT NULL,
  `img_url` text COLLATE utf8mb4_polish_ci NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `cars`
--

INSERT INTO `cars` (`ID`, `makeID`, `model`, `body_id`, `color_id`, `drive_id`, `power`, `img_url`, `price`) VALUES
(10, 3, 'Stelvio', 5, 4, 1, 280, 'https://upload.wikimedia.org/wikipedia/commons/5/56/Alfa_Romeo_Stelvio_Rear.jpg', 100),
(11, 2, 'Fiesta', 1, 8, 1, 125, 'https://upload.wikimedia.org/wikipedia/commons/2/2d/2018_Ford_Fiesta_Vignale_Front.jpg', 25),
(12, 6, 'Clio', 1, 2, 1, 65, 'https://upload.wikimedia.org/wikipedia/commons/3/33/00_renault_clio_1.jpg', 25),
(13, 8, 'Yaris', 1, 6, 4, 116, 'https://upload.wikimedia.org/wikipedia/commons/3/33/Osaka_Motor_Show_2019_%2863%29_-_Toyota_YARiS_HYBRID_Z_E-Four_%286AA-MXPH15-AHXEB%29.jpg', 40),
(14, 1, 'Passat', 3, 3, 2, 150, 'https://upload.wikimedia.org/wikipedia/commons/1/12/2004_Volkswagen_Passat_GL_Estate_Wagon_%28US_model%29_in_Mojave_Beige_%28rear_perspective_view%29.jpg', 70),
(15, 7, 'A4', 2, 2, 1, 150, 'https://upload.wikimedia.org/wikipedia/commons/4/44/Audi_A4_sedan_--_02-18-2011.jpg', 70),
(16, 5, 'C4 Cactus', 5, 7, 1, 110, 'https://upload.wikimedia.org/wikipedia/commons/e/ef/2014_Citroen_C4_Cactus_Feel_Edition_PureTech_e-THP_110_Seitenansicht_Hello_Yellow.jpg', 45),
(17, 3, '4C', 4, 4, 1, 240, 'https://upload.wikimedia.org/wikipedia/commons/f/fb/Alfa_romeo_4c_front_right.jpg', 180),
(18, 4, 'Adam', 1, 1, 1, 90, 'https://upload.wikimedia.org/wikipedia/commons/b/b8/Opel_Adam_1.4_Black_Link_%E2%80%93_Frontansicht%2C_2._Mai_2014%2C_W%C3%BClfrath.jpg', 40),
(19, 4, 'Mokka', 5, 5, 3, 136, 'https://upload.wikimedia.org/wikipedia/commons/9/91/Opel-mokka-e-vorne.jpg', 95),
(20, 1, 'Scirocco', 1, 2, 1, 160, 'https://upload.wikimedia.org/wikipedia/commons/d/da/Volkswagen_Scirocco_III_China_2014-04-14.jpg', 75),
(21, 2, 'Mustang', 4, 4, 1, 450, 'https://upload.wikimedia.org/wikipedia/commons/d/d2/Ford_Mustang_GT_2017.jpg', 200),
(22, 7, 'TT', 4, 3, 1, 160, 'https://upload.wikimedia.org/wikipedia/commons/5/5a/2007_Audi_TT_Coupe.JPG', 80),
(23, 8, 'Supra GR', 4, 3, 1, 340, 'https://upload.wikimedia.org/wikipedia/commons/3/32/Toyota_GR_SUPRA_RZ_%283BA-DB42-ZRRW%29_rear.jpg', 180);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `car_makes`
--

CREATE TABLE `car_makes` (
  `ID` int(11) NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `car_makes`
--

INSERT INTO `car_makes` (`ID`, `name`) VALUES
(1, 'Volkswagen'),
(2, 'Ford'),
(3, 'Alfa Romeo'),
(4, 'Opel'),
(5, 'Citroen'),
(6, 'Renault'),
(7, 'Audi'),
(8, 'Toyota');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `colors`
--

CREATE TABLE `colors` (
  `ID` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_polish_ci NOT NULL,
  `hex` varchar(7) COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `colors`
--

INSERT INTO `colors` (`ID`, `name`, `hex`) VALUES
(1, 'czarny', '#000000'),
(2, 'biały', '#ffffff'),
(3, 'srebrny', '#c0c0c0'),
(4, 'czerwony', '#ff0000'),
(5, 'zielony', '#00ff00'),
(6, 'niebieski', '#0000ff'),
(7, 'żółty', '#ffff00'),
(8, 'szary', '#808080');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `curr_booked`
--

CREATE TABLE `curr_booked` (
  `ID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `carID` int(11) NOT NULL,
  `borrow_start` datetime NOT NULL,
  `borrow_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Wyzwalacze `curr_booked`
--
DELIMITER $$
CREATE TRIGGER `archive` AFTER DELETE ON `curr_booked` FOR EACH ROW INSERT INTO archive(userID, carID, borrow_start, borrow_end) VALUES(old.userID, old.carID, old.borrow_start, old.borrow_end)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `clear_rejected` AFTER INSERT ON `curr_booked` FOR EACH ROW DELETE FROM pending_requests WHERE pending_requests.carID = NEW.carID
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `drive_types`
--

CREATE TABLE `drive_types` (
  `ID` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `drive_types`
--

INSERT INTO `drive_types` (`ID`, `name`) VALUES
(1, 'benzynowy'),
(2, 'diesel'),
(3, 'elektryczny'),
(4, 'hybrydowy'),
(5, 'wodorowy');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pending_requests`
--

CREATE TABLE `pending_requests` (
  `ID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `carID` int(11) NOT NULL,
  `preferred_start` datetime NOT NULL,
  `preferred_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `nick` varchar(40) COLLATE utf8mb4_polish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_polish_ci NOT NULL,
  `email` varchar(40) COLLATE utf8mb4_polish_ci NOT NULL,
  `type` tinyint(4) NOT NULL,
  `blocked` boolean NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`ID`, `nick`, `password`, `email`, `type`, `blocked`) VALUES
(1, 'admin', '$2y$10$qmWg0gCmgkLv8wVnADdGQeZM5zUC88Bli6snKgJMQAeTR9hwokdc2', 'admin@example.com', 0, 0),
(2, 'user', '$2y$10$vw2GXxggp4QLquFa8UszBenoLmphe3sGfU4Yc/bPcjLNpPrmxoiGK', 'user@example.com', 1, 0);

--
-- Struktura tabeli curr_time
--

CREATE TABLE `curr_time` (
  `time` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `curr_time`
--

INSERT INTO `curr_time` (`time`) VALUES
(DATE_FORMAT(CURRENT_TIMESTAMP, "%Y-%m-%d %H:00:00"));

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `archive_ibfk_1` (`carID`),
  ADD KEY `archive_ibfk_2` (`userID`);

--
-- Indeksy dla tabeli `body_types`
--
ALTER TABLE `body_types`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksy dla tabeli `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `makeID` (`makeID`),
  ADD KEY `body_id` (`body_id`),
  ADD KEY `color_id` (`color_id`),
  ADD KEY `drive_id` (`drive_id`);

--
-- Indeksy dla tabeli `car_makes`
--
ALTER TABLE `car_makes`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksy dla tabeli `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksy dla tabeli `curr_booked`
--
ALTER TABLE `curr_booked`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `curr_booked_ibfk_1` (`carID`),
  ADD KEY `curr_booked_ibfk_2` (`userID`);

--
-- Indeksy dla tabeli `drive_types`
--
ALTER TABLE `drive_types`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksy dla tabeli `pending_requests`
--
ALTER TABLE `pending_requests`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `pending_requests_ibfk_1` (`carID`),
  ADD KEY `pending_requests_ibfk_2` (`userID`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `archive`
--
ALTER TABLE `archive`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT dla tabeli `body_types`
--
ALTER TABLE `body_types`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `cars`
--
ALTER TABLE `cars`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT dla tabeli `car_makes`
--
ALTER TABLE `car_makes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT dla tabeli `colors`
--
ALTER TABLE `colors`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT dla tabeli `curr_booked`
--
ALTER TABLE `curr_booked`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT dla tabeli `drive_types`
--
ALTER TABLE `drive_types`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT dla tabeli `pending_requests`
--
ALTER TABLE `pending_requests`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `archive`
--
ALTER TABLE `archive`
  ADD CONSTRAINT `archive_ibfk_1` FOREIGN KEY (`carID`) REFERENCES `cars` (`ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `archive_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`ID`) ON DELETE SET NULL;

--
-- Ograniczenia dla tabeli `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`makeID`) REFERENCES `car_makes` (`ID`),
  ADD CONSTRAINT `cars_ibfk_2` FOREIGN KEY (`body_id`) REFERENCES `body_types` (`ID`),
  ADD CONSTRAINT `cars_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `colors` (`ID`),
  ADD CONSTRAINT `cars_ibfk_4` FOREIGN KEY (`drive_id`) REFERENCES `drive_types` (`ID`);

--
-- Ograniczenia dla tabeli `curr_booked`
--
ALTER TABLE `curr_booked`
  ADD CONSTRAINT `curr_booked_ibfk_1` FOREIGN KEY (`carID`) REFERENCES `cars` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `curr_booked_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;

--
-- Ograniczenia dla tabeli `pending_requests`
--
ALTER TABLE `pending_requests`
  ADD CONSTRAINT `pending_requests_ibfk_1` FOREIGN KEY (`carID`) REFERENCES `cars` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `pending_requests_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE EVENT IF NOT EXISTS fake_curr_time
ON SCHEDULE EVERY 1 MINUTE
DO
  UPDATE curr_time SET time = DATE_ADD(time, INTERVAL 1 HOUR); 
