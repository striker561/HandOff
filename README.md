<p align="center">
  <img src="public/logo.png" alt="HandOff logo" width="120" />
</p>

<p align="center">
  <a href="https://github.com/striker561/HandOff/actions/workflows/ci.yml"><img src="https://github.com/striker561/HandOff/actions/workflows/ci.yml/badge.svg" alt="CI status" /></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License" /></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/php-8.2%2B-777bb3.svg" alt="PHP version" /></a>
</p>

# HandOff

> A self-hosted client portal and project delivery management system — currently in active development.

**HandOff** helps agencies and freelancers manage client projects end-to-end: organize milestones, track deliverables, store credentials securely, schedule meetings, and maintain a complete audit trail — all in one place.

### How It Works

```
Agency ──▶ Project ──▶ Milestones (sequential) ──▶ Deliverables (versioned files)
                │
                ├── Credentials (passwords, API keys, SSH keys)
                ├── Meetings (notes, recording links)
                └── Activity Log (complete audit trail)
```

The **agency** (admin) creates projects, invites **clients**, and manages all project data. Clients get a portal to view progress, download deliverables, and access shared credentials. Every action is logged and auditable.

> **Status:** HandOff is under active development. The admin workspace is functional; the client portal is upcoming. See [CONTRIBUTING.md](CONTRIBUTING.md) to help build it.

## Features

- **Projects & Milestones** — Organize work with sequential milestones and trackable deliverables
- **Credential Storage** — Securely store login credentials, API keys, SSH keys, and database passwords
- **File Management** — Version-controlled file uploads with approval workflows
- **Collaboration** — Comment on projects, milestones, and deliverables (internal/external visibility)
- **Meeting Scheduling** — Track meetings with notes and recording links
- **Activity Tracking** — Complete audit trail of all project activities
- **Notifications** — Keep clients and team members informed

## Installation

### Prerequisites

- **PHP 8.2+** with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `intl`, `json`, `mbstring`, `openssl`, `pcre`, `pdo`, `tokenizer`, `xml`
- **Composer** 2.x
- **Node.js** 18+ & NPM
- **Database:** PostgreSQL (recommended), MySQL 8+, or SQLite

### Quick Start

```bash
# Clone and install
git clone https://github.com/striker561/HandOff.git
cd HandOff
composer setup

# Configure your database in .env
# (composer setup copies .env.example if .env doesn't exist)
# See .env.example for all available options
DB_CONNECTION=pgsql
DB_DATABASE=handoff
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Seed sample data (optional)
php artisan db:seed

# Start the development environment
composer dev
```

`composer dev` starts four processes concurrently: PHP server, queue worker, log tail, and Vite dev server. Visit `http://localhost:8000`.

### Running Tests & Quality Checks

```bash
# Run the full test suite
composer test          # runs lint → static analysis → tests

# Individual steps
composer lint          # Pint code style fixer
composer analyse       # PHPStan static analysis
php artisan test       # Pest test suite (with --compact for CI)
```

## Tech Stack

| Layer      | Technology                       |
| ---------- | -------------------------------- |
| Framework  | Laravel 13                       |
| Language   | PHP 8.2+                         |
| CSS        | Tailwind CSS 4                   |
| UI         | Livewire 4 + Flux UI + Alpine.js |
| Auth       | Laravel Fortify + Passkeys       |
| Database   | PostgreSQL / MySQL / SQLite      |
| Testing    | Pest 4 + PHPStan                 |
| Code Style | Laravel Pint                     |

## Documentation

| Document                                 | Purpose                                              |
| ---------------------------------------- | ---------------------------------------------------- |
| [ARCHITECTURE.md](ARCHITECTURE.md)       | Codebase structure, layers, UI conventions, patterns |
| [CONTRIBUTING.md](CONTRIBUTING.md)       | Development workflow, service patterns, code style   |
| [SECURITY.md](SECURITY.md)               | Vulnerability reporting and security policy          |
| [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) | Community guidelines                                 |

## Contributing

We welcome contributors! See [CONTRIBUTING.md](CONTRIBUTING.md) for architecture details, service patterns, and development conventions.

Quick start for contributors:

```bash
# Fork → clone → setup → branch → code → test → PR
composer setup
php artisan db:seed
composer test          # make sure everything passes
composer dev           # start hacking
```

## License

MIT License — see [LICENSE](LICENSE) file for details.

---

Built with [Laravel](https://laravel.com)
