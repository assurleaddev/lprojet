# Laravel Dashboard

## Overview
This project is a Laravel-based application designed for managing a marketplace. It includes features for handling products, categories, and media management.

## Project Structure
The project follows a standard Laravel directory structure with the following key components:

- **app/Models**: Contains the Eloquent models for the application.
  - `Product.php`: Defines the Product model with media handling capabilities.
  - `Category.php`: Defines the Category model for organizing products.
  - `Media.php`: Wraps the Spatie Media model for modularity.

- **app/Http/Controllers/Backend/Marketplace**: Contains controllers for handling marketplace-related actions.
  - `ProductController.php`: Manages product-related operations such as creation, updating, and deletion.

- **database/factories**: Contains factories for generating fake data.
  - `ProductFactory.php`: Defines a factory for creating Product instances using Faker.

- **database/seeders**: Contains seeders for populating the database.
  - `ProductSeeder.php`: Seeds the database with Product instances.

## Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   ```
2. Navigate to the project directory:
   ```
   cd laradashboard
   ```
3. Install dependencies:
   ```
   composer install
   ```
4. Set up your `.env` file:
   ```
   cp .env.example .env
   php artisan key:generate
   ```
5. Run migrations:
   ```
   php artisan migrate
   ```

## Usage
To seed the database with sample products, run:
```
php artisan db:seed --class=ProductSeeder
```

## Contributing
Contributions are welcome! Please submit a pull request for any changes or improvements.

## License
This project is licensed under the MIT License. See the LICENSE file for details.