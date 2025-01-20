---

# Ingredient Order Tracker

This is a Laravel-based application designed to manage products, ingredients, and orders. It ensures that stock levels are updated when orders are placed and sends notifications when ingredient stock levels fall below 50%.

---

## **Stack**
- PHP 8.1
- Laravel 9.19
- MySQL (or any supported database)
- Docker (optional, for local development)

---

## **Development Setup**

### **1. Clone the Repository**
```bash
git clone https://github.com/your-repo/ingredient-order-tracker.git
cd ingredient-order-tracker
```

### **2. Set Up Environment**
1. Copy the environment configuration:
   ```bash
   cp .env.example .env
   ```
   Update the `.env` file with your database credentials and other required settings.

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

---

## **API Documentation**

### **Endpoints**
- **Create an Order**: `POST /api/orders`
  - Payload:
    ```json
    {
      "products": [
        {
          "product_id": 1,
          "quantity": 2
        }
      ]
    }
    ```
  - Response:
    ```json
    {
      "message": "Order created successfully",
      "order_id": 1
    }
    ```

### **Example `curl` Request**
```bash
curl -X POST http://localhost/api/orders \
-H "Content-Type: application/json" \
-d '{
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}'
```

---

## **Testing Guide**

### **Running Tests with Laravel Sail**
1. Ensure the application is running:
   ```bash
   ./vendor/bin/sail up -d
   ```

2. Run the tests:
   ```bash
   ./vendor/bin/sail artisan test
   ```

### **Running Tests without Laravel Sail**
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

---

## **Test Coverage**

### **Test Results**
```
 Tests\Unit\Observers\ProductOrderObserverTest
  ✓ it updates stock correctly
  ✓ it throws exception when stock is insufficient
  ✓ it logs and notifies on low stock

   Tests\Unit\Services\StockServiceTest
  ✓ deduct stock for order
  ✓ deduct stock for order throws exception if stock insufficient
  ✓ no notification when stock is exactly 50 percent
  ✓ deduct stock for order with multiple ingredients
  ✓ deduct stock for order with zero stock throws exception
  ✓ deduct stock for order with no ingredients

   Tests\Unit\Services\StoreOrderServiceTest
  ✓ it creates an order and attaches products

   Tests\Feature\OrderTest
  ✓ order creation updates stock

   Tests\Feature\ProductOrderObserverFeatureTest
  ✓ it updates stock when order is created
  ✓ it throws validation exception for insufficient stock
  ✓ it logs and notifies on low stock

   Tests\Feature\StoreOrderControllerTest
  ✓ user can store an order
  ✓ user cant use fake product id

  Tests:  16 passed
```

### **Feature Tests**
- Verify order creation, product stock updates, and validation for invalid product IDs.

### **Observer Tests**
- Ensure stock is reduced, validation for insufficient stock, and logging/notifying low stock.

### **Service Tests**
- Validate order creation and accurate product attachment.

### **Edge Cases**
- Handle unexpected inputs and ensure proper error handling.

Tests ensure database integrity and application functionality. Run tests regularly to maintain code quality.

---

## **Database Schema**

### **Tables**
- **Products**: Stores product details.
- **Ingredients**: Stores ingredient details and stock levels.
- **Orders**: Stores order details.
- **ProductIngredients**: Pivot table for products and ingredients.
- **ProductOrders**: Pivot table for products and orders.
- **IngredientNotificationLogs**: Logs low stock notifications.

---

## **Contributing**
1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Commit your changes and push to your branch.
4. Submit a pull request.

---

## **License**
This project is open-source and available under the [MIT License](LICENSE).

---