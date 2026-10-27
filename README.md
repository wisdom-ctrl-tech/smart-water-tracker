# Smart Water Intake Tracker

A small school-project web application that helps a user keep track of how much water
they drink each day. It is built with plain **HTML**, **CSS**, **JavaScript**, **PHP**
and **MySQL (MariaDB)** running inside **XAMPP**. There are no frameworks, no Composer
and no external libraries.

---

## 1. What the application does

| Page | What it does |
|------|--------------|
| `register.php` | Creates a new account (full name, email, password, confirm password). Validates everything, checks that the email is unique, and stores the password as a **hash**. |
| `login.php` | Logs a user in by checking the email and password against the database. Starts a PHP session and regenerates the session ID on success. |
| `index.php` | The dashboard. Shows the welcome message, the daily goal, today's progress bar, quick "add water" buttons, a custom amount box, a change-goal form, today's water history with a delete button per row, and a seven-day summary. |
| `logout.php` | Destroys the session and sends the user back to the login page. |
| `database.php` | The one place where the database connection and the main settings live. |
| `database.sql` | Creates the `smart_water_tracker` database and its two tables. |
| `auth.php` | A small helper file: starts the session, checks login, and provides the CSRF and HTML-escaping functions. |
| `style.css` | All the styling (blue and white water theme). |
| `script.js` | Three small interface helpers (see below). |

### Behaviour details

* A new account starts with a daily goal of **2,500 ml**.
* Quick add buttons add **250 ml**, **500 ml** and **750 ml**.
* The custom box accepts a whole number of millilitres. It must be between **1 and 5,000 ml**.
* The daily goal can be set to any whole number between **500 and 10,000 ml**.
* Passwords must be at least **6 characters** long.
* The progress bar stops at 100%, but the amount consumed is always shown in full
  (for example "3,100 ml" with a full bar when the goal is 2,500 ml).
* Recommendations are general: low intake gets a gentle reminder, half-way or more gets
  encouragement, and a reached goal gets congratulations.

The default daily goal is just a starting number that the user can change at any time.
It is **not** a medical recommendation.

---

## 2. Project structure

The folder must be named exactly `smart-water-tracker` and must sit inside `htdocs`:

```
C:\xampp\htdocs\smart-water-tracker\
    index.php
    register.php
    login.php
    logout.php
    database.php
    auth.php
    database.sql
    style.css
    script.js
    README.md
```

Everything is in one folder on purpose: there are no sub-folders to keep track of.

---

## Step 1: Download XAMPP

1. Open your web browser and go to the official XAMPP website: **https://www.apachefriends.org/**
2. Click the big **XAMPP for Windows** button. The download for Windows is offered
   directly on the page (the site detects Windows automatically).
3. Choose one of the two Windows downloads:
   * **Installer** — the file that ends in `.exe` (for example
     `xampp-windows-x64-8.2.x-VS16-installer.exe`). **Recommended.** It installs
     everything for you and creates the Start Menu entry.
   * **Portable ZIP** — the file that ends in `.zip`. Only use this if you cannot run
     installers, because you then have to unzip it yourself.
4. Run the file you downloaded (double-click it). Windows may show a warning about an
   unknown publisher; click **More info → Run anyway** if that happens.
5. If a screen about antivirus or User Account Control appears, click **Yes / OK**.
6. On the "Choose Components" screen, leave the default selection as it is. You need at
   least **Apache**, **MySQL**, **PHP** and **phpMyAdmin**.
7. On the "Choose Install Location" screen, set the folder to:

   ```
   C:\xampp
   ```

   Click **Next**. (Do not install it inside `Program Files`, because the spaces in
   that path sometimes cause problems.)
8. On the "Welcome to XAMPP" screen, you can untick the extra offers. Click **Next**,
   then **Finish**. Leave the box "Do you want to start the Control Panel now?" ticked.

**Expected result:** the XAMPP installer finishes and the XAMPP Control Panel window
opens.

---

## Step 2: Start the required services

You need two services running at the same time: **Apache** (the web server) and
**MySQL** (the database server).

