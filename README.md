# Stack
- PHP 8.1
- Laravel 9.19

# Dev Run
To start the application:

1. Copy the environment configuration:
   ```bash
   cp .env.example .env
   ```
   Fill in the required data.

2. Install dependencies:
   ```bash
   docker-compose run --rm composer install
   ```

3. Generate the application key:
   ```bash
   docker-compose run --rm php artisan key:generate
   ```

4. Start the application:
   ```bash
   ./vendor/bin/sail up -d
   ```

5. Run migrations and seed the database:
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

# Testing Guide
## Running Tests with Laravel Sail
1. Ensure the application is running:
   ```bash
   ./vendor/bin/sail up -d
   ```

2. Run the tests:
   ```bash
   ./vendor/bin/sail artisan test
   ```

## Running Tests without Laravel Sail
If you are not using Laravel Sail, you can run the tests directly:

1. Ensure you have the environment set up locally (PHP, Composer, and dependencies installed).

2. Run the tests using the artisan command:
   ```bash
   php artisan test
   ```

3. Alternatively, use PHPUnit directly:
   ```bash
   ./vendor/bin/phpunit
   ```

## Test Coverage
### Test Results
- **Tests\Unit\Observers\ProductOrderObserverTest**
  - ✓ it throws exception when stock is insufficient
  - ✓ it updates stock correctly
  - ✓ it logs and notifies on low stock

- **Tests\Unit\Services\StoreOrderServiceTest**
  - ✓ it creates an order and attaches products

- **Tests\Feature\ProductOrderObserverFeatureTest**
  - ✓ it updates stock when order is created
  - ✓ it throws validation exception for insufficient stock
  - ✓ it logs and notifies on low stock

- **Tests\Feature\StoreOrderControllerTest**
  - ✓ user can store an order
  - ✓ user cant use fake product id

### Feature Tests
- Verify order creation, product stock updates, and validation for invalid product IDs.

### Observer Tests
- Ensure stock is reduced, validation for insufficient stock, and logging/notifying low stock.

### Service Tests
- Validate order creation and accurate product attachment.

### Edge Cases
- Handle unexpected inputs and ensure proper error handling.

Tests ensure database integrity and application functionality. Run tests regularly to maintain code quality.

