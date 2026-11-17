# 🎤 Presentation Guide

A short plan for the oral presentation. Keep this open on your phone or print it.

## In one sentence

The Smart Water Intake Tracker lets a user set a daily water goal and log drinks by
scanning a QR label on their glass or bottle, so they do not have to type amounts.

## Before the presentation

- [ ] Apache and MySQL are running in XAMPP.
- [ ] The laptop and phone are on the same Wi-Fi network.
- [ ] `APP_BASE_URL` in `database.php` matches the laptop's current Wi-Fi address
      (`ipconfig` → IPv4 Address). If it changed, update it and reprint the labels.
- [ ] You are logged in on the phone as the same account you will use on the laptop.
- [ ] At least one QR label is printed and stuck on a container.
- [ ] Your account has a container (Glass 250 ml or Bottle 750 ml).
- [ ] Open the app on the laptop: <http://localhost/smart-water-tracker/>
- [ ] Have a backup screenshot of the dashboard in case the Wi-Fi fails.

## Demo script (about 5 minutes)

1. **Login** — "This is the login page. Each user has their own account and daily goal."
2. **Dashboard** — "After logging in, the dashboard shows today's total, the goal, and
   the progress bar. Below are today's entries and a chart of the last seven days."
3. **Containers** — "Each container has a name, a volume, and a unique QR code. The
   labels are printed from this page."
4. **Scan with the phone** — "I point the phone camera at the label on the glass. It
   opens the app and logs 250 ml automatically. I refresh the dashboard on the laptop
   and the total goes up."
5. **Fallback** — "If a phone is not available, the Scan page also lets you tap a
   container or type the code printed under the label."
6. **Password reset** — "If a user forgets their password, they can request a one-time
   link. XAMPP has no email server, so the link is shown on the screen."
7. **Finish** — "All data is stored in a MySQL database, so the history is still there
   after logging out."

## How it works

```
Phone camera
    ↓ scans
QR label  →  http://192.168.1.66/smart-water-tracker/scan.php?code=SWT-XXXXXXXX
    ↓
scan.php looks up the container code in the database
    ↓
It inserts the container's volume into water_logs
    ↓
The dashboard reads the new total from the database
```

- The QR code contains a **URL**, not a password or personal data.
- `APP_BASE_URL` is the laptop's Wi-Fi address, which is why the phone can open the link.
- Each container code belongs to one account, so another user's label does not work.

## Database (3 tables)

| Table | What it stores |
|-------|----------------|
| `users` | Name, email, hashed password, daily goal, and the password-reset token |
| `containers` | The user's glasses/bottles: name, volume, and unique code |
| `water_logs` | One row per drink: user, amount, and time |

## What each file does

| File | Purpose |
|------|---------|
| `index.php` | Dashboard: progress, goal, today's history, 7-day chart |
| `scan.php` | Logs a container from a QR link, code box, or container button |
| `containers.php` | Add/delete containers and print QR labels |
| `login.php` / `register.php` / `logout.php` | Account access |
| `forgot-password.php` / `reset-password.php` | Password reset |
| `helpers.php` | Shared functions (water entries, container codes, messages) |
| `auth.php` | Session, login check, HTML escaping |
| `database.php` | Database connection and app settings |
| `database.sql` | Creates the three tables |
| `style.css` / `script.js` | Styling and small interface helpers |

## Likely questions and short answers

**Why did you use QR codes instead of a form?**
Typing every drink is slow and easy to forget. A QR label makes logging one quick scan,
and the amount is always the container's real volume.

**How does the QR code work?**
The label contains a link with the container code. The phone opens `scan.php?code=...`;
PHP finds the container and saves its volume as a water entry.

**Where is the data stored?**
In the `smart_water_tracker` MySQL database, in three tables: users, containers, and
water_logs.

**What happens if the phone cannot connect?**
The phone and laptop must be on the same Wi-Fi. `APP_BASE_URL` must match the laptop's
current Wi-Fi address. If it changes, update it and print the labels again.

**What if there is no phone?**
The Scan page has a code box and container buttons. The code is printed under each QR
label.

**How are passwords protected?**
Passwords are stored with PHP's `password_hash()`, never as plain text. Database queries
use prepared statements.

**How does the password reset work?**
The user enters their email. The app creates a random token, stores its hash and a
30-minute expiry in the `users` table, and shows the link on the page. The link works once.

**What would you add next?**
Email sending, reminders/notifications, hosting so it works outside the local network, and
a monthly report.

## If something goes wrong during the demo

- **QR link does not open:** check the phone is on the same Wi-Fi, then type the code
  printed under the label into the Scan page.
- **Dashboard is empty:** scan a container, use the Scan page's container button, or
  click **Log now** on the Containers page.
- **Page cannot connect to the database:** open XAMPP and start Apache and MySQL.
- **Wi-Fi address changed:** run `ipconfig`, update `APP_BASE_URL` in `database.php`, and
  reprint the labels (or type the code instead).
