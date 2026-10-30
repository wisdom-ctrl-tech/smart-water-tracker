# 💧 Smart Water Intake Tracker

A simple web app that helps you keep track of how much water you drink each day.
Set a daily goal, scan the QR label on your glass or bottle, and watch your progress
fill up.

No installation is needed to use the app — just open it in your browser, create an
account, and start tracking.

---

## Contents

1. [What the app does](#what-the-app-does)
2. [Getting started](#getting-started)
3. [Scanning your containers](#scanning-your-containers)
4. [Using the dashboard](#using-the-dashboard)
5. [Forgotten password](#forgotten-password)
6. [Quick reference](#quick-reference)
7. [Frequently asked questions](#frequently-asked-questions)
8. [Privacy and security](#privacy-and-security)
9. [Running the project yourself](#running-the-project-yourself)

---

## What the app does

| Feature | What it gives you |
|---------|-------------------|
| Personal account | Your own login, daily goal, and water history. |
| Daily goal | A target you choose, starting at 2,500 ml. |
| QR scan-to-log | Print a label for each container and scan it with a phone camera. The amount is logged without typing. |
| Container manager | Add and delete the glasses and bottles you use. |
| Manual fallback | Quick buttons or a typed amount if a label is not handy. |
| Progress bar | See how much you have drunk today and how much is left. |
| Daily history | Every entry you logged today, with the time and a delete button. |
| Seven-day summary | A simple bar chart of the last seven days. |
| Password reset | A one-time link if you ever forget your password. |

Your daily goal is a personal target, **not** a medical recommendation. You can change it
at any time.

---

## Getting started

### Create an account

1. Open the app in your browser.
2. On the login page, click **Create an account**.
3. Fill in your **full name**, **email address**, **password**, and the password again in
   **Confirm password**.
4. Click **Register**.

Your password must be at least 6 characters long. When the account is created you are sent
back to the login page with a green confirmation message. Every new account also gets two
containers ready to scan: a **250 ml Glass** and a **750 ml Bottle**.

### Log in

1. Type the email address and password you registered with.
2. Click **Log in**.

The dashboard opens with a welcome message and today's date.

---

## Scanning your containers

Instead of typing how much you drank, you scan a QR label that is stuck on the container.

### Set up your containers

1. On the dashboard, click **Containers** in the top bar.
2. Check the two containers that were created with your account. If you use other glasses
   or bottles, add them in **Add a container**: give each one a name and its volume in
   millilitres, then click **Add container**.
3. Click **Print labels**. Each label shows a QR code, the container name, its volume, and
   the code printed underneath.
4. Cut out the labels and stick one on each glass or bottle. Keep them dry.

The **Containers** page also has a **Log now (test)** button next to each label, which is
useful for trying the system without a phone.

### Log water with your phone

1. Make sure your phone and the computer running the app are on the same Wi-Fi network.
2. Open the camera app on your phone.
3. Point it at the QR label on the container you just used.
4. Tap the link that appears on the phone screen.
5. The app logs that container's volume. If you are not logged in on the phone yet, log in
   first and the scan is completed automatically.

If the phone cannot open the link, the labels were probably printed while the app was open
at `localhost`. See [Running the project yourself](#running-the-project-yourself) for the
one-time setup that fixes this.

### Log water without a phone

Open **Scan** in the top bar and either:

* tap one of your containers under **Your containers**, or
* type the code printed under a label into **Container code** and click **Log it**.

The dashboard also keeps the old quick buttons and the **Custom amount** box as a fallback
for when a label is not nearby.

---

## Using the dashboard

The dashboard is the main screen. It is made up of five cards.

### Add water

The **Add water** card is the starting point for every entry:

* Click **Scan a container** to open the scan page.
* Click **Manage containers & print QR labels** to add containers or print their labels.
* If a label is not handy, use **+ 250 ml**, **+ 500 ml** or **+ 750 ml**, or type an
  amount in **Custom amount (ml)** and click **Add**. A manual amount must be a whole
  number between 1 and 5,000 ml.

A green message confirms every entry, and the totals update immediately.

### Your daily progress

The **Today's progress** card shows:

* **Consumed today** — the total you have logged today.
* **Daily goal** — your current target.
* **Remaining** — how much is left to reach the goal.
* A progress bar and the percentage of your goal reached.

The bar stops at 100%, but the total is always shown in full. For example, if your goal is
2,500 ml and you drink 3,100 ml, the bar is full and the card still says 3,100 ml.

A short message under the bar changes with your progress: a gentle reminder when your
intake is low, encouragement once you are halfway, and congratulations when you reach your
goal.

### Change your daily goal

1. In the **Daily goal** card, replace the number with a new goal.
2. Click **Save goal**.

The goal must be between 500 and 10,000 ml. Your new goal is used straight away.

### Today's water history

The **Today's water history** card lists every entry you made today, newest first, with the
amount, the time, and a **Delete** button.

* Click **Delete** on a row to remove that entry.
* A pop-up asks you to confirm first; click **Cancel** and nothing is deleted.
* You cannot edit an entry. If you typed the wrong amount, delete it and add the correct
  one.

### Last 7 days

The **Last 7 days** card shows a bar for each of the last seven days, with the amount above
each bar and the day underneath. The tallest bar belongs to your best day. The total for
the whole week is shown at the bottom.

The detailed list above only shows today, but this chart lets you compare the whole week at
a glance.

---

## Forgotten password

If you cannot remember your password:

1. On the login page, click **Forgot your password?** next to the Password field.
2. Type the email address you registered with and click **Create reset link**.
3. A **Reset your password** link appears on the page. Click it.
4. Type your new password twice and click **Change password**.
5. You are sent back to the login page. Log in with your new password.

Important things to know:

* The link can be used **once** and expires after **30 minutes**.
* Requesting a new link cancels any older links.
* After a successful reset, your old password no longer works.
* The page shows the same confirmation whether or not the email address is registered, so
  nobody can use it to find out who has an account.

---

## Quick reference

| Item | Value |
|------|-------|
| Default daily goal | 2,500 ml |
| Allowed daily goal | 500 – 10,000 ml |
| Default containers | Glass 250 ml, Bottle 750 ml |
| Container volume | 1 – 5,000 ml |
| Label code format | `SWT-` followed by 8 characters |
| Quick add buttons | 250 ml, 500 ml, 750 ml |
| Manual entry | 1 – 5,000 ml |
| Minimum password length | 6 characters |
| Reset link lifetime | 30 minutes, single use |
| History shown in detail | Today only |
| Summary chart | Last 7 days |

---

## Frequently asked questions

**I forgot my password. What do I do?**
Use **Forgot your password?** on the login page. See [Forgotten password](#forgotten-password).

**My reset link says it cannot be used.**
The link has expired, has already been used, or a newer link was requested. Create a new
one from the login page.

**I entered my email but no reset link appeared.**
Check that you typed the same email address you registered with. If you still see no link,
the address is not registered — try creating a new account.

**The progress bar is full but my total is bigger than my goal.**
That is normal. The bar stops at 100%, but the amount you actually drank is always shown in
full.

**Can I change or delete an entry?**
You can delete any of today's entries from the history card. Entries cannot be edited, so
delete the wrong one and add the correct amount.

**Can I see my history from previous days?**
The **Last 7 days** chart shows your daily totals for the past week. The detailed list with
times only shows today.

**Can I use the app on my phone?**
Yes. The layout adjusts to smaller screens, so you can use it in your phone's browser.

**How do I log water without typing?**
Print a QR label for each container from the **Containers** page, stick it on the
container, and scan it with your phone camera. The container's volume is logged
automatically. See [Scanning your containers](#scanning-your-containers).

**The QR link will not open on my phone.**
The labels were probably printed while the app was open at `localhost`, which a phone
cannot reach. Open the **Containers** page using the computer's Wi-Fi address (for example
`http://192.168.1.10/smart-water-tracker/containers.php`) and print again, or set
`APP_BASE_URL` in `database.php` to that address first. Also check that the phone and the
computer are on the same Wi-Fi network.

**The scan page says the code does not belong to me.**
Each label only works for the account that created it. Make sure you are logged in as the
right user, and check that the code under the label matches the one on the **Containers**
page.

**I lost or damaged a label.**
Open the **Containers** page and print the labels again. You can also write the code down:
typing it into the **Scan** page logs the same amount.

**Can I use the same container more than once?**
Yes. A label is not used up. Scan the same glass or bottle every time you drink from it.

**Does my goal affect anyone else?**
No. Every account has its own goal, its own entries, and its own history.

**Why do I have to confirm before deleting?**
So that one accidental tap does not remove a water entry. Click **OK** to delete or
**Cancel** to keep it.

---

## Privacy and security

* Each account can only see, add, and delete **its own** water entries.
* Each container code belongs to one account. Scanning another user's label does not work.
* QR labels contain only the app address and the container code — never your password or
  personal details.
* Passwords are stored as one-way hashes, never as plain text. Nobody — including whoever
  runs the site — can read your password from the database.
* Water amounts and goals are checked on the server, so invalid values are rejected.
* Forms are protected against cross-site request forgery, and anything you type is escaped
  before it is displayed.

---

## Running the project yourself

This section is for whoever hosts the app on their own computer.

**Requirements:** XAMPP (or any setup with Apache, PHP and MySQL/MariaDB).

1. Put the project folder here:

   ```
   C:\xampp\htdocs\smart-water-tracker\
   ```

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open <http://localhost/phpmyadmin/> and import `database.sql`. This creates the
   `smart_water_tracker` database and its four tables (`users`, `water_logs`,
   `containers`, `password_resets`). If you already had the app installed, importing it
   again adds the new `containers` table without deleting your accounts or water entries.
4. Check the connection settings at the top of `database.php`. On a default XAMPP install,
   the username is `root` and the password is empty.
5. Open <http://localhost/smart-water-tracker/> in your browser.

If Apache uses port 8080, add it to the address:
<http://localhost:8080/smart-water-tracker/>.

### Making the QR labels work on a phone

A phone cannot open `localhost`, because that means "this phone". The labels therefore need
the computer's address on the local Wi-Fi network.

1. On the computer, open a Command Prompt and run `ipconfig`. Note the **IPv4 Address** of
   the Wi-Fi adapter, for example `192.168.1.10`.
2. Make sure the phone is connected to the same Wi-Fi network.
3. Either:
   * open the app on the computer as
     `http://192.168.1.10/smart-water-tracker/containers.php` and print the labels, or
   * open `database.php`, set
     `define('APP_BASE_URL', 'http://192.168.1.10/smart-water-tracker');`
     and keep using `localhost` on the computer. The labels will then always use the
     Wi-Fi address.
4. If the phone still cannot connect, allow Apache through the Windows Firewall when
   Windows asks.

You can check the address the labels will use on the **Containers** page, just above the
labels. It also shows a warning when the labels point at `localhost`.

A longer installation and troubleshooting guide is kept separately with the project
handover notes.
