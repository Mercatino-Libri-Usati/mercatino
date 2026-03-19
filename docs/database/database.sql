CREATE TABLE `adozioni` (
  `ID` int PRIMARY KEY,
  `ID_catalogo` int,
  `scuola` text,
  `indirizzo` text,
  `classe` text,
  `materia` text,
  `nuova` text,
  `da_acquistare` text,
  `consigliato` text,
  `anno` int
);

CREATE TABLE `catalogo` (
  `ID` int PRIMARY KEY,
  `ISBN` text,
  `titolo` text,
  `casa_editrice` text,
  `autore` text,
  `prezzo` float,
  `materia` text,
  `sottotitolo` text,
  `vol` text
);

CREATE TABLE `libron` (
  `id` bigint PRIMARY KEY,
  `prezzo` decimal,
  `id_libro` int,
  `id_ritiro` int,
  `id_prenotazione` int,
  `id_vendita` int,
  `id_restituzione` int,
  `numero_libro` int,
  `note` text
);

CREATE TABLE `log_operazioni` (
  `id` int PRIMARY KEY,
  `data` date,
  `tipo` enum,
  `libro` bigint,
  `operatore` int,
  `importo` decimal,
  `causale` varchar(255)
);

CREATE TABLE `personal_access_tokens` (
  `id` bigint PRIMARY KEY,
  `tokenable_type` varchar(255),
  `tokenable_id` bigint,
  `name` varchar(255),
  `token` varchar(255),
  `abilities` text,
  `last_used_at` timestamp,
  `expires_at` timestamp,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `prenotazionen` (
  `id` bigint PRIMARY KEY,
  `data` timestamp,
  `id_utente` int,
  `numero_prenotazione` int,
  `url_pdf` text
);

CREATE TABLE `pwd_request` (
  `id` int PRIMARY KEY,
  `id_utenti` int,
  `date_req` datetime,
  `token` varchar(255)
);

CREATE TABLE `restituzionen` (
  `id` bigint PRIMARY KEY,
  `data` timestamp,
  `id_utente` int,
  `numero_restituzione` int,
  `url_pdf` text
);

CREATE TABLE `ritiron` (
  `id` bigint PRIMARY KEY,
  `data` timestamp,
  `id_utente` int,
  `numero_ritiro` int,
  `url_pdf` text
);

CREATE TABLE `users` (
  `ID` int PRIMARY KEY,
  `ID_utenti` int,
  `nickname` text,
  `password` text,
  `privilegi` int,
  `ID_registro` int,
  `sede` smallint,
  `attivo` tinyint
);

CREATE TABLE `utenti` (
  `ID` int PRIMARY KEY,
  `nome` text,
  `cognome` text,
  `telefono` text,
  `mail` text,
  `scuola` text,
  `ID_registro` int,
  `data` datetime,
  `codiceVerifica` varchar(255)
);

CREATE TABLE `venditan` (
  `id` bigint PRIMARY KEY,
  `data` timestamp,
  `id_utente` int,
  `numero_vendita` int,
  `url_pdf` text
);

ALTER TABLE `adozioni` ADD FOREIGN KEY (`ID_catalogo`) REFERENCES `catalogo` (`ID`);

ALTER TABLE `log_operazioni` ADD FOREIGN KEY (`operatore`) REFERENCES `utenti` (`ID`);

ALTER TABLE `log_operazioni` ADD FOREIGN KEY (`libro`) REFERENCES `libron` (`id`);

ALTER TABLE `libron` ADD FOREIGN KEY (`id_libro`) REFERENCES `catalogo` (`ID`);

ALTER TABLE `libron` ADD FOREIGN KEY (`id_ritiro`) REFERENCES `ritiron` (`id`);

ALTER TABLE `libron` ADD FOREIGN KEY (`id_prenotazione`) REFERENCES `prenotazionen` (`id`);

ALTER TABLE `libron` ADD FOREIGN KEY (`id_vendita`) REFERENCES `venditan` (`id`);

ALTER TABLE `libron` ADD FOREIGN KEY (`id_restituzione`) REFERENCES `restituzionen` (`id`);

ALTER TABLE `prenotazionen` ADD FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`ID`);

ALTER TABLE `restituzionen` ADD FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`ID`);

ALTER TABLE `ritiron` ADD FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`ID`);

ALTER TABLE `venditan` ADD FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`ID`);

ALTER TABLE `personal_access_tokens` ADD FOREIGN KEY (`name`) REFERENCES `utenti` (`ID`);

ALTER TABLE `pwd_request` ADD FOREIGN KEY (`id_utenti`) REFERENCES `utenti` (`ID`);

ALTER TABLE `users` ADD FOREIGN KEY (`ID_utenti`) REFERENCES `utenti` (`ID`);
