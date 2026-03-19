# 1. Testo

Il progetto nasce dall’esigenza di mantenere e migliorare il sito del Mercatino dei Libri Usati
di Crema, che ogni anno necessita di manutenzione.
Negli ultimi anni, il codice esistente è diventato difficile da gestire e correggere. Gli interventi
di manutenzione hanno progressivamente aumentato la complessità del sistema, fino al
punto in cui intervenire sull’attuale codice non è più sostenibile.
Per questi motivi, si è deciso di ripartire da zero, realizzando una nuova versione del sito
basata su tecnologie moderne, più scalabili e manutenibili.

# 2. Analisi del contesto

Prima di partire con il progetto abbiamo effettuato un’analisi , unendo le nostre opinioni , in qualità di sviluppatori, ma prima ancora gestori-volontari , al mercatino dei libri , con quelle degli altri stakeholder .

### Stakeholder

| Nome                        | Ruolo e coinvolgimento                    |
| --------------------------- | ----------------------------------------- |
| **Prof. Davide Pagliarini** | Supervisore tecnico / guida del progetto. |

Alto potere e alto interesse.
Aggiornato e consultato regolarmente. |
| **Consultorio / Veruska Stanga** | Beneficiario indiretto del progetto.
Alto potere ma basso interesse.
Verrà informata al completamento. |
| **Volontari del Mercatino** | Utilizzatori principali del sistema.
Basso potere decisionale, alto interesse.
Verranno consultati prima della realizzazione e formati all’uso del nuovo sito. |
| **Clienti del mercatino** | Utenti finali del servizio. Basso potere e basso interesse. Beneficeranno di prenotazioni più semplici e minori tempi di attesa. |
| **Project Manager** | Parolari Andrea |
| **Project Team** | Fusar Bassini Simone – Frontend Developer
Parolari Andrea – Backend Developer |

# 3. Ipotesi

| **Voce**                                                                        | **Descrizione**                                                                     |
| ------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| **Constraints (Vincoli)**                                                       | Infrastruttura server e database preesistenti non modificabili in modo sostanziale. |
| Nessun budget per software o licenze aggiuntive. Deadline fissa ad aprile 2025. |
| **Assumptions (Assunzioni)**                                                    | Accesso garantito al database e ai sorgenti precedenti.                             |

Disponibilità del prof. Pagliarini per supporto tecnico.
I volontari collaboreranno per il testing e forniranno feedback.
Requisiti invariati rispetto a gli anni scorsi |
| **Dependencies (Dipendenze)** | Compatibilità con il database attuale.
Utilizzo del repository GitHub condiviso.
Dipendenza da librerie e strumenti (Vue.js, compose, altre librerie “on demand”). |
| **Risks (Rischi)** | - Problemi di compatibilità con il vecchio database.

- Bug critici non rilevati durante i test.
- Difficolta di comprensione del codice vecchio.
- Possibili difficoltà di integrazione con il sito pubblico. |

# 4. Obiettivi

1. Re-implementare tutte le funzionalità già esistenti .
2. Aggiungere almeno la gestione delle ricevute di restituzione e la relativa contabilità.
3. Garantire stabilità della piattaforma dopo la fase di testing (assenza di bug noti).
4. Migliorare le performance complessive del sito (misurazione del tempo di risposta nelle operazioni chiave, e tutte quelle user-facing sotto il secondo).

# 5. Target

Il progetto si rivolge a quelli che sono i nostri stakeholder :

| Nome                        | Ruolo e coinvolgimento                    |
| --------------------------- | ----------------------------------------- |
| **Prof. Davide Pagliarini** | Supervisore tecnico / guida del progetto. |

