# Obiettivo

Implementare un sistema di autenticazione coerente con lo schema fornito, usando **Laravel + Sanctum** per proteggere **API REST** e **frontend Vue** (SPA), con flusso di registrazione a doppio step (standby → attivo).

Il sistema deve:

* separare **dati anagrafici** da **credenziali**
* supportare **email verification / onboarding differito**
* proteggere **API** e **frontend Vue** con la stessa base di sicurezza
* essere pulito, estendibile, e senza campi inutili

---

## Modello dati (razionalizzato)

### 1. `utenti`

Contiene **solo dati anagrafici**, nessuna credenziale.

Campi:

* `id` (PK)
* `nome`
* `cognome`
* `telefono`
* `email` (UNIQUE)
* `scuola` (evitabile)
* `id_registro` (evitabile)
* `created_at` 

> Questa tabella **non autentica nessuno**. Serve come dominio applicativo.

---

### 2. `users`

Tabella **di autenticazione reale** (Sanctum lavora qui).

Campi:

* `id`
* `utente_id` (FK → utenti.id, UNIQUE)
* `username` (UNIQUE, indicizzato, nome.cognome)
* `password` (bcrypt, **mai md5**)
* `role` (tinyint / enum: es. 1=user, 2=gestore, 3=admin)
* `active` (inutile)

> Ogni record qui rappresenta **un account loggabile**.

---

### 3. `standby_users`

Usata **solo durante onboarding / verifica email**.

Campi:

* `id`
* `utente_id` (FK → utenti.id)
* `username` (pre-allocato, nome.cognome)
* `verification_token` (hashato)
* `expires_at`
* `created_at`

> Nessuna password qui. La password viene scelta **solo dopo la verifica**.

---

## Flusso di registrazione (step-by-step)

### Step 1 – Inserimento dati

Schermata del gestore o pubblica:
Inserire nome, cognome, email, telefono.

Endpoint API (pubblico):

```
POST /api/register
```

Payload:

```json
{
  "nome": "Mario",
  "cognome": "Rossi",
  "email": "mario@x.it",
  "telefono": "123",
}
```

Azioni backend:

1. Validazione (email unica, username unico)
2. Creazione record in `utenti`
3. Creazione record in `standby_users`


# Step 2. Generazione token unico

1. Generazione di un token casuale in cod standby_users, salvare sha in db standby_users.cod
2. Invio email con link di verifica:

```
https://frontend.app/complete-registration?tempUserId=123&token=XYZ
```

### Step 3 – Completamento registrazione

Frontend Vue:

* pagina pubblica
* legge `tempUserId` e `token` da query string
* mostra ciao Nome Cognome, inserisci password (2 volte)

Endpoint API:

```
POST /api/complete-registration
```

Payload:

```json
{
  "token": "XYZ",
  "password": "StrongPassword123"
}
```

Azioni backend:

1. Verifica token + scadenza
2. Creazione record in `users`

   * password cryptata nel campo `users.password`
3. Eliminazione record `standby_users`

---

## Autenticazione con Laravel Sanctum



### 1. Middleware

In `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

---

### 2. Config `sanctum.php`

```php
'stateful' => [
    'localhost',
    'frontend.app',
],
```

---

### 4. CORS

```php
'supports_credentials' => true,
```

---

## Login

Endpoint:

```
POST /api/login
```

Se user è ancora in `standby_users` → ritorna al punto "3 – Completamento registrazione"

Azioni:

* verifica username + password
* `Auth::attempt()`
* ritorna utente autenticato

Sanctum usa:

* cookie HttpOnly
* sessione

---

## Protezione API

### Route API

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (Request $r) => $r->user());
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

Chi non è autenticato riceve **401**.

---

## Protezione Frontend Vue

### Stato auth

* all’avvio SPA chiama:

```
GET /api/me
```

* se 200 → utente loggato
* se 401 → redirect a `/login`

---

### Vue Router Guard

```js
router.beforeEach(async (to) => {
  if (to.meta.requiresAuth) {
    const ok = await checkAuth()
    if (!ok) return '/login'
  }
})
```

