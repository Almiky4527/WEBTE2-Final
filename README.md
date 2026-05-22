# Docker Setup

Dockerfile for PHP + Octave + Laravel pre-installed

`php/Dockerfile`
```Dockerfile
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add Composer global bin to PATH so we can run laravel installer anywhere
ENV PATH="/root/.config/composer/vendor/bin:${PATH}"

# Install Laravel installer globally
RUN composer global require laravel/installer
```

Laravel installer will be installed globally.

# Laravel + Vue Setup

```bash
sudo docker exec -it dev_php_custom bash
laravel new <dir>
```

Postup zadavania veci ako si ich laravel setup pyta:
- Starter kit: Vue
- Authentication provider: Laravel's built-in authentication
- Teams support: No
- Testing framework: PHPUnit (doesnt matter really)
- Laravel Boost: No
- Would you like to run `npm install` ?: Yes
	- or say No and manually:
```bash
cd <dir>
npm install
npm run build
```

However, when you open `http://localhost:8080/` in browser you will get something like `tempnam(): file created in the system's temporary directory`, bcs Laravel has **insufficient permissions**, so run:

```bash
chown -R www-data:www-data storage database bootstrap/cache/
chmod -R 777 storage database bootstrap/cache/
```


Project is now set up with **Laravel + Inertia + Vue starter kit** which has Inertia configured + bunch of stuff pre-installed to showcase things.


# Implementation

### Stack
  - **Backend:** PHP 8.3 / Laravel, running under `php-fpm` behind `nginx`.
  - **Frontend:** Vue 3 + TypeScript, served through Inertia.js, built with Vite.
  - **Database:** MariaDB 11 (sessions, users, logs, animation usage stats).
  - **Compute:** the `octave` CLI, invoked from PHP via Symfony Process.
  - **Infrastructure:** `docker-compose` brings up `nginx`, `php`, `db` and
    `phpmyadmin`. The app is reachable on `http://localhost:8080`.

  ### Pages (Inertia)
  | Route | Component | Purpose |
  |---|---|---|
  | `/` | `Welcome.vue` | Landing page |
  | `/console` | `Console.vue` | REPL-style Octave console |
  | `/ball-beam` | `BallBeam.vue` | Ball & Beam animation |
  | `/pendulum` | `Pendulum.vue` | Inverted pendulum animation |
  | `/stats` | `Stats.vue` | Usage statistics dashboard |
  | `/api-docs` | `ApiDocs.vue` | OpenAPI documentation viewer |

  ### Octave console (`/console`)
  The console UI (`components/octave/*`) sends code to `POST /api/octave/eval`.
  On the server, `OctaveController::evaluate`:

  1. checks the **session unlock flag** (set via `POST /api/octave/unlock` with a
     shared password); locked sessions get `401`,
  2. validates the snippet against a small blocklist of dangerous calls
     (`system`, `popen`, `fork`, file I/O, …),
  3. wraps the user code in a **prelude + postlude** that loads the workspace,
     disables figures, runs the code, captures any figure as PNG, and saves the
     resulting workspace,
  4. spawns `octave --eval` through Symfony Process with a 30 s timeout,
  5. returns JSON containing `output`, `stderr`, `workspace` and a base64
     `figure`.

  The Vue side (`useOctaveEval`, `useOctaveWorkspace`, `useOctaveUnlock`)
  persists the workspace blob on the client, so successive evaluations share
  variable state, and re-prompts for the password if the session expires.

  ### Animations (`/ball-beam`, `/pendulum`)
  Each page renders a parameter form and a `<canvas>`. The composable
  `useAnimSim` calls `GET /api/octave/ball` or `/api/octave/pendulum` with the
  chosen physical parameters. `AnimController` assembles a state-space model
  (`A`, `B`, `C`, `D`), designs a controller (pole placement for the ball,
  LQR for the pendulum), runs `lsim`, and returns the sampled trajectory
  `{t, y, x}` as JSON. The frontend then drives a `requestAnimationFrame` loop
  that interpolates between samples to draw a smooth animation. Internally the
  animation endpoints reuse `OctaveController::evaluate`, so they share the
  same sandboxing, unlock check and process handling as the console.

  ### Logging, stats and export
  - The `LogOctaveApi` middleware writes one `OctaveLog` row per `/eval` call
    (IP, status, timing).
  - The `LogAnimStats` middleware aggregates animation hits into
    `anim_usage_stats` over a configurable time window (`config/octave.php`).
  - `LogExportController` exposes `GET /api/octave/logs` (JSON) and
    `GET /api/octave/logs.csv` for download; both require an unlocked session.
  - `Stats.vue` visualizes recent activity.

  ### API documentation
  The REST API is described in an OpenAPI YAML file served at
  `GET /api/docs.yaml` and rendered in `ApiDocs.vue`; a printable PDF version
  is available at `GET /api/docs.pdf`.

  ### Configuration
  `config/octave.php` centralizes the unlock password, optional API token,
  process throttling (`sleep_us`) and stats time-frame. Credentials are
  provided via the `.env` file.




