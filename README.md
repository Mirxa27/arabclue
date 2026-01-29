# HabibiStay

HabibiStay is a premier, multilingual platform for short-term and long-term property rentals, designed to connect guests with unique and unforgettable stays. It provides property owners and investors with powerful tools to manage their listings, optimize pricing, and track earnings, while offering guests a seamless booking experience enhanced by an AI-powered assistant named "Sara".

## Core Features

*   **Property Management:** Comprehensive tools for hosts to create, manage, and publish property listings with multilingual descriptions, high-quality media, and dynamic calendars.
*   **Advanced Booking System:** Real-time availability, instant booking, and secure payment processing with multiple gateway options.
*   **User Dashboards:** Dedicated dashboards for Guests, Hosts, and Administrators to manage their activities, from bookings to platform-wide settings.
*   **Sara AI Chatbot:** An intelligent, multilingual chatbot that assists guests with property discovery, booking, and support, all within a conversational interface.
*   **Channel Manager:** Synchronize property availability and pricing across major Online Travel Agencies (OTAs).
*   **Financial Reporting:** Detailed financial reports and analytics for hosts and platform administrators.

## Tech Stack

*   **Backend:** PHP 8.2, Laravel 10
*   **Database:** MySQL, Redis
*   **Frontend:** Blade, Livewire/Alpine.js (or Vue/React)
*   **Web Server:** Nginx
*   **DevOps:** Docker, Laravel Sail, GitHub Actions

## Getting Started

Follow these instructions to get the project up and running on your local machine for development and testing purposes.

### Prerequisites

Make sure you have the following software installed on your system:

*   PHP 8.2 or higher
*   Composer
*   Node.js & NPM
*   MySQL

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-repo/habibi-lara.git
    ```

2.  **Navigate to the project directory:**
    ```bash
    cd habibi-lara
    ```

3.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

4.  **Install JavaScript dependencies:**
    ```bash
    npm install
    ```

5.  **Create your environment file:**
    ```bash
    cp .env.example .env
    ```

6.  **Generate an application key:**
    ```bash
    php artisan key:generate
    ```

7.  **Configure your environment:**
    Open the `.env` file and configure your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) and any other required service keys.

8.  **Run database migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```

9.  **Start the local development server:**
    ```bash
    php artisan serve
    ```

10. **Start the frontend asset builder:**
    ```bash
    npm run dev
    ```

The application should now be running at `http://localhost:8000`.

## Running Tests

To run the automated test suite, use the following Artisan command:

```bash
php artisan test
```

## Environment Variables

The `.env` file contains all the configuration variables needed to run the application. Here are some of the key variables from `.env.example`:

*   `APP_URL`: The base URL of your application.
*   `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Credentials for the database connection.
*   `MAIL_MAILER`, `MAIL_HOST`, etc.: Configuration for sending emails.
*   `OPENAI_API_KEY`: API key for OpenAI, used by the Sara AI Chatbot and other AI features.
*   `GOOGLE_CLIENT_ID`, `FACEBOOK_CLIENT_ID`, `APPLE_CLIENT_ID`: Credentials for social authentication providers.
*   `STRIPE_KEY`, `PAYPAL_CLIENT_ID`, `MYFATOORAH_API_KEY`: API keys for payment gateways.
*   `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`: Credentials for Amazon S3 file storage.
*   `SENTRY_LARAVEL_DSN`: DSN for error tracking with Sentry.
