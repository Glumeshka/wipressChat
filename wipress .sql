-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Сен 29 2023 г., 19:56
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `wipress`
--

-- --------------------------------------------------------

--
-- Структура таблицы `chats`
--

CREATE TABLE `chats` (
  `chat_id` int NOT NULL,
  `chat_type` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `participants` json NOT NULL,
  `group_name` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mute_chat` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `chats`
--

INSERT INTO `chats` (`chat_id`, `chat_type`, `participants`, `group_name`, `mute_chat`) VALUES
(86, 'Пользовательский', '[39, 40]', NULL, 1),
(87, 'Пользовательский', '[39, 42]', NULL, 0),
(88, 'Пользовательский', '[39, 43]', NULL, 0),
(89, 'Пользовательский', '[39, 45]', NULL, 0),
(90, 'Пользовательский', '[39, 41]', NULL, 0),
(91, 'Групповой', '[40, 42, 43, 45, 41, 39]', 'Зоопарк', 0),
(92, 'Пользовательский', '[40, 45]', NULL, 0),
(93, 'Пользовательский', '[40, 43]', NULL, 0),
(94, 'Пользовательский', '[40, 42]', NULL, 0),
(95, 'Пользовательский', '[40, 41]', NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `message_id` int NOT NULL,
  `chat_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`message_id`, `chat_id`, `sender_id`, `message_text`, `timestamp`) VALUES
(118, 86, 39, 'окей шле', '2023-09-29 14:17:02');

-- --------------------------------------------------------

--
-- Структура таблицы `profile`
--

CREATE TABLE `profile` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nick` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `avatar` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/img/avatar_default.jpg',
  `hideEmail` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `profile`
--

INSERT INTO `profile` (`id`, `user_id`, `nick`, `avatar`, `hideEmail`) VALUES
(1, 39, 'Гепард', 'avatars/enot.jpg', 0),
(2, 40, 'Фокс', 'avatars/fox.jpg', 0),
(3, 41, 'Цифра', 'avatars/smile.jpg', 0),
(4, 42, 'инкогнито', 'avatars/face.jpg', 0),
(5, 43, 'Мышка', 'avatars/230775.jpg', 0),
(7, 45, 'Кыся', 'avatars/кыся.jpg', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `email` varchar(128) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Пользователь удален',
  `password` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `OPT` int NOT NULL,
  `verification` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `token`, `OPT`, `verification`) VALUES
(39, 'rzl1985@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$d1h6dlVXSXk5dzlLbzM2TA$7oR4uEx8HaGeP0k6qKN+Y2WwuAQMvp9dga7WkUquYas', '$argon2id$v=19$m=65536,t=4,p=1$VnRJVDVPL25nZmQ4WTZOMg$vKloLCtFicJU6OYA5dw3fD6Qz0fKGpNTE08i4Wcj0Is', 0, 1),
(40, 'rzl1985@yandex.ru', '$argon2id$v=19$m=65536,t=4,p=1$bnFobTRsUDNQTVVPckdxZg$TZHkXYkQg8EHFaAiEYzvxZG83aDF+YKmOaYuR+ksOc4', '$argon2id$v=19$m=65536,t=4,p=1$Z3ZFTlN3dUlQL21CYTBNbg$3P2VFupHkFJsSqM8D5jPFY/U/pLLBU0XF1nH00oTBng', 0, 1),
(41, '123@123.ru', '$argon2id$v=19$m=65536,t=4,p=1$WDhKL003UmcyTEdSRzk4Lw$WW6UB7Qbi7rGPqMSsa3VuhJK4m/HfHEZdEU3nKnTivU', '$argon2id$v=19$m=65536,t=4,p=1$QlFvbWxvdG5NNHRTUXJ3cg$bq4PcUN3GaawgQbWGFV+odfCVMQ7CUeG3GFMvizhhno', 0, 1),
(42, 'asd@asd.we', '$argon2id$v=19$m=65536,t=4,p=1$WGxLTnhiTkx3ajFFRkdUTQ$kbgbBfYiFUxTXUNfugf+VHtEJeq1+btlsfU3v+v3JZk', '$argon2id$v=19$m=65536,t=4,p=1$UDhZTm9XLkU5Mi94bUlZdQ$FlCzY8pOxtAmLTpmVcUBfLWGRqrdRFIP8DolQ/jCW2w', 0, 1),
(43, 'test@test.com', '$argon2id$v=19$m=65536,t=4,p=1$Y2p6SzVyeS42MTRqM3I5Lw$FelcTOB99/6b20Jm36r7FIE7MVcc5tdZ0xqr1GpKFz4', '$argon2id$v=19$m=65536,t=4,p=1$MXJoSHlSdVBFWjcvOE5KUg$HiwGYoe94FhfG/uMlOpOqzTCvoA0dXayHZEEdNdagqA', 0, 1),
(45, '321@321.ru', '$argon2id$v=19$m=65536,t=4,p=1$cTdnZ3JFNVJNR1NteFpWMQ$+h9HydIrryLb4Krg5WGlMptroK+ceoz95y42fASGXDE', '$argon2id$v=19$m=65536,t=4,p=1$YmpTMndoMlhpTlZ4M05mUA$zvLSLa72cQ23f2l/Ny2mcHR4Hnu2HBg6Bu76Xds9qSY', 0, 1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`chat_id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `FK_messages_users` (`sender_id`),
  ADD KEY `FK_messages_chats` (`chat_id`);

--
-- Индексы таблицы `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_profile_users` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `chats`
--
ALTER TABLE `chats`
  MODIFY `chat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT для таблицы `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `FK_messages_chats` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`chat_id`),
  ADD CONSTRAINT `FK_messages_users` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`);

--
-- Ограничения внешнего ключа таблицы `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `FK_profile_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