1. Open the XAMPP Control Panel:
   * If it is already open, skip to point 2.
   * Otherwise click the Windows **Start** button, type `XAMPP`, and click
     **XAMPP Control Panel**.
   * Or open the folder `C:\xampp` and double-click `xampp-control.exe`.
2. In the Control Panel you will see a list of modules. Find **Apache** and **MySQL**.
3. Click **Start** next to **Apache**.
4. Click **Start** next to **MySQL**.
5. **What a successful start looks like:**
   * The word next to each module becomes green: **Apache** turns green with a running
     port (for example `80, 443`) and **MySQL** turns green with the port `3306`.
   * The **Start** buttons become **Stop** buttons.
   * In the log window at the bottom you see lines such as
     `Apache started [Port 80]` and `MySQL started [Port 3306]`.
6. Optional but useful: click **Config → Service and Port Settings** later if you ever
   need to change the ports.

**If a service does not start:**

*Apache will not start*
* The usual cause is that port 80 is already used by another program (Skype, IIS, or
another web server). Click **Config** next to Apache → **Apache (httpd.conf)**. Find
`Listen 80` and change it to `Listen 8080`. Then click **Stop** and **Start** again.
After this, all web addresses need the port, for example
`http://localhost:8080/smart-water-tracker/`.
* Click the **Netstat** button in the Control Panel to see which program is holding
port 80.
* Look at the last lines of `C:\xampp\apache\logs\error.log` — it usually names the exact
problem.

*MySQL will not start*
* Port 3306 is probably already used by another MySQL/MariaDB installation. Check in
Windows **Services** (`Start` → type `services`) whether a service named **MySQL** is
running and stop it, or change XAMPP's MySQL port in **Config → my.ini** (change both
`port= 3306` lines to `port= 3307`) and then update `DB_SERVER` in `database.php` to
`localhost:3307`.
* Look at `C:\xampp\mysql\data\mysql_error.log`. The last lines tell you what is wrong.
* Never open the same XAMPP MySQL data folder with two different MySQL servers at once.

---

## Step 3: Create the database

1. Make sure **Apache** and **MySQL** are both green in the XAMPP Control Panel.
2. Open your browser and go to: **http://localhost/phpmyadmin/**
3. The phpMyAdmin home page appears with a list of databases on the left.
4. Click the **Databases** tab at the top (or **New** in the left sidebar).
5. In the "Create database" box, type the exact name:

   ```
   smart_water_tracker
   ```

6. In the **Collation** dropdown next to it, choose:

   ```
   utf8mb4_unicode_ci
   ```

7. Click **Create**.
   *Expected result:* the message "Database smart_water_tracker has been created" and
   the new database appears in the left sidebar.
8. Click **smart_water_tracker** in the left sidebar so it is selected (the page title
   becomes "Database: smart_water_tracker").
9. Click the **Import** tab at the top.
10. Click **Choose File** (also called *Browse*), and select the file
    `database.sql` from your project folder:

    ```
    C:\xampp\htdocs\smart-water-tracker\database.sql
    ```

11. Leave "Character set of the file" as `utf-8` and "Format" as `SQL`.
12. Scroll to the bottom and click **Go** (or **Import**).
    *Expected result:* a green message starting with
    "Import has been successfully finished" appears at the top (the number of queries it
    mentions depends on your phpMyAdmin version).
13. **Verify the tables:** click **smart_water_tracker** in the left sidebar. Under it you
    should now see exactly two tables: **users** and **water_logs**.

The import also works if the database does not exist yet: `database.sql` begins with
`CREATE DATABASE IF NOT EXISTS smart_water_tracker`. If phpMyAdmin says the import
succeeded but you see no tables, you forgot to select the database in step 8 — select it
and import again.

> The two tables are:
> * `users` — `id`, `name`, `email` (unique), `password` (hash), `daily_goal`
>   (default 2500), `created_at`
> * `water_logs` — `id`, `user_id` (foreign key to `users.id`), `amount_ml`, `logged_at`

---

## Step 4: Place the project files

1. Open File Explorer and go to:

   ```
   C:\xampp\htdocs\
   ```

   `htdocs` is the folder that Apache publishes as the website.