Alto potere e alto interesse.
Aggiornato e consultato regolarmente. |
| **Consultorio / Veruska Stanga** | Beneficiario indiretto del progetto.
Alto potere ma basso interesse.
Verrà informata al completamento. |
| **Volontari del Mercatino** | Utilizzatori principali del sistema.
Basso potere decisionale, alto interesse.
Verranno consultati prima della realizzazione e formati all’uso del nuovo sito. |
| **Clienti del mercatino** | Utenti finali del servizio. Basso potere e basso interesse. Beneficeranno di prenotazioni più semplici e minori tempi di attesa. |

# UML

![Alt text](../docs/casi%20d'uso.png)

## User stories

### Funzionalità del gestore

- Voglio registrare nuovi libri, che ricevo servendo i clienti.
- Voglio vendere un libro a un cliente, facendo una transazione.
- Voglio registrare una restituzione di libri e soldi agli utenti, aggiornando la disponibilità di libri.
- Voglio visualizzare le informazioni sul mercatino, così da sapere quali libri sono disponibili, prenotati o venduti.

### Funzionalità del cliente

- Voglio registrarmi o fare il login, così da poter accedere al servizio.
- Voglio prenotare uno o più libri, così da assicurarmi una copia disponibile.
- Voglio vedere lo stato della mia prenotazione/vendita, così da sapere se il mio ordine è confermato, in attesa o completato.

### Funzionalità amministratore

- Voglio importare i dati delle adozioni, così da aggiornare le informazioni sui libri accettati ed il loro prezzo.
- Voglio creare e gestire gli account dei gestori, a partire da un account utente.

# 7. Strategia di soluzione

## Hardware

Dal punto di vista hardware non ci siamo dovuti preoccupare di niente .
I pc in dotazione al Mercatino dei Libri usati , sono forniti dalla scuola, così come stampanti, scanner per ISBN.

## Software

### Spiegazione tecnologie

Abbiamo scelto di realizzare un'applicazione in cui frontend e backend sono **stand alone** .

### Backend

Il backend è stato realizzato in Laravel, un framework di PHP , questo perchè lo abbiamo ritenuto molto comodo per realizzare un backend indipendente che si interfacci con il frontend attraverso delle APPLICATION PROGRAMMING INTERFACE .
Il principale vantaggio di questa scelta è che i problemi sul frontend, non vanno a toccare il backend . E viceversa.

### Frontend

Il frontend è stato realizzato in VUE.JS, un framework JS, ottimo per realizzare componenti dinamici e riutilizzabili.
Il frontend si interfaccia con il backend attraverso delle chiamate alla API.

### Database

Il Database è un database classico SQL. <br>
Siamo partiti dal DB già esistente del mercatino , ma sono state effettuate delle riottimizzazioni, in funzione alle analisi da noi pre-effettuate.

Schema E/R ? inserire
<br>

<!-- non so se lo abbiamo ...-->

# 8. Implementazione

<!-- TO DO-->

# 9. Distribuzione

Il progetto sarà disponibile alla repo github già esistente (attualmente privata)
Il deploy sull' hosting finale che verrà usato , dipenderà dalle valutazioni sull' Hosting che dovranno essere fatte più avanti.

# 10. VALUTAZIONE

Le faremo alla fine.
Per ora possiamo dire che su quello su cui abbiamo lavorato abbiamo ottenuto le aspetative richieste.

# 11. SVILUPPI FUTURI

Dipende tutto da quanto viene completato nell ' ultimo sprint. Se si fa tutto bene , altrimenti ce ne saranno altri . <br>
Comunque alcuni sviluppi certi sono :

- implementazione a livello backend di avere la possibilità di più sedi fisiche in cui si trovino libri e aziende.

# 12. DIRITTI D' AUTORE

Il codice è attualmente privato , quindi non distribuito con alcuna licenza.
Tuttavia , crediamo che lo andremo a distribuire con licenza CREATIVE COMMONS

## Rispetto copyright e GDPR

Noi nell' implementare il codice ci siamo preoccupati soltanto che le icone utilizzate nelle pagine , e anche le immagini, fossero senza copyright .
