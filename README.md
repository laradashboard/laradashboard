<img width="100%" alt="Lara Dashboard" src="https://github.com/user-attachments/assets/c56009a4-718f-43dc-bd1e-caad5417b05b"  />


**Lara Dashboard** - A project which manages Role, Permissions and every actions of your Laravel application. A complete solution for Role based Access Control in Laravel with Tailwind CSS integrated with all starting features including dark/lite mode, charts, tables, logs, forms and so on...

**Demo:** https://demo.laradashboard.com/
```
Email - superadmin@example.com
password - 12345678
```

## Requirements:
- Laravel `7.x` | `9.7` | `11.x` | `12.x`
- Spatie role permission package  `^6.4`
- PHPUnit test package `^11.x`
- Tailwind CSS >= 4.x
- Laravel Modules - https://laravelmodules.com/docs/12/getting-started/introduction
- Laravel Events (A WordPress like action/filter hooks) - https://github.com/tormjens/eventy

## Versions:
- Laravel `7.x` & PHP -`7.x`
    - Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel7.x
    - Branch - https://github.com/ManiruzzamanAkash/laravel-role/tree/Laravel7.x

- Laravel `9.7` & PHP - `8.x`
    - Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel9.x

- Laravel `11.x`
    - Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v11.x-main

- Laravel `12.x` & PHP >= `8.3`
    - Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x

- Laravel `12.x` & Tail Admin Template Integration
    - Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x-tailadmin

- Laravel `12.x` & Module & Action Log integration
    - Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x-module-logs
 
## Project Setup
**Clone and Go Project**
```console
git clone git@github.com:laradashboard/laradashboard.git
cd laradashboard
```

**Install Composer & Node Dependencies**
```console
composer install
npm install
```

**Database & env creation**
- Create database called - `laradashboard`
- Create `.env` file by copying `.env.example` file

**Generate Artisan Key or necessary linkings**
```console
php artisan key:generate
php artisan storage:link
```

**Migrate Database with seeder**
```console
php artisan migrate:fresh --seed && php artisan module:seed
```

**Run Project**
```php
php artisan serve
npm run dev
```

So, You've got the project of Laravel Role & Permission Management on your http://localhost:8000

## Previously From laravel-role
We were previously at https://github.com/ManiruzzamanAkash/laravel-role, so you need to change the URL if you moved from there
```console
git remote set-url origin git@github.com:laradashboard/laradashboard.git
```

## How it works
1. Login using Super Admin Credential -
    1. Email - `superadmin@example.com`
    1. Password - `12345678`
1. Forget password - Password forget and reset will work if email is setupped properly
1. Create User
1. Create Role
1. Assign Permission to Roles
1. Assign Multiple Role to an User
1. Check by login with the new credentials.
1. If you've not enough permission to do any task, you'll get a warning message.
1. Dashboard with Beautiful chart integrated
1. Module Based Development - Custom Module Add/Enable/Disable/Delete
1. Monitoring - Logging of every action of your application
1. Monitoring - Laravel Pulse

## Email setup
You can use mailtrap to test emails easily - https://mailtrap.io/ (first need to create mailtrap account and and can )

```bash
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=mailtrap-username
MAIL_PASSWORD=mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=youremail@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Documentation
https://laradashboard.com/docs/

### Login & Dashboard Page
**Login Page**
![alt text][userLoginImage]

**Forget password Page (Dark mode)**
![alt text][userForgetPasswordImage]

**Dashboard Page Lite Mode**
![alt text][dashboardImage]

**Dashboard Page Dark Mode**
![alt text][dashboardDarkMode]

### Role Pages
**Role List**
![alt text][roleListImage]
**Role Create**
![alt text][roleCreateImage]
**Role Edit**
![alt text][roleEditImage]

### Users Pages
**Users List**
![alt text][userListImage]
**User Create**
![alt text][userCreateImage]

### Modules Page
**Module List**
![alt text][moduleListPage]

**Upload Module**
![alt text][moduleCreatePage]

### Settings Pages
**General Settings**
![alt text][generalSettingsImage]

**Site Appearance**
![alt text][siteAppearanceImage]

**Content Settings**
![alt text][contentSettingsImage]

### Monitoring Pages
**Action Logs**
![alt text][actionLogsPage]

**Laravel Pulse**
![alt text][laravelPulsePage]

### Other Pages
Custom Error Pages
![alt text][errorPageImage]


[dashboardImage]: /demo-screenshots/Dashboard%20Page%20white%20Mode.png "Dashboard Page Laravel Role Management"
[dashboardDarkMode]:  /demo-screenshots/Dashboard%20Page%20Dark%20Mode.png "Dashboard Page Dark Mode"
[roleListImage]: /demo-screenshots/Role%20List.png "2-Laravel-Manage-Roles"
[roleCreateImage]: /demo-screenshots/Role%20Create.png "3-Laravel-Role-Create"
[roleEditImage]: /demo-screenshots/Role%20Edit.png "4-Laravel-Role-Edit"
[userListImage]:  /demo-screenshots/Users%20List.png "5-Laravel-Users-Manage" 
[userCreateImage]: /demo-screenshots/User%20Create.png "6-Laravel-User-Create"
[userLoginImage]: /demo-screenshots/login.png "7-Login-Page"
[userForgetPasswordImage]: /demo-screenshots/Forget-password.png "Forget Password"
[errorPageImage]: /demo-screenshots/Custom%20Error%20Pages.png "8 - Error Page Handling"
[moduleListPage]: /demo-screenshots/Module%20List.png
[moduleCreatePage]: /demo-screenshots/Upload%20Module.png
[actionLogsPage]: /demo-screenshots/Action%20Log%20List.png
[laravelPulsePage]: /demo-screenshots/Laravel%20Pulse%20Dashboard%20for%20Monitoring.png
[generalSettingsImage]: /demo-screenshots/Settings-General.png "General Settings Page"
[siteAppearanceImage]: /demo-screenshots/Settings-Site-Appearance.png "Site Appearance Page"
[contentSettingsImage]: /demo-screenshots/Settings-Content.png "Content Settings Page"

## Live Demo
https://demo.laradashboard.com

## Articles
- How to setup - https://devsenv.com/tutorials/laravel-role-permission-management-system-full-example-with-source-code

## Wanna talk with me
Please mail me at - manirujjamanakash@gmail.com

## Premium Features
Please visit at Lara Dashboard to get more premium moduels - https://laradashboard.com. Premium modules included CRM, HRM, Course Managements and so on.

## Core modules
- **User Avatar** - https://github.com/laradashboard/UserAvatar - A very simple module create an avatar for a user. Handle migration, entries/updates in user forms and so on. 

## Support
If you like my work you may consider buying me a ☕ / 🍕

<a href="https://www.patreon.com/maniruzzaman" target="_blank" title="Buy Me A Coffee">
    Go to Patreon
</a>

## Contribution
Contribution is open. Create Pull-request and I'll add it to the project if it's good enough.