2. The project folder must be called `smart-water-tracker` and must contain the files
   directly (not inside another extra folder). The result must look exactly like this:

   ```
   C:\xampp\htdocs\smart-water-tracker\index.php
   C:\xampp\htdocs\smart-water-tracker\register.php
   C:\xampp\htdocs\smart-water-tracker\login.php
   C:\xampp\htdocs\smart-water-tracker\logout.php
   C:\xampp\htdocs\smart-water-tracker\database.php
   C:\xampp\htdocs\smart-water-tracker\auth.php
   C:\xampp\htdocs\smart-water-tracker\database.sql
   C:\xampp\htdocs\smart-water-tracker\style.css
   C:\xampp\htdocs\smart-water-tracker\script.js
   C:\xampp\htdocs\smart-water-tracker\README.md
   ```

   A common mistake is ending up with
   `C:\xampp\htdocs\smart-water-tracker\smart-water-tracker\index.php`. If that happens,
   open the inner folder, select everything (**Ctrl+A**), cut it (**Ctrl+X**) and paste it
   into `C:\xampp\htdocs\smart-water-tracker`.
3. Open `C:\xampp\htdocs\smart-water-tracker\database.php` with Notepad (right-click →
   **Open with** → **Notepad**). Near the top you see these settings:

   ```php
   define('DB_SERVER', 'localhost');           // MySQL server
   define('DB_USERNAME', 'root');              // XAMPP default MySQL username
   define('DB_PASSWORD', '');                  // XAMPP default MySQL password is empty
   define('DB_NAME', 'smart_water_tracker');   // database created in Step 3
   define('DB_CHARSET', 'utf8mb4');
   ```

   * `DB_USERNAME` — on a normal XAMPP installation this is `root`.
   * `DB_PASSWORD` — on a normal XAMPP installation this is **empty** (nothing between
     the two quotes). This is correct, not a mistake.
   * `DB_NAME` — must be `smart_water_tracker`.
   * `DB_SERVER` — leave it as `localhost`.

   **If your MySQL username or password is different**, type your own values between the
   quotes, for example `define('DB_PASSWORD', 'mysecret');`. If you changed the MySQL port
   to 3307 in Step 2, write `define('DB_SERVER', 'localhost:3307');`.
4. Save the file (**Ctrl+S**) and close Notepad.

### Time zone setting (please check this)

Also in `database.php` there is:

```php
define('APP_TIMEZONE', 'Asia/Kolkata');
```

This decides which water entries count as "today" and which days appear in the
seven-day summary. If you do not live in the India time zone, change this to your own
zone, for example `'Europe/London'`, `'America/New_York'` or `'Asia/Manila'`.
A list of valid names is at https://www.php.net/manual/timezones.php. If this value is
wrong, entries added just after midnight may be counted on the previous day.

---

## Step 5: Run the website

1. Check the XAMPP Control Panel: **Apache** and **MySQL** must both be green and running.
2. Open your browser and go to:

   **http://localhost/smart-water-tracker/**

   That address opens `index.php` automatically (it is the first page of the folder).
3. **Expected result:** you are not logged in yet, so the page sends you straight to the
   **login page** (`http://localhost/smart-water-tracker/login.php`). You see a blue page
   with a water-drop icon, the title "Smart Water Intake Tracker", an "Email address"
   box, a "Password" box, a blue **Log in** button, and the link **Create an account**.
4. If you type the address without a trailing slash (`.../smart-water-tracker`), Apache
   still redirects you correctly.

**If the page does not appear, check these things in this order:**

* Is the address spelled exactly right, including the hyphens?
  `smart water tracker` with spaces or `smart_water_tracker` with underscores will give a
  404 error.
* Is the folder really named `smart-water-tracker` and is it directly inside
  `C:\xampp\htdocs`? (See Step 4.)
* Is Apache green in the Control Panel? If it failed to start, look at the Apache
  section of Step 2.
* If you changed Apache's port to 8080, use
  `http://localhost:8080/smart-water-tracker/` instead.
* If you see a database message instead of the login page, continue with Step 8
  ("Database connection errors").

---

## Step 6: Test all features

Do these ten tests in order. Each one tells you what you should see.

**1. Register a new account**
1. On the login page click **Create an account**.
2. Type your full name, an email such as `student@example.com`, a password of at least
   6 characters, and the same password again in **Confirm password**.
