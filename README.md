<a id="readme-top"></a>

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![Unlicense License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<img width="100%" alt="Lara Dashboard" src="https://github.com/user-attachments/assets/c56009a4-718f-43dc-bd1e-caad5417b05b"  />

**⚡ Lara Dashboard** CMS By Laravel (7.x - 12.x) - Manages Users, Roles, Permissions, Modules, Settings, Translations, Contents(Post, Page, Category, Tags), System logs, Monitoring, Rest API's and every actions of your Laravel application. A complete CMS solution for Laravel application with Tailwind CSS integrated with all starting features including modules, dark/lite mode, charts, tables, forms, lots of components and many more. By our preimium modules, you can get more features like CRM, HRM, Course Management and so on.

**Demo:** https://laradashboard.com/try-demo/

```
Email - superadmin@example.com
password - 12345678
```

## 📋 Requirements:

-   Spatie role permission package `^6.4`
-   PHPUnit test package `^11.x`
-   Tailwind CSS >= 4.x
-   Laravel Modules - https://laravelmodules.com/docs/12/getting-started/introduction
-   Laravel Events (A WordPress like action/filter hooks) - https://github.com/tormjens/eventy
-   PHP 8.3 or 8.4

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### 🛠️ Built With

-   [![PHP][PHP.com]][PHP-url]
-   [![Laravel][Laravel.com]][Laravel-url]
-   [![Tailwind CSS][TailwindCSS.com]][TailwindCSS-url]
-   [![JavaScript][JavaScript.com]][JavaScript-url]
-   [![Alpine JS][AlpineJS.com]][AlpineJS-url]
-   [![React][React.js]][React-url]
-   [![MySQL][MySQL.com]][MySQL-url]
-   <a href="https://penguinui.com/">
      <img src="https://res.cloudinary.com/ds8pgw1pf/image/upload/v1721401292/penguinui/main-assets/Logo.png" alt="Penguin UI" style="height: 30px;">
     </a>
-   <a href="https://tailadmin.com" style="display: flex; align-items: center; text-decoration: none; color: #3d51e0;">
      <img src="https://avatars.githubusercontent.com/u/95587422?v=4" alt="Tail Admin" style="height: 20px;"> <span style="color:#3d51e0; margin-left: 5px;">Tail Admin</span>
     </a>

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 📝 Changelog

**[v2.0.1] - 2025-07-27**

-   **Feat:** Refactor Lara Dashboard whole Admin UI - Icons, Accessibility, Components, Pages, Layouts and so on.
-   **Enhancement:** Keep search form design consistent as `form-control` height.
-   **Enhancement:** Global variable for editor script to handle from any module.
-   **Enhancement:** Cleanup many codes to separate service to keep business logics separated.
-   **Enhancement:** Fixed several unit tests.
-   **Fix:** User chart data with SQLite supported.
-   **Fix:** Remove some unnecessary console logs.
-   **Doc:** Added Coding standard docs.

**[v2.0] - 2025-07-20**

-   **Feat:** Refactor Lara Dashboard whole Admin UI - Icons, Accessibility, Components, Pages, Layouts and so on.
-   **Enhancement:** Improve components, reusibility, and code quality.
-   **Fix:** Some random UI fixes.

**[v1.7.0] - 2025-07-13**

-   **Feat:** Rest API For Lara Dashboard, Scramble API documentation.
-   **Enhancement:** Cleanup class names to use more standard class names.
-   **Fix:** Some random UI fixes.

**[v1.6.0] - 2025-06-21**

-   **Enhancement:** Write/Update Standard Unit Tests, pint, rector, phpstan for the project.
-   **Fix:** Potential fix for code scanning alert no. 1: Workflow does not contain permissions.
-   **Fix:** Language switcher if no icon is selected.
-   **Fix:** Non-Translated keys keep empty instead the placeholder.

**[v1.5.0] - 2025-06-01**

-   **Feature**: Content Management System (CMS) with Content(Post/Page), Content Category, Content Tag management.
-   **Feature**: Post/Page activity chart in Dashboard.
-   **Feature**: Bulk delete for Users, Roles, Posts, Categories, Tags.
-   **Enhancement**: Sorting features for Users, Roles, Permissions, Posts, Categories, Tags.
-   **Enhancement**: Components - Confirm delete, Success/Error messages, Toast, Text Editor, Breadcrumbs, Action dropdown.
-   **Fix**: System dark mode issues.
-   **Fix**: Modules refactorring in demo mode.

**[v1.3.0] - 2025-05-18**

-   **Feature**: Admin Menu architecture with more extendible way.
-   **Feature**: Permission List and detail page.
-   **Enhancement**: Improved module compatibility.

**[v1.2.0] - 2025-05-12**

-   **Feature - Translation Management**: Added Translation management sytem with supporting 21 languages by default and possibility to add any in a second.
-   **Enhancement - Dashboard Redesign**: Dashboard redesigned with new card, user history chart, several more design improvements.
-   **Enhancement**: Role list page, user list page to add links of users list sorting by role and role edit page linkings.
-   **Enhancement**: Cleanup code base to use services, requests more, use SOLID whenever needed.
-   **Fix**: Fixed #109 Submenu dropdown icon doesn't change on open/close submenu of a menu item.
-   **Fix**: Fixed #105 Sidebar Icon not working good if collapsed.
-   **Fix**: Fixed #93 Theme primary color, secondary color was not working.
-   **Fix**: Fixed #99 Superadmin role shouldn't be edited.
-   **Fix**: Fixed Mobile responsive has some issues.
-   **Fix**: Fixed Sidebar toggle was not persistent issue.
-   **Fix**: Fixed Role create -> selecting permission group can't check the permissions in that group checkboxes automatically.

**[v1.0.0] - 2025-04-21**

-   **Feature - Forget Password Management**: Enhanced the forget password functionality for better reliability and user experience.
-   **Feature - Settings Management**: Added comprehensive settings management features, including API support.
-   **Enhancement - Role-Based Access Control (RBAC) Improvements**: Improved authorization mechanisms and role-based access control.
-   **Feature - Admin Impersonation**: Administrators can now log in as other users and switch back to their original accounts seamlessly.
-   **Enhancement - UI/UX Enhancements**: Updated the role create/edit form for a more intuitive and user-friendly experience.
-   **Enhancement - User Profile and Management Enhancements**: Refactored user-related operations to utilize `UserService` and `RolesService` for better separation of concerns and maintainability.
-   **Docs - Documentation and Configuration Updates**:
    -   Updated `.env.example` to include a `GITHUB_LINK` variable for improved project visibility.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 🔄 Versions:

Latest version `v2.0` - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v2.0

<details>
<summary>View Old versions</summary>

-   Laravel `7.x` & PHP -`7.x`

    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel7.x
    -   Branch - https://github.com/ManiruzzamanAkash/laravel-role/tree/Laravel7.x

-   Laravel `9.7` & PHP - `8.x`

    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel9.x

-   Laravel `11.x`

    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v11.x-main

-   Laravel `12.x` & PHP >= `8.3`

    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x

-   Laravel `12.x` & Tail Admin Template Integration

    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x-tailadmin

-   Laravel `12.x` & Module & Action Log integration

    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/Laravel12.x-module-logs

-   v1.0.0 - Settings, Forget password and lots of refactorring
    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.0.0
-   v1.2.0 - Translation Management, Dashboard Redesign, Role/User List improvements
    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.2.0
-   v1.3.0 - Admin Menu architecture, Permission List and detail page
    -   Tag - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.3.0
-   v1.5.0 - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.5.0
-   v1.6.0 - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.6.0
-   v1.7.0 - https://github.com/ManiruzzamanAkash/laravel-role/releases/tag/v1.7.0

More release tags - https://github.com/laradashboard/laradashboard/releases

</details>

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 🚀 Project Setup

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

-   Create database called - `laradashboard`
-   Create `.env` file by copying `.env.example` file

```bash
cp .env.example .env
```

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
composer run dev
```

So, You've got the project of Lara Dashboard on your local machine - http://localhost:8000

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 🔄 Previously From laravel-role

We were previously at https://github.com/ManiruzzamanAkash/laravel-role, so you need to change the URL if you moved from there

```console
git remote set-url origin git@github.com:laradashboard/laradashboard.git
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## ⚙️ How it works

1. Login using Super Admin Credential -
    1. Email - `superadmin@example.com`
    1. Password - `12345678`
1. Forget password - Password forget and reset will work if email is set up properly
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
1. Translation Management - Add/Edit/Delete Language, Add/Edit/Delete Translation
1. Settings - General, Site Appearance, Content, Integration settings
1. Admin Menu - Add/Edit/Delete Menu, Submenu, Link
1. Admin Impersonation - Login as another user and switch back to your original account
1. Custom Error Pages - 404, 500, 503, 403
1. Content Management System - Add/Edit/Delete Content, Content Category, Content Tag
1. Rest API - Rest API's for Users, Roles, Permissions, Settings, Translations, Content(Post/Page/Category/Tag) and so on.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 📧 Email setup

You can use Mailpit to test emails easily - https://mailpit.axllent.org/

Installation link - https://mailpit.axllent.org/docs/install/

```bash
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=dev@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Browse Emails - http://localhost:8025

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 📚 Documentation

https://laradashboard.com/docs/

## 🔗 Rest API Documentation

We've used [Scramble](https://github.com/dedoc/scramble) to automatically generate the Rest API documentation for Lara Dashboard. You can find the API documentation at:

http://localhost:8000/docs/api

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Tests

We've used Laravel Pint, Larastan(PHPstan), PHPUnit for formatting, static analysis and testing.

```bash
composer run test
```

**Format:** To format the code, we've used `pint`. You can run the following command to format the code:

```bash
composer run format
```

**Type Safety:** To improve type safety, we've used `rector`. You can run the following command to add type hints:

```bash
composer run type-check
```

You can also run individual commands for each tool (optional):

```bash
composer run pint
composer run phpstan
composer run phpunit
```

## 🚀 Laravel Boost

Laravel Boost is already installed in this project to provide enhanced development and debugging tools for Lara Dashboard.

**How to use Boost:**

Visit the [Laravel Boost documentation](https://github.com/laravel/boost).

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 📸 Screenshots

### 🔐 Login & Authentication

<table>
  <tr>
    <td width="50%">
      <strong>Login Page</strong><br/>
      <img width="100%" alt="Login Page" src="/demo-screenshots/00-Login-Page-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Forget Password Page (Dark Mode)</strong><br/>
      <img width="100%" alt="Forget Password Page" src="/demo-screenshots/01-Forget-password.png"/>
    </td>
  </tr>
</table>

### 📊 Dashboard

<table>
  <tr>
    <td width="50%">
      <strong>Dashboard (Light Mode)</strong><br/>
      <img width="100%" alt="Dashboard Light Mode" src="/demo-screenshots/03-Dashboard-Page-lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Dashboard (Dark Mode)</strong><br/>
      <img width="100%" alt="Dashboard Dark Mode" src="/demo-screenshots/04-Dashboard-Page-Dark-Mode.png"/>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Dashboard Collapsed Sidebar</strong><br/>
      <img width="100%" alt="Dashboard Collapsed Sidebar" src="/demo-screenshots/04_1-Dashboard-Collapsed-Sidebar.png"/>
    </td>
  </tr>
</table>

### 🔑 Role Management

<table>
  <tr>
    <td width="50%">
      <strong>Role List (Light Mode)</strong><br/>
      <img width="100%" alt="Role List" src="/demo-screenshots/05-Role-List-Lite.png"/>
    </td>
    <td width="50%">
      <strong>Role List (Dark Mode)</strong><br/>
      <img width="100%" alt="Role List Dark" src="/demo-screenshots/06-Role-List-Dark.png"/>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Role Create</strong><br/>
      <img width="100%" alt="Role Create" src="/demo-screenshots/07-Role-Create.png"/>
    </td>
    <td width="50%">
      <strong>Role Edit</strong><br/>
      <img width="100%" alt="Role Edit" src="/demo-screenshots/08-Role-Edit.png"/>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Permission List</strong><br/>
      <img width="100%" alt="Permission List" src="/demo-screenshots/09-Permissions-List-Lite-Mode.png"/>
    </td>
  </tr>
</table>

### 👥 User Management

<table>
  <tr>
    <td width="50%">
      <strong>Users List (Light mode)</strong><br/>
      <img width="100%" alt="Users List (Light mode)" src="/demo-screenshots/10-User-List-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Users List (Dark mode)</strong><br/>
      <img width="100%" alt="Users List (Dark mode)" src="/demo-screenshots/11-User-List-Dark-Mode.png" />
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>User Create</strong><br/>
      <img width="100%" alt="User Create" src="/demo-screenshots/12-User-Create-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>User Delete</strong><br/>
      <img width="100%" alt="User Delete" src="/demo-screenshots/13-User-Delete-Lite-Mode.png" />
    </td>
  </tr>
</table>

### 📝 Content Management - CMS

<table>
  <tr>
    <td width="50%">
      <strong>Posts List</strong><br/>
      <img width="100%" alt="Users List (Light mode)" src="/demo-screenshots/31-Post-List-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Post Create</strong><br/>
      <img width="100%" alt="Users List (Dark mode)" src="/demo-screenshots/30-Post-List-Dark-Mode.png" />
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Pages List</strong><br/>
      <img width="100%" alt="Users List (Light mode)" src="/demo-screenshots/38-Pages-List-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Page Edit</strong><br/>
      <img width="100%" alt="Users List (Dark mode)" src="/demo-screenshots/39-Pages-Edit-Dark-Mode.png" />
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Category List & Create</strong><br/>
      <img width="100%" alt="Category List & Create" src="/demo-screenshots/34-Category-List-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Category Edit</strong><br/>
      <img width="100%" alt="Category Edit" src="/demo-screenshots/35-Category-Edit-Dark-Mode.png" />
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Tag List & Create</strong><br/>
      <img width="100%" alt="Tag Create" src="/demo-screenshots/36-Tags-List-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Tag Edit</strong><br/>
      <img width="100%" alt="Tag Delete" src="/demo-screenshots/37-Tags-Edit-Dark-Mode.png" />
    </td>
  </tr>
</table>

### 📁 Media Management

<table>
  <tr>
    <td width="50%">
      <strong>Media List</strong><br/>
      <img width="100%" alt="Media List" src="/demo-screenshots/60-Media-List-Lite-Mode.png"/>
    </td>
    <td width="50%">
      <strong>Media Modal View</strong><br/>
      <img width="100%" alt="Media Modal View" src="/demo-screenshots/61-Media-Modal-View.png"/>
    </td>
  </tr>
</table>

### 🧩 Module Management

<table>
  <tr>
    <td width="50%">
      <strong>Module List</strong><br/>
      <img width="100%" alt="Module List" src="/demo-screenshots/14-Module-List.png"/>
    </td>
    <td width="50%">
      <strong>Upload Module</strong><br/>
      <img width="100%" alt="Upload Module" src="/demo-screenshots/15-Module-Upload.png"/>
    </td>
  </tr>
</table>

### ⚙️ Settings Pages

<table>
  <tr>
    <td width="50%">
      <strong>General Settings</strong><br/>
      <img width="100%" alt="General Settings" src="/demo-screenshots/40-Settings-General.png"/>
    </td>
    <td width="50%">
      <strong>Site Appearance</strong><br/>
      <img width="100%" alt="Site Appearance" src="/demo-screenshots/41-Settings-Site-Appearance-Dark-Mode.png"/>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Content Settings</strong><br/>
      <img width="100%" alt="Content Settings" src="/demo-screenshots/42-Settings-Content.png"/>
    </td>
    <td width="50%">
      <strong>Integration Settings</strong><br/>
      <img width="100%" alt="Integration Settings" src="/demo-screenshots/43-Settings-Integration.png"/>
    </td>
  </tr>
</table>

### 🌐 Translations Pages

<table>
  <tr>
    <td width="50%">
      <strong>Translations List</strong><br/>
      <img width="100%" alt="Translations List" src="/demo-screenshots/50-Translation-List-Lite-Mode.png" />
    </td>
    <td width="50%">
      <strong>Add Language</strong><br/>
      <img width="100%" alt="Create Translation" src="/demo-screenshots/51-Translation-Create-Dark-Mode.png" />
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>Language Switcher</strong><br/>
      <img width="100%" alt="Language Switcher" src="/demo-screenshots/90-Language-Switcher.png" />
    </td>
    <td width="50%">
      <!-- Reserved for future screenshot -->
    </td>
  </tr>
</table>

### 📊 Monitoring

<table>
  <tr>
    <td width="50%">
      <strong>Action Logs</strong><br/>
      <img width="100%" alt="Action Logs" src="/demo-screenshots/20-Action-Log-List.png"/>
    </td>
    <td width="50%">
      <strong>Laravel Pulse</strong><br/>
      <img width="100%" alt="Laravel Pulse" src="/demo-screenshots/91-Laravel-Pulse-Dashboard-for-Monitoring.png"/>
    </td>
  </tr>
</table>

### 🧩 Rest API Management

<table>
  <tr>
    <td width="50%">
      <strong>Rest API</strong><br/>
      <img width="100%" alt="Rest API" src="/demo-screenshots/120-Rest-API-Documentation.png"/>
    </td>
    <td width="50%">
      <strong>Rest API Login</strong><br/>
      <img width="100%" alt="Rest API Login" src="/demo-screenshots/121-Rest-API-Login.png"/>
    </td>
  </tr>
</table>

### 🔧 Other Pages / Sections / Tests

<table>
  <tr>
    <td width="50%">
      <strong>Custom Error Pages</strong><br/>
      <img width="100%" alt="Custom Error Pages" src="/demo-screenshots/100-Custom-Error-Pages.png"/>
    </td>
    <td width="50%">
      <strong>Post activity Chart</strong><br/>
      <img width="100%" alt="Post activity Chart" src="/demo-screenshots/102-Post-activity-Chart.png"/>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <strong>PHPUnit, Pint, Rector, PHPstan tests</strong><br/>
      <img width="100%" alt="PHPUnit, Pint, Rector, PHPstan tests" src="/demo-screenshots/103-Unit-Tests-Demo.png"/>
    </td>
  </tr>
</table>

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 🔗 Live Demo

https://laradashboard.com/try-demo/

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## ✨ Premium Features

Please visit at Lara Dashboard to get more premium moduels - https://laradashboard.com. Premium modules included CRM, HRM, Course Managements and so on.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 🧩 Core modules

-   **Task Manager** - https://github.com/laradashboard/TaskManager - Basic Task Manager module for Lara Dashboard | Standard Starter Module for Lara Dashboard.
-   **User Avatar** - https://github.com/laradashboard/UserAvatar - A very simple module create an avatar for a user. Handle migration, entries/updates in user forms and so on.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 👥 Contributing

Want to contribute? Fork the project, make your changes, and submit a pull request. Even small improvements to documentation are appreciated!

Please be sure to read our [Contribution Guide](CONTRIBUTING.md) before submitting your PR.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## ➶ Coding Standards

✨ Following a consistent coding standard ensures code quality, readability, and easier collaboration for everyone.  
📏 Please be sure to read our [Contribution Guide](Coding-Standard.md) before submitting your PR.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### 🌟 Top contributors:

<a href="https://github.com/laradashboard/laradashboard/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=laradashboard/laradashboard" alt="contrib.rocks image" />
</a>

### ✩ Growth Story

[![Star History Chart](https://api.star-history.com/svg?repos=laradashboard/laradashboard&type=Date)](https://www.star-history.com/#laradashboard/laradashboard&Date)

## 💖 Support

If you like my work you may consider buying me a ☕ / 🍕

<a href="https://www.patreon.com/maniruzzaman" target="_blank" title="Buy Me A Coffee">
    Go to Patreon
</a>

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 📞 Connect

-   Join Facebook Community (For any questions, latest updates) - https://www.facebook.com/groups/laradashboard
-   Linkedin Community - https://www.linkedin.com/groups/14690156
-   Youtube channel (For tutorials) - https://www.youtube.com/@laradashboard
-   Maniruzzaman Akash - [@LinkedIn](https://www.linkedin.com/in/maniruzzamanakash) | manirujjamanakash@gmail.com

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->

[contributors-shield]: https://img.shields.io/github/contributors/laradashboard/laradashboard.svg?style=for-the-badge
[contributors-url]: https://github.com/laradashboard/laradashboard/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/laradashboard/laradashboard.svg?style=for-the-badge
[forks-url]: https://github.com/laradashboard/laradashboard/network/members
[stars-shield]: https://img.shields.io/github/stars/laradashboard/laradashboard.svg?style=for-the-badge
[stars-url]: https://github.com/laradashboard/laradashboard/stargazers
[issues-shield]: https://img.shields.io/github/issues/laradashboard/laradashboard.svg?style=for-the-badge
[issues-url]: https://github.com/laradashboard/laradashboard/issues
[license-shield]: https://img.shields.io/github/license/laradashboard/laradashboard.svg?style=for-the-badge
[license-url]: https://github.com/laradashboard/laradashboard/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/maniruzzamanakash
[product-screenshot]: images/screenshot.png
[Next.js]: https://img.shields.io/badge/next.js-000000?style=for-the-badge&logo=nextdotjs&logoColor=white
[Next-url]: https://nextjs.org/
[React.js]: https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB
[React-url]: https://reactjs.org/
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Angular.io]: https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white
[Angular-url]: https://angular.io/
[Svelte.dev]: https://img.shields.io/badge/Svelte-4A4A55?style=for-the-badge&logo=svelte&logoColor=FF3E00
[Svelte-url]: https://svelte.dev/
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com
[PHP.com]: https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white
[PHP-url]: https://www.php.net
[JavaScript.com]: https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black
[JavaScript-url]: https://developer.mozilla.org/en-US/docs/Web/JavaScript
[MySQL.com]: https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white
[MySQL-url]: https://www.mysql.com
[TailwindCSS.com]: https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white
[TailwindCSS-url]: https://tailwindcss.com
[AlpineJS.com]: https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=black
[AlpineJS-url]: https://alpinejs.dev
