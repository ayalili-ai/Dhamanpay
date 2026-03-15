# DhamanPay Backend

## Introduction

DhamanPay is an escrow-based payment backend designed to secure transactions between buyers and sellers.

During a transaction, the system temporarily **freezes the buyer's funds** in escrow. Once the order is confirmed, the funds are either released to the seller or refunded if necessary.

This project demonstrates how escrow payment logic can be implemented in a backend system using **Laravel and PostgreSQL (Neon Database)**.


## Project Architecture

The backend follows a simple request-response architecture.

Client (Postman / Frontend)  
↓  
API Routes  
↓  
Controllers  
↓  
Database Queries / Functions  
↓  
PostgreSQL Database  
↓  
JSON Response

Explanation:

1. The client sends an HTTP request to the API.
2. The route receives the request.
3. The controller processes the request.
4. A database query or function is executed.
5. The database returns the result.
6. The API sends a JSON response back to the client.

---

## User Instructions

### Running the project locally

1. Open the project in VS Code.

2. Install project dependencies:composer install

3. Create a .env file in the root directory of the project.

4. Add the Neon database connection string inside the .env file.

5. Start the Laravel development server:

6. The API will run locally:  http://127.0.0.1:8000

## Developer Instructions

### Project structure

app/Http/Controllers → contains the API logic

routes → contains the API endpoints

database → contains migrations and database related files

config → contains application configuration

### Backend request flow

Postman Request
↓
Route
↓
Controller
↓
Database Query / Function
↓
JSON Response

### Example API endpoint
POST /orders/{id}/confirm

. This endpoint is used to confirm an order and trigger the escrow logic by calling the PostgreSQL function:

escrow_freeze(order_id, actor_user_id)

The function freezes funds during the transaction process.

## Contributors Expectations

Contributors working on this project should:

1. follow the existing project structure

2. write clear commit messages

3. test API endpoints before pushing code

4. keep the code readable and documented

## Known Issues

Current limitations of the project include:

1. authentication system is not implemented yet

2. some API endpoints are still under development

3. error handling can be improved

## Future Improvements

This project is currently in a **local development stage.**

> Planned future updates include:

1. deploying the backend to an online server

2. making the API publicly accessible

3. improving security and authentication

4. expanding API documentation

5. updating this README with deployment instructions and live API access