3. Click **Register**.
4. *Expected result:* you are sent to the login page and see the green message
   "Your account was created successfully. You can log in now."
5. Try it once more with the same email to see the validation: this time you get the red
   message "That email address is already registered. Please log in instead."
   You can also try typing two different passwords, or a password shorter than 6
   characters, to see the other error messages.

**2. Log in**
1. Type the email and the password you just registered.
2. Click **Log in**.
3. *Expected result:* the dashboard opens at
   `http://localhost/smart-water-tracker/index.php`, with "Welcome back, *your name*!"
   at the top and a **Log out** button in the blue bar.

**3. Add 250 ml of water**
1. In the **Add water** card, click the **+ 250 ml** button.
2. *Expected result:* the page reloads with the green message "Added 250 ml of water.
   Keep it up!", and "Consumed today" changes from 0 ml to 250 ml.

**4. Add a custom amount**
1. In the same card, type `350` in the **Custom amount (ml)** box and click **Add**.
2. *Expected result:* the green message "Added 350 ml of water" appears and the total
   becomes 600 ml.
3. To see the validation, type `0` (or `-100`, or `99999`) and click **Add**.
   *Expected result:* a red message asking for a whole number between 1 and 5000.

**5. Check the progress bar and the recommendation**
1. Look at **Today's progress**: you see "Consumed today", "Daily goal" (2,500 ml) and
   "Remaining".
2. The blue bar is partly filled and the caption under it shows the percentage.
3. Add water until you pass 2,500 ml.
   *Expected result:* the bar is full but never wider than the card, the real total is
   shown (for example 2,750 ml), "Remaining" shows 0 ml, and the message changes to
   "Congratulations! You reached your daily goal of 2,500 ml today. Great work!"

**6. Change the daily goal**
1. In the **Daily goal** card, replace `2500` with `3000` and click **Save goal**.
2. *Expected result:* the green message "Your daily goal is now 3,000 ml." and the
   "Daily goal" number becomes 3,000 ml.
3. Type `100` and click **Save goal** to see the error message (the value must be between
   500 and 10,000).

**7. Delete a water log**
1. In **Today's water history** every entry has a **Delete** button on the right.
2. Click **Delete** on one row.
3. *Expected result:* a browser pop-up asks "Delete this water entry?" Click **OK**. The
   row disappears, the green message "Water entry deleted." appears, and the total goes
   down. (Click **Cancel** in the pop-up and nothing is deleted.)

**8. View the seven-day summary**
1. Scroll to the **Last 7 days** card at the bottom.
2. *Expected result:* seven bars, one per day, with the amount in ml above each bar, the
   day name under it, and the total of all seven days at the bottom
   ("Total for the last 7 days: ... ml").
3. Log water on a second day (or just look after using the app for a few days) to see the
   bars change. The tallest bar belongs to the day with the most water.

**9. Log out**
1. Click **Log out** in the top blue bar.
2. *Expected result:* you are returned to the login page.

**10. Confirm that a logged-out user cannot open the dashboard**
1. After logging out, type `http://localhost/smart-water-tracker/index.php` directly in
   the address bar and press Enter.
2. *Expected result:* you are immediately sent back to the login page and you never see
   the dashboard. (This is the proof that the session protection works.)

---

## Step 7: Verify that MySQL is really storing the data

1. Go to **http://localhost/phpmyadmin/**
2. In the left sidebar click **smart_water_tracker**.
3. Click the **users** table.
4. Click the **Browse** tab at the top.
   *Expected result:* one row for every account you registered, showing `id`, `name`,
   `email`, `password`, `daily_goal` (2500 by default) and `created_at`.
5. Look at the **password** column. It must contain something like

   ```
   $2y$10$wO0v3Xh4kQ9uJ2sZ0m7dOe... (about 60 characters)
   ```

   This is the **hash**, not the real password. The original password is never stored, and
   you cannot read it back from this column — that is exactly what is supposed to happen
   and it is worth mentioning during your presentation.
6. Click the **water_logs** table, then the **Browse** tab.
   *Expected result:* one row for every glass of water you added, with `id`, `user_id`
   (the `id` of the account it belongs to), `amount_ml` and `logged_at` (date and time).
   Add more water on the dashboard, refresh this page, and the new rows appear.