# Sequence diagram — Console request flow



  ```mermaid
  sequenceDiagram
      autonumber
      actor U as Pouzivatel
      participant V as Console.vue
      participant ED as OctaveEditor.vue
      participant UD as UnlockDialog.vue
      participant CE as useOctaveEval
      participant CU as useOctaveUnlock
      participant CW as useOctaveWorkspace
      participant R as routes/web.php
      participant MW as LogOctaveApi
      participant OC as OctaveController
      participant OCT as octave CLI
      participant DB as OctaveLog
      participant OUT as OutputPanel.vue

      U->>V: Otvori /console
      V->>ED: Zobrazi editor a tlacidlo Run
      U->>ED: Napise kod a klikne Run
      ED->>CE: run(code)

      CE->>CU: ensureUnlocked()
      alt Konzola je zamknuta
          CU-->>CE: false
          CE->>UD: promptForUnlock()
          U->>UD: Zada heslo
          UD->>R: POST /api/octave/unlock
          R->>OC: unlock()
          OC-->>UD: 200 + session flag
          UD-->>CE: unlocked = true
      end

      CE->>CW: nacitaj workspace blob
      CE->>R: POST /api/octave/eval
      R->>MW: LogOctaveApi::handle
      MW->>OC: evaluate(Request)

      OC->>OC: validate_code a workspace
      OC->>OC: vytvor temp subory
      OC->>OCT: octave --eval (prelude + code + postlude)
      OCT-->>OC: stdout, stderr, wsOut, figOut
      OC->>OC: remove_octave_shutdown_error
      OC-->>MW: JSON output stderr workspace figure
      MW->>DB: zapis OctaveLog
      MW-->>CE: HTTP 200 + JSON

      CE->>CW: setWorkspace
      CE-->>ED: EvalResult
      ED->>OUT: vykresli output stderr figuru
      OUT-->>U: Zobrazeny vysledok
  ```

# Sequence diagram — Animation request flow (Ball & Beam / Pendulum)

  ```mermaid
  sequenceDiagram
      autonumber
      actor U as Pouzivatel
      participant P as BallBeam.vue / Pendulum.vue
      participant F as Formular parametrov
      participant CS as useAnimSim
      participant CU as useOctaveUnlock
      participant R as routes/web.php
      participant MW as LogAnimStats
      participant AC as AnimController
      participant OC as OctaveController
      participant OCT as octave CLI
      participant DB as OctaveLog
      participant CV as Canvas + RAF loop

      U->>P: Otvori /ball alebo /pendulum
      P->>F: Zobrazi parametre (m, R, g, ...)
      U->>F: Zmeni parametre a klikne Simuluj
      F->>CS: run(params)

      CS->>R: GET /api/octave/ball?... alebo /pendulum?...
      R->>MW: LogAnimStats::handle
      MW->>AC: run_ball() / run_pendulum()

      AC->>AC: validate(params) + doplnenie defaultov
      AC->>AC: zlozi octave skript (A, B, C, D, K, lsim, jsonencode)
      AC->>OC: evaluate(Request)

      alt Session je zamknuta
          OC-->>AC: 401 Console is locked
          AC-->>MW: 401
          MW-->>CS: 401
          CS->>CU: markLocked()
          CS-->>F: { locked: true }
          F-->>U: Vyzva na odomknutie (/console)
      else Session odomknuta
          OC->>OCT: octave --eval (prelude + skript + postlude)
          OCT-->>OC: stdout (JSON struct t,y,x), stderr
          OC-->>AC: JSON { output, stderr, workspace, figure }
          AC->>AC: parseSeries() - vyrezanie JSON z output
          AC-->>MW: JSON { success, t, y, x }
          MW->>DB: zapis OctaveLog (anim hit)
          MW-->>CS: HTTP 200 + series

          CS->>CS: series.value = { t, y, x }
          CS-->>F: AnimRunResult { success, series }
          F->>CV: spusti requestAnimationFrame loop
          loop Pre kazdy frame
              CV->>CV: interpoluj stav podla casu t
              CV->>CV: vykresli scenu (loptu / kyvadlo)
          end
          CV-->>U: Plynula animacia
      end
  ```

