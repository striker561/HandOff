# HandOff 🧤

> A self-hosted client portal and project delivery management system

**HandOff** helps agencies and freelancers manage client projects, track deliverables, store credentials securely, and maintain project communication; all in one place.

## Features

- **Projects & Milestones** - Organize work with sequential milestones and trackable deliverables
- **Credential Storage** - Securely store login credentials, API keys, SSH keys, and database passwords
- **File Management** - Version-controlled file uploads with approval workflows
- **Collaboration** - Comment on projects, milestones, and deliverables (internal/external visibility)
- **Meeting Scheduling** - Track meetings with notes and recording links
- **Activity Tracking** - Complete audit trail of all project activities
- **Notifications** - Keep clients and team members informed

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- PostgreSQL / MySQL / SQLite

### Setup

```bash
# Clone repository
git clone https://github.com/striker561/HandOff.git
cd HandOff

# Install and setup
composer setup

# Configure database in .env
DB_CONNECTION=pgsql
DB_DATABASE=handoff
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Seed sample data (optional)
php artisan db:seed

# Start development server
composer dev
```

THIS IS THE API & ADMIN API; MONO REPO SETUP FOR THE USER INTERFACE IS COMING SHORTLY

## Tech Stack

- Laravel 12.x
- PHP 8.2+
- TailwindCSS 4.x
- PostgreSQL
- Next.JS (Client Dashboard)
- Livewire (Admin Dashboard)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/name`)
3. Commit changes (`git commit -m 'Add feature'`)
4. Push to branch (`git push origin feature/name`)
5. Open a Pull Request

## License

MIT License - see [LICENSE](LICENSE) file for details

---

Built with [Laravel](https://laravel.com)
