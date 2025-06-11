-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-5.7
-- Время создания: Июн 11 2025 г., 03:36
-- Версия сервера: 5.7.44
-- Версия PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `ProcIMP`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Администраторы`
--

CREATE TABLE `Администраторы` (
  `ID` int(11) NOT NULL,
  `Логин` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Пароль` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Администраторы`
--

INSERT INTO `Администраторы` (`ID`, `Логин`, `Пароль`, `is_admin`) VALUES
(1, 'admin', 'admin', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `Контактные_сообщения`
--

CREATE TABLE `Контактные_сообщения` (
  `ID` int(11) NOT NULL,
  `Имя` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Сообщение` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Дата` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Контактные_сообщения`
--

INSERT INTO `Контактные_сообщения` (`ID`, `Имя`, `Email`, `Сообщение`, `Дата`) VALUES
(1, 'Мавтй', 'test@emal.ste', 'Проверка отправки', '2025-05-06 02:48:01');

-- --------------------------------------------------------

--
-- Структура таблицы `Поставщики`
--

CREATE TABLE `Поставщики` (
  `ID-Поставщика` int(11) NOT NULL,
  `Название` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Страна` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Контактная информация` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Поставщики`
--

INSERT INTO `Поставщики` (`ID-Поставщика`, `Название`, `Страна`, `Контактная информация`) VALUES
(1, 'Intel', 'США', 'intel@gmail.com'),
(2, 'AMD', 'США', 'amd@gmail.com'),
(3, 'МЦСТ', 'Россия', 'mcst@mail.ru'),
(4, 'Testing', 'TEst23', 'test@teeest.ru');

-- --------------------------------------------------------

--
-- Структура таблицы `Процессоры`
--

CREATE TABLE `Процессоры` (
  `ID-Процессора` int(11) NOT NULL,
  `Модель` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Характеристики` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Цена` decimal(11,0) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Дата Выпуска` date NOT NULL,
  `ID-Поставщика` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Процессоры`
--

INSERT INTO `Процессоры` (`ID-Процессора`, `Модель`, `Характеристики`, `Цена`, `image_url`, `Дата Выпуска`, `ID-Поставщика`) VALUES
(1, 'i5-12405f', '04-12 3300Hz', 21000, 'https://www.market777.ru/upload/iblock/5bb/b4l37v0rbcuuqzar7h0injq18atk9in7.jpg', '2022-06-15', 1),
(2, 'i7-13700k', '16-24 3400Hz', 45000, 'https://microless.com/cdn/products/4ca48b2f58b4e123077ce00a595f341c-hi.jpg', '2023-09-13', 1),
(3, 'Ryzen 7 7700x', '08-16 4500Hz', 37790, 'https://encrypted-tbn3.gstatic.com/shopping?q=tbn:ANd9GcS5DoRBTJGrnltVzVXb2J0V5iCf9epQ_teVOSiIAVuQ91WaLq1FVn3a6728i1rWOMGVU6oE3TjpdGmAwNO5Z-ldh2drkuQfSNTq1BZH_gKn_ycPpqDPMCH3Bg', '2022-05-10', 2),
(4, 'Эльбрус-8СВ', '08-16 1300Hz', 7800, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSRPoQtBnUXz7HFyJGx1q6brlParuBDPdOv8Q&s', '2016-09-14', 3);

-- --------------------------------------------------------

--
-- Структура таблицы `Заказ_Процессор_Склад`
--

CREATE TABLE `Заказ_Процессор_Склад` (
  `ID-Заказа` int(11) NOT NULL,
  `ID-Процессора` int(11) NOT NULL,
  `ID-Склада` int(11) NOT NULL,
  `Количество` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Заказ_Процессор_Склад`
--

INSERT INTO `Заказ_Процессор_Склад` (`ID-Заказа`, `ID-Процессора`, `ID-Склада`, `Количество`) VALUES
(1, 1, 1, 50),
(2, 2, 2, 40),
(5, 3, 3, 34),
(2, 3, 1, 20),
(4, 4, 1, 54),
(7, 4, 1, 2),
(7, 1, 1, 5),
(15, 3, 1, 4),
(16, 3, 1, 5),
(17, 1, 1, 50),
(17, 2, 1, 3),
(15, 1, 1, 43),
(16, 1, 1, 32),
(18, 2, 1, 20),
(19, 2, 1, 20),
(20, 2, 2, 20),
(21, 3, 3, 20);

-- --------------------------------------------------------

--
-- Структура таблицы `Заказы`
--

CREATE TABLE `Заказы` (
  `ID-Заказа` int(11) NOT NULL,
  `Тип` tinyint(1) NOT NULL,
  `Дата` date NOT NULL,
  `Статус` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Заказы`
--

INSERT INTO `Заказы` (`ID-Заказа`, `Тип`, `Дата`, `Статус`) VALUES
(1, 1, '2025-03-04', 1),
(2, 0, '2024-02-14', 0),
(3, 0, '2024-11-04', 1),
(4, 1, '2024-09-04', 0),
(5, 1, '2024-09-10', 1),
(7, 1, '2025-05-06', 1),
(15, 1, '2025-05-06', 0),
(16, 1, '2025-05-13', 0),
(17, 1, '2025-05-13', 0),
(18, 1, '2025-06-04', 0),
(19, 1, '2025-06-04', 0),
(20, 1, '2025-06-04', 0),
(21, 1, '2025-06-04', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `Заказы на импорт`
--

CREATE TABLE `Заказы на импорт` (
  `ID-Заказа` int(11) NOT NULL,
  `ID-Точки импорта` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Заказы на импорт`
--

INSERT INTO `Заказы на импорт` (`ID-Заказа`, `ID-Точки импорта`) VALUES
(2, 1),
(3, 1),
(7, 4),
(15, 4),
(16, 4),
(17, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `Заказы на закуп`
--

CREATE TABLE `Заказы на закуп` (
  `ID-Заказа` int(11) NOT NULL,
  `ID-Закупщика` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Заказы на закуп`
--

INSERT INTO `Заказы на закуп` (`ID-Заказа`, `ID-Закупщика`) VALUES
(5, 1),
(1, 2),
(4, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `Закупщики`
--

CREATE TABLE `Закупщики` (
  `ID-Закупщика` int(11) NOT NULL,
  `Название предприятия` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Контактная информация:` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Закупщики`
--

INSERT INTO `Закупщики` (`ID-Закупщика`, `Название предприятия`, `Контактная информация:`) VALUES
(1, 'DNS', 'dns-business@gmail.com'),
(2, 'NIX', 'nix-urlic@gmail.com'),
(3, 'LOandCO', 'loco@gmail.com');

-- --------------------------------------------------------

--
-- Структура таблицы `Импортеры`
--

CREATE TABLE `Импортеры` (
  `ID-Точки импорта` int(11) NOT NULL,
  `Местоположение` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pass` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Импортеры`
--

INSERT INTO `Импортеры` (`ID-Точки импорта`, `Местоположение`, `email`, `pass`, `image_url`) VALUES
(1, 'Северный-12', 'importS1@yandex.ru', 'sever1', 'https://brightrich.moscow/uploads/catalog/product/origin/origin_8b2_8pceb3knb64gt7z2uvzfh88numnnvov3_avilon_fasad_1.webp'),
(2, 'Южный-2', 'importU2@yandex.ru', 'ug2', 'https://avatars.mds.yandex.net/get-altay/4324851/2a00000177454cf04ed91b3b42637a43c519/L_height'),
(3, 'Западный-2', 'importZ2@yandex.ru', 'zapad2', 'https://avatars.mds.yandex.net/get-altay/14067398/2a00000192c8622523d79c3effab0642a656/L_height'),
(4, 'Угольный-6', 'test@test.ru', 'test', 'https://images.cdn-cian.ru/images/07/456/701/1076547020-6.jpg'),
(7, 'Западный-4', 'test2@test.ru', '$2y$10$BsgEsdjYvnWKYJi5tJZZre.qsMujWDKoZ4xX0V2DcDrWpU.Wcmvi6', 'https://avaho.ru/upload/articles/%D0%91%D0%B8%D0%B7%D0%BD%D0%B5%D1%81%20%D1%86%D0%B5%D0%BD%D1%82%D1%80%D1%8B/Dominion%20Tower_photo-resizer.ru.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `Склады`
--

CREATE TABLE `Склады` (
  `ID-Склада` int(11) NOT NULL,
  `Площадь` int(11) NOT NULL,
  `Местоположение` varchar(255) NOT NULL,
  `Статус` tinyint(1) NOT NULL,
  `ID-Точки импорта` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

--
-- Дамп данных таблицы `Склады`
--

INSERT INTO `Склады` (`ID-Склада`, `Площадь`, `Местоположение`, `Статус`, `ID-Точки импорта`) VALUES
(1, 805, 'ул. Ломоносова 12', 1, 1),
(2, 650, 'Ул. Киринского 34', 0, 2),
(3, 600, 'Ул. Университетская 23', 1, 3),
(4, 800, 'Ул. Кшиштовского 52', 0, 1),
(5, 700, 'Ул. Остроского 35', 1, 4);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Администраторы`
--
ALTER TABLE `Администраторы`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Логин` (`Логин`);

--
-- Индексы таблицы `Контактные_сообщения`
--
ALTER TABLE `Контактные_сообщения`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `Поставщики`
--
ALTER TABLE `Поставщики`
  ADD PRIMARY KEY (`ID-Поставщика`);

--
-- Индексы таблицы `Процессоры`
--
ALTER TABLE `Процессоры`
  ADD PRIMARY KEY (`ID-Процессора`),
  ADD KEY `ID-Поставщика` (`ID-Поставщика`) USING BTREE;

--
-- Индексы таблицы `Заказ_Процессор_Склад`
--
ALTER TABLE `Заказ_Процессор_Склад`
  ADD KEY `ID-Процессора` (`ID-Процессора`),
  ADD KEY `ID-Склада` (`ID-Склада`) USING BTREE,
  ADD KEY `ID-Заказа` (`ID-Заказа`) USING BTREE;

--
-- Индексы таблицы `Заказы`
--
ALTER TABLE `Заказы`
  ADD PRIMARY KEY (`ID-Заказа`);

--
-- Индексы таблицы `Заказы на импорт`
--
ALTER TABLE `Заказы на импорт`
  ADD UNIQUE KEY `ID-Заказа` (`ID-Заказа`) USING BTREE,
  ADD KEY `ID-Точки импорта` (`ID-Точки импорта`) USING BTREE;

--
-- Индексы таблицы `Заказы на закуп`
--
ALTER TABLE `Заказы на закуп`
  ADD UNIQUE KEY `ID-Заказа` (`ID-Заказа`),
  ADD KEY `ID-Закупщика` (`ID-Закупщика`) USING BTREE;

--
-- Индексы таблицы `Закупщики`
--
ALTER TABLE `Закупщики`
  ADD PRIMARY KEY (`ID-Закупщика`);

--
-- Индексы таблицы `Импортеры`
--
ALTER TABLE `Импортеры`
  ADD PRIMARY KEY (`ID-Точки импорта`);

--
-- Индексы таблицы `Склады`
--
ALTER TABLE `Склады`
  ADD PRIMARY KEY (`ID-Склада`),
  ADD KEY `ID-Точки импорта` (`ID-Точки импорта`) USING BTREE;

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Администраторы`
--
ALTER TABLE `Администраторы`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `Контактные_сообщения`
--
ALTER TABLE `Контактные_сообщения`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `Поставщики`
--
ALTER TABLE `Поставщики`
  MODIFY `ID-Поставщика` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `Процессоры`
--
ALTER TABLE `Процессоры`
  MODIFY `ID-Процессора` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `Заказы`
--
ALTER TABLE `Заказы`
  MODIFY `ID-Заказа` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `Закупщики`
--
ALTER TABLE `Закупщики`
  MODIFY `ID-Закупщика` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `Импортеры`
--
ALTER TABLE `Импортеры`
  MODIFY `ID-Точки импорта` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `Склады`
--
ALTER TABLE `Склады`
  MODIFY `ID-Склада` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Процессоры`
--
ALTER TABLE `Процессоры`
  ADD CONSTRAINT `процессоры_ibfk_1` FOREIGN KEY (`ID-Поставщика`) REFERENCES `Поставщики` (`ID-Поставщика`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Заказ_Процессор_Склад`
--
ALTER TABLE `Заказ_Процессор_Склад`
  ADD CONSTRAINT `заказ_процессор_склад_ibfk_1` FOREIGN KEY (`ID-Процессора`) REFERENCES `Процессоры` (`ID-Процессора`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `заказ_процессор_склад_ibfk_2` FOREIGN KEY (`ID-Заказа`) REFERENCES `Заказы` (`ID-Заказа`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `заказ_процессор_склад_ibfk_3` FOREIGN KEY (`ID-Склада`) REFERENCES `Склады` (`ID-Склада`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Заказы на импорт`
--
ALTER TABLE `Заказы на импорт`
  ADD CONSTRAINT `заказы на импорт_ibfk_1` FOREIGN KEY (`ID-Заказа`) REFERENCES `Заказы` (`ID-Заказа`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `заказы на импорт_ibfk_2` FOREIGN KEY (`ID-Точки импорта`) REFERENCES `Импортеры` (`ID-Точки импорта`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Заказы на закуп`
--
ALTER TABLE `Заказы на закуп`
  ADD CONSTRAINT `заказы на закуп_ibfk_1` FOREIGN KEY (`ID-Закупщика`) REFERENCES `Закупщики` (`ID-Закупщика`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `заказы на закуп_ibfk_2` FOREIGN KEY (`ID-Заказа`) REFERENCES `Заказы` (`ID-Заказа`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Склады`
--
ALTER TABLE `Склады`
  ADD CONSTRAINT `склады_ibfk_1` FOREIGN KEY (`ID-Точки импорта`) REFERENCES `Импортеры` (`ID-Точки импорта`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