7. Click the **Structure** tab of `water_logs` to show the foreign key
   `fk_water_logs_user` and the index `idx_water_logs_user_date` in your presentation.

---

## Step 8: Troubleshooting

Work through the matching section below. Do **not** reinstall XAMPP as a first step —
almost every problem here has a smaller cause.

### Apache refuses to start
* **Where to look:** XAMPP Control Panel log window, and `C:\xampp\apache\logs\error.log`.
* **What to check:** the message usually says `Address already in use: make_sock: could not
  bind to address 0.0.0.0:80`. That means port 80 is taken (often by Skype, IIS, or
  another web server).
* **Next action:** close the other program, or click **Config → Apache (httpd.conf)**,
  change `Listen 80` to `Listen 8080`, save, then **Stop** and **Start** Apache. Remember
  to add `:8080` to every web address afterwards.

### MySQL refuses to start
* **Where to look:** `C:\xampp\mysql\data\mysql_error.log`.
* **What to check:** `Another mysqld process is already running` or
  `Do you already have another mysqld server running on port 3306?`.
* **Next action:** in Windows **Services**, stop any other service named *MySQL*; or in the
  Control Panel click **Config → my.ini**, change `port= 3306` to `port= 3307` (there are
  two places), restart MySQL, and set `DB_SERVER` in `database.php` to
  `'localhost:3307'`. If the log mentions a corrupted table, delete the newest `.err`
  file only after making a copy of the whole `C:\xampp\mysql\data` folder.

### Database connection errors
* **Where to look:** the page shows "Sorry, the website cannot connect to the database."
* **What to check:** (1) Is MySQL green in the Control Panel? (2) Does the database
  `smart_water_tracker` exist in phpMyAdmin? (3) Do `DB_USERNAME`, `DB_PASSWORD`,
  `DB_NAME` and `DB_SERVER` in `database.php` match your installation? (4) Did you save
  `database.php` after changing it?
* **Next action:** fix the value in `database.php`, save, and reload the page. About the
  password: on a default XAMPP install it is empty (`''`), so `define('DB_PASSWORD', '')`
  is correct and is not a mistake.

### Missing database tables
* **Where to look:** phpMyAdmin → `smart_water_tracker` → do you see **users** and
  **water_logs**? If you see an error such as `Table 'smart_water_tracker.users' doesn't
  exist`, this is the case.
* **What to check:** the import in Step 3 was not done, or was done while no database was
  selected.
* **Next action:** select `smart_water_tracker` in the left sidebar, open the **Import**
  tab, choose `database.sql` again and click **Go**. The script uses
  `CREATE TABLE IF NOT EXISTS`, so importing it twice is safe.

### HTTP 404 errors ("Object not found!")
* **Where to look:** the address bar and the exact folder name.
* **What to check:** the URL must be `http://localhost/smart-water-tracker/`. Also check
  that `C:\xampp\htdocs\smart-water-tracker\index.php` really exists.
* **Next action:** correct the spelling (hyphens, no spaces), make sure the folder is not
  nested twice (see Step 4), and if you changed Apache's port, add `:8080`.

### PHP syntax errors
* **What to look for:** a message such as
  `Parse error: syntax error, unexpected ... in C:\xampp\htdocs\smart-water-tracker\index.php on line 42`.
* **What to check:** that file and that line number. It is nearly always a missing `;`,
  a missing `}` or an extra `}`.
* **Next action:** open the file in Notepad, fix that line, save, and reload the page. If
  you did not edit any file, re-copy the original file — nobody should have to guess.
* You can also check a file from the command line without opening a browser:
  open `C:\xampp` in a terminal (type `cmd` in the address bar) and run
  `php\php.exe -l htdocs\smart-water-tracker\index.php`. It prints either
  "No syntax errors detected" or the exact problem.

### A blank white page
* **Where to look:** `C:\xampp\apache\logs\error.log` and `C:\xampp\php\logs\php_error.log`.
  These files hold the real error message.
* **What to check:** the usual causes are a fatal PHP error (for example a typo in
  `database.php`), or PHP is not running the file as PHP. A blank page plus "0 bytes" in
  the browser means the script died at the very start.
