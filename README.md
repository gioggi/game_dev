# Dev Game

A game where you manage a software development company, hiring developers and salespeople, and managing projects.

## Prerequisites

Before you begin, ensure you have the following installed:
- [Docker](https://www.docker.com/get-started) and [Docker Compose](https://docs.docker.com/compose/install/)
- [Git](https://git-scm.com/downloads)

## Setup Instructions

1. Clone the repository:
```bash
git clone <repository-url>
cd dev_game
```

2. Create environment files:
```bash
cp backend/.env.example backend/.env
```

3. Start the Docker containers:
```bash
docker compose up -d
```

4. Install backend dependencies:
```bash
docker compose exec keristo composer install
```

5. Generate application key:
```bash
docker compose exec keristo php artisan key:generate
```

6. Run database migrations:
```bash
docker compose exec keristo php artisan migrate
```

7. Install frontend dependencies:
```bash
cd frontend
npm install
```

8. Start the frontend development server:
```bash
npm run dev
```

## Accessing the Application

Once everything is set up, you can access the application at:
- **Frontend**: http://localhost:3010
- **Backend API**: http://localhost:8000/api
- **phpMyAdmin**: http://localhost:8081 (username: `dev_game`, password: `dev_game`)

## Project Structure

```
dev_game/
├── backend/           # Laravel backend
│   ├── app/          # Application code
│   ├── database/     # Database migrations and seeds
│   └── routes/       # API routes
├── frontend/         # Vue.js frontend
└── docker/          # Docker configuration files
```

## Game Features

- Manage a software development company
- Hire developers with different seniority levels
- Hire salespeople to generate projects
- Assign developers to projects
- Track project progress and completion
- Manage company finances

## Development

### Backend (Laravel)

The backend is built with Laravel and provides a RESTful API. Key features:
- RESTful API endpoints for games, developers, salespeople, and projects
- Regular polling for real-time updates
- Database transactions for data integrity
- Event-driven architecture for game state changes

### Frontend (Vue.js)

The frontend is built with Vue.js and provides the user interface. Features:
- Regular polling for real-time updates
- Responsive design
- Interactive game interface
- State management with Vuex

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
