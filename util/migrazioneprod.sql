DROP TABLE standbyusers;

DROP TABLE registro;

DROP table ricevute;

RENAME TABLE libri TO libri;

RENAME TABLE ritiri TO ritiri;

RENAME TABLE prenotazioni TO prenotazioni;

RENAME TABLE venditan TO vendite;

RENAME TABLE restituzioni TO restituzioni;

RENAME TABLE users TO credenziali;

RENAME TABLE richieste_password to richieste_password;

ALTER TABLE `libri` CHANGE `id_catalogo` `id_catalogo` INT (11) NOT NULL;

ALTER TABLE `richieste_password` CHANGE `richieste_password` `data` DATETIME NOT NULL;

ALTER TABLE `richieste_password` CHANGE `id_utenti` `id_utente` INT (11) NOT NULL;