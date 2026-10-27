# 💧 Smart Water Intake Tracker

A simple web app that helps you keep track of how much water you drink each day.
Set a daily goal, log every glass, and watch your progress fill up.

No installation is needed to use the app — just open it in your browser, create an
account, and start tracking.

---

## Contents

1. [What the app does](#what-the-app-does)
2. [Getting started](#getting-started)
3. [Using the dashboard](#using-the-dashboard)
4. [Forgotten password](#forgotten-password)
5. [Quick reference](#quick-reference)
6. [Frequently asked questions](#frequently-asked-questions)
7. [Privacy and security](#privacy-and-security)
8. [Running the project yourself](#running-the-project-yourself)

---

## What the app does

| Feature | What it gives you |
|---------|-------------------|
| Personal account | Your own login, daily goal, and water history. |
| Daily goal | A target you choose, starting at 2,500 ml. |
| Quick add | One-click buttons for 250 ml, 500 ml and 750 ml. |
| Custom amount | Type any amount from 1 to 5,000 ml. |
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
back to the login page with a green confirmation message.

### Log in

1. Type the email address and password you registered with.
2. Click **Log in**.

The dashboard opens with a welcome message and today's date.

---

## Using the dashboard

The dashboard is the main screen. It is made up of five cards.

### Add water

Use the **Add water** card whenever you drink something:

* Click **+ 250 ml**, **+ 500 ml** or **+ 750 ml** for a quick entry.
* Or type an amount in **Custom amount (ml)** and click **Add**. The amount must be a
  whole number between 1 and 5,000 ml.

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
| Quick add buttons | 250 ml, 500 ml, 750 ml |
| Custom entry | 1 – 5,000 ml |
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

**Does my goal affect anyone else?**
No. Every account has its own goal, its own entries, and its own history.

**Why do I have to confirm before deleting?**
So that one accidental tap does not remove a water entry. Click **OK** to delete or
**Cancel** to keep it.

---

## Privacy and security

* Each account can only see, add, and delete **its own** water entries.
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
   `smart_water_tracker` database and its three tables.
4. Check the connection settings at the top of `database.php`. On a default XAMPP install,
   the username is `root` and the password is empty.
5. Open <http://localhost/smart-water-tracker/> in your browser.

If Apache uses port 8080, add it to the address:
<http://localhost:8080/smart-water-tracker/>.

A longer installation and troubleshooting guide is kept separately with the project
handover notes.