* **Next action:** read the last lines of `error.log`, fix the reported line, reload.
  Temporarily adding `<?php phpinfo(); ?>` in a small test file (`test.php`) tells you
  whether PHP itself works at all.

### Registration or login failing
* **Where to look:** the red error message on the page, then phpMyAdmin → `users`.
* **What to check and what to do:**
  * "That email address is already registered" → the email is in the `users` table. Use a
    different email or log in with the existing one.
  * "Incorrect email address or password" → the email is not in the table, or the password
    is wrong. Open the same email in phpMyAdmin to confirm the spelling.
  * The form seems to do nothing and you are sent back to an empty form → your browser is
    blocking cookies, or you left the page open for a very long time so the session
    expired. Close the tab, open the site again, and try once more. (The app uses sessions,
    so cookies must be allowed for `localhost`.)
  * The user is created but logging in always fails → the `users` table has a different
    structure than expected. Compare it with Step 3 and import `database.sql` again into a
    fresh database.
  * `Fatal error: Call to undefined function mb_strlen()` → the **mbstring** extension is
    switched off. Open `C:\xampp\php\php.ini`, find the line `;extension=mbstring`, remove
    the leading `;`, save, and restart Apache. (XAMPP has it enabled by default.)

---

## Step 9: Back up the project

Make a copy of **two** things: the files and the database.

1. **The files:** open File Explorer, go to `C:\xampp\htdocs`, right-click the
   `smart-water-tracker` folder and choose **Copy**. Go to the place where you keep your
   school work (for example `D:\SchoolProject` or a USB stick), right-click and choose
   **Paste**. You now have `D:\SchoolProject\smart-water-tracker\...`.
2. **The database:**
   1. Open **http://localhost/phpmyadmin/**
   2. Click **smart_water_tracker** in the left sidebar.
   3. Click the **Export** tab at the top.
   4. Leave "Export method" on **Quick** and "Format" on **SQL**.
   5. Click **Export** (or **Go**). Your browser downloads a file such as
      `smart_water_tracker.sql`. Save it next to the copied project folder.
3. To restore later, first copy the project folder back to `C:\xampp\htdocs`, then in
   phpMyAdmin select (or create) the database `smart_water_tracker`, open the **Import**
   tab, choose the exported `.sql` file and click **Go**.

Tip: do this backup right before your presentation, so you always have a version that is
known to work.

---

## Step 10: Starting the project again

Whenever you want to continue working or show the project:

1. Open the **XAMPP Control Panel** (Start button → type `XAMPP` → **XAMPP Control Panel**,
   or double-click `C:\xampp\xampp-control.exe`).
2. Click **Start** next to **Apache**, then **Start** next to **MySQL**. Wait until both
   are green.
3. Open your browser and go to **http://localhost/smart-water-tracker/**
   (or `http://localhost:8080/smart-water-tracker/` if Apache uses port 8080).
4. Log in with an account you created earlier, or click **Create an account**.
5. When you have finished, in the XAMPP Control Panel click **Stop** next to **MySQL** and
   then **Stop** next to **Apache**. The data stays saved in MySQL, so nothing is lost.

---

## How the code is kept secure (short version for the presentation)

* Passwords are stored with `password_hash()` and checked with `password_verify()`; the
  real password is never saved or shown.
* Every database query uses **prepared statements** (`prepare` + `bind_param`), so typed
  input can never become SQL.
* Login state is kept in a **PHP session**, and `session_regenerate_id(true)` is called
  after login.
* `index.php` starts with `require_login()`, so a logged-out visitor is redirected to the
  login page.
* Every query on `water_logs` includes `AND user_id = ?`, so a user can only ever see,
  delete or count their own entries.
* All forms that change something use **POST** and contain a **CSRF token** that is
  compared with `hash_equals()`.
* Anything typed by a user is printed with `htmlspecialchars()` (the `e()` helper), so
  HTML cannot be injected into the page.
* Water amounts and the daily goal are validated on the server: amounts between 1 and
  5000 ml, goals between 500 and 10000 ml.
* Database errors are never printed on the page; the visitor sees a simple message
  instead.
