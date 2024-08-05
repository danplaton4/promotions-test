
# Promotions Test

This project is a promotional game system designed to distribute prizes to authenticated users over a two-day campaign period. The system uses a RESTful API built with Symfony, following best practices for code design, database design, and object-oriented programming principles.

## Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Usage](#usage)
- [Concurrency Handling](#concurrency-handling)
- [Future Improvements](#future-improvements)

## Project Overview

The promotional system allows users to log in and participate in a prize draw over a two-day campaign. Each day has a limited number of prizes that users can win. The game rules enforce strict prize distribution, prevent users from winning multiple prizes per day, and handle concurrency issues to ensure fairness.

## Features

- **User Authentication**: Secure login for users to participate in the game.
- **Prize Distribution**: Prizes are evenly distributed over two campaign days.
- **RESTful API**: API follows REST principles and returns JSON responses.
- **Multilingual Support**: Supports dual languages based on user preferences.
- **Concurrency Handling**: Ensures no duplicate prize allocation through efficient mechanisms.

## Technology Stack

- **Backend Framework**: Symfony
- **Database**: MySQL
- **Authentication**: Symfony Security Component with hashed passwords
- **API Format**: JSON

## Installation

To set up this project locally, follow these steps:

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL
- Redis
- Symfony CLI (optional, but recommended)

### Steps

1. **Clone the Repository**

   ```bash
   git clone https://github.com/danplaton4/promotions-test.git
   cd promotions-test
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Configure Environment Variables**

   Create a `.env.local` file and set your database and Redis credentials:

   ```plaintext
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/promotions"
   REDIS_URL="redis://localhost:6379"
   ```

4. **Setup the Database**

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Load Initial Data**

   Load initial data for partners and prizes:

   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. **Run the Server**

   ```bash
   symfony server:start
   ```

   The server will start at `http://127.0.0.1:8000`.

## Database Schema

The database consists of three main tables:

- **Partners**: Contains partner details and associated languages.
- **Prizes**: Contains prize details and associates prizes with partners.
- **Users**: Contains user details, including authentication data and prize status.

## API Endpoints

### User Authentication

- **Login**
  - **Endpoint**: `POST /api/login`
  - **Payload**:
    ```json
    {
      "username": "user1",
      "password": "securepassword"
    }
    ```
  - **Response**:
    ```json
    {
      "token": "JWT_TOKEN"
    }
    ```

### Game Endpoints

- **Play Game**
  - **Endpoint**: `GET /api/play`
  - **Headers**: `Authorization: Bearer JWT_TOKEN`
  - **Response**:
    ```json
    {
      "message": "Congratulations! You have won a prize.",
      "prize": {
        "name": "Prize Name",
        "description": "Prize Description",
        "partner": {
          "name": "Partner Name",
          "description": "Partner Description"
        }
      }
    }
    ```

- **Check Prize Status**
  - **Endpoint**: `GET /api/status`
  - **Headers**: `Authorization: Bearer JWT_TOKEN`
  - **Response**:
    ```json
    {
      "hasPlayed": true,
      "prize": {
        "name": "Prize Name",
        "description": "Prize Description"
      }
    }
    ```

## Usage

1. **Login**: Users must log in to receive a JWT token, which is used for subsequent API requests.
2. **Play Game**: Users call the play endpoint to attempt to win a prize. This call can only be made once per day.
3. **Check Status**: Users can check if they have played and view their prize information.

### Game Rules

- **Active Hours**: Prizes are distributed between 09:00 and 20:00.
- **One Prize Per Day**: Users can win only one prize per day.
- **Equal Distribution**: Prizes are evenly divided between the two campaign days.

## Concurrency Handling

Concurrency is managed using Redis to ensure efficient and scalable handling of prize distribution. Redis is used to track prize availability and to implement atomic operations for prize allocation. This approach reduces the risk of race conditions and improves the overall performance of the system.

## Future Improvements

- **Scalability**: Implement caching strategies to handle increased load.
- **Enhanced Security**: Use OAuth2 for authentication and authorization.
- **Additional Languages**: Expand multilingual support to include more languages.
- **Admin Dashboard**: Create an admin interface for monitoring and managing the campaign.
- **NoSQL Concurrency Handling**: Utilize Redis for more efficient and scalable concurrency handling, replacing traditional database locks with atomic operations.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
