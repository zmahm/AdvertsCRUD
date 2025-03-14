# Marketplace Web Application

## Overview

This project is a marketplace web application developed using PHP and the Symfony framework. It provides functionality for user management, advert creation, and role-based access control. The application follows a Model-View-Controller (MVC) architecture and incorporates security best practices.

## Features

### User Management
- User registration, login, and logout
- Role-based access control (admin, registered user, anonymous user)

### Advert Management
- Create, read, update, and delete adverts
- Each advert contains a title, description, price, location, and category
- Image upload functionality (supports PNG and JPEG, maximum 5MB per image)
- Pagination for browsing adverts

### Category Management
- Categories contain a name and description
- Adverts are linked to categories

### Security and Validation
- Secure authentication and authorisation using Symfony Security Component
- Input validation to prevent SQL injection and cross-site scripting (XSS)
- Cross-Site Request Forgery (CSRF) protection
- File validation for image uploads

## Technology Stack

- **Backend:** PHP, Symfony
- **Database:** Doctrine ORM (MySQL)
- **Frontend:** Twig templating engine
- **Authentication:** Symfony Security Bundle
- **Testing:** PHPUnit, Doctrine Fixtures

## Installation

### Prerequisites
Ensure you have the following installed:
- PHP 8.x
- Composer
- Symfony CLI
- MySQL or compatible database

### Setup

1. Clone the repository and navigate to the project directory:  
   ```sh 
   git clone https://github.com/zmahm/AdvertsCRUD.git
   cd your-repository
   ```  

2. Install dependencies:  
   ```sh
   composer install
   ```  

3. Configure environment variables by copying the `.env.example` file and renaming it to `.env`:  
   ```sh  
   cp .env.example .env  
   ```

4. Open the `.env` file and update the database credentials:  
   ```sh
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name"
   ```  

5. Create the database schema:  
   ```sh  
   php bin/console doctrine:database:create  
   php bin/console doctrine:migrations:migrate
   ```  

6. Load test data (optional):  
   ```sh
   php bin/console doctrine:fixtures:load
   ```  

7. Start the Symfony server:  
   ```sh  
   symfony server:start
   ```  

## Usage

- Visit `http://127.0.0.1:8000/` in a browser
- Register or log in to create and manage adverts
- Admin users can manage categories and user roles

## Testing

Run unit and integration tests:  
```sh  
php bin/phpunit
```  
