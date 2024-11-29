
# RegistrationApp

A comprehensive application designed to handle user registration, profile management, and authentication workflows. This guide outlines the setup process, features, and key aspects of the application to help you get started quickly.

---

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Prerequisites](#prerequisites)
    - [Software](#software)
4. [Installation](#installation)
    - [Step 1: Clone the Repository](#step-1-clone-the-repository)
    - [Step 2: Setup Environment Variables](#step-2-setup-environment-variables)
    - [Step 3: Run Docker](#step-3-run-docker)
5. [Usage](#usage)
6. [API Documentation](#api-documentation)
    - [Endpoints Overview](#endpoints-overview)
    - [Examples](#examples)
7. [Project Structure](#project-structure)
8. [Tech Stack](#tech-stack)
9. [Contributing](#contributing)
10. [License](#license)

---

## Overview

`RegistrationApp` is a modern, user-friendly platform for managing user registration and profiles. It is built to simplify and secure the process of user authentication, allowing seamless integration with various front-end applications.

---

## Features

| **Feature**                     | **Description**                                                                                 |
|----------------------------------|---------------------------------------------------------------------------------------------|
| **User Registration**            | Allows users to create accounts by submitting essential details (email, password, nickname). |
| **Profile Management**           | Enables users to update their profile information, such as email, nickname, and birth date.  |
| **Authentication**               | Provides secure login/logout functionality with password-based authentication.                 |
| **Validation**                   | Implements robust client- and server-side input validation for security and usability.      |
| **API-Driven Architecture**      | Fully modular backend accessible via PHP endpoints.                                      |
| **Dockerized Deployment**        | Ensures easy setup and cross-platform compatibility with Docker.                            |
| **Logging**                | Logs errors,warnings,infos for better debugging and maintenance.                              |

---

## Prerequisites

To get started with `RegistrationApp`, ensure that you have the following software installed on your system.

### Software

| **Software**     | **Version**   | **Purpose**                              |
|-------------------|---------------|------------------------------------------|
| Docker            | 20.10+        | To containerize and run the application. |
| PHP               |Latest         | For local development or testing.        |
| Git               | Latest        | To clone the repository.                 |
| Postman or Insomnia (Optional)| Latest        | To test API endpoints.       |

---

### Users Table Documentation

This document provides a detailed description of the `users` table, which is a core part of the application database schema.

---

#### Table: `users`

The `users` table stores user information, including their credentials, profile details, and login status.

#### **Columns**

| **Column Name**     | **Data Type**       | **Description**                                                                 |
|---------------------|---------------------|---------------------------------------------------------------------------------|
| `id`                | `bigint`           | Primary key. A unique identifier for each user.                                |
| `email`             | `varchar(255)`     | The user's email address. Must be unique.                                      |
| `nickname`          | `varchar(50)`      | The user's chosen nickname. Must be unique.                                    |
| `birth_date`        | `date`             | The user's date of birth.                                                      |
| `password_hash`     | `varchar(255)`     | The hashed password for secure authentication.                                 |
| `created_at`        | `timestamp`        | The timestamp when the user was created.                                       |
| `is_logged`         | `tinyint(1)`       | Indicates whether the user is currently logged in: `0` (not logged in), `1` (logged in). |
| `logged_in_at`      | `timestamp`        | The timestamp of the user's most recent login.                                 |

---

#### **Important Notes**

1. **Unique Constraints**:
   - The `email` column must be unique for all users.
   - The `nickname` column must also be unique.

2. **Password Security**:
   - The `password_hash` column stores a securely hashed version of the user's password. It should never store plain-text passwords.

3. **Login Status**:
   - The `is_logged` column is a boolean-like field:
      - `0`: The user is not logged in.
      - `1`: The user is currently logged in.
   - The `logged_in_at` column records the last successful login timestamp for audit and tracking purposes.

4. **Timestamps**:
   - The `created_at` column automatically tracks the creation date of the user account.

---

#### **Usage Scenarios**

##### **Registration**
- When a user registers:
   - A new row is inserted into the `users` table with their `email`, `nickname`, `password_hash`, and other details.
   - The `is_logged` field is set to `0` by default.

##### **Login**
- Upon successful login:
   - The `is_logged` field is updated to `1`.
   - The `logged_in_at` field is updated to the current timestamp.

##### **Logout**
- When the user logs out:
   - The `is_logged` field is reset to `0`.

---

This schema is essential for managing user authentication and profile details effectively in the system.


## Installation

Follow these steps to set up the application:

### Step 1: Clone the Repository

```bash
git clone https://github.com/tozogabee/registrationApp.git
cd registrationApp
```

### Step 2: Run Docker

Build and start the application using Docker from the registraionApp folder:

```bash
docker-compose up -d
```

Once the application starts, it will be available at `http://localhost:8080/client` or `http://localhost:8080/client/`.

---

## Usage

### Accessing the Application

1. Open your browser and navigate to `http://localhost:8080/client`.
2. Use Postman or any REST client to interact with the API endpoints.
3. Check logs for any errors or detailed information:
4. You can see the logs in backend/log/backend.log

---

## API Documentation

### Endpoints

| **Endpoint**           | **Method** | **Description**                                     | **Parameters**                                                                                     |
|-------------------------|------------|-----------------------------------------------------|----------------------------------------------------------------------------------------------------|
| `/backend/registration` | POST       | Registers a new user.                              | **Request Body**: `email`, `password`, `nickname`,`birth_date`                                     |
| `/backend/login`        | POST       | Logs in the user and returns an authentication token. | **Request Body**: `email`, `password`                                                              |
| `/backend/profile`      | GET        | Fetches the authenticated user's profile details.   |                                                        |
| `/backend/profile`      | POST       | Updates the authenticated user's profile details.   | **Request Body**: `Authorization` `email`, `password`, `nickname`,`birth_date`    |

---
### Examples

#### User Registration

**Request**
```json
POST /backend/register
Content-Type: application/json
{
   "email": "tozogabee@gmail.com",
   "nickname": "tozo",
   "birth_date": "1989-09-02",
   "password": "1234"
}
```

**Response**
```json
{
   "success": true,
   "message": "Registration successful!"
}
```

#### User Login

**Request**
```json
POST /backend/login
Content-Type: application/json

{
    "email": "tozogabee@gmail.com",
    "password": "1234"
}
```

**Response**
```json
{
   "success": true,
   "id": 6,
   "email": "tozogabee@gmail.com",
   "nickname": "tozogabee",
   "birth_date": "1998-01-01"
}
```

#### User logout
**Request**
```json
POST /backend/logout/6
```

**Response**
```json
{
   "success": true,
   "message": "tozogabee@mail.com logged out successfully!"
}
```
#### User update
**Request**
```json
POST /backend/profile
Content-Type: application/json
{
   "email": "tozoogabee@gmail.com",
   "password": "0115",
   "nickname" : "tozogabee2"
}
```

**Response**
```json
{
   "success": true,
   "message": "User updated successfully."
}
```
**Request**
```json
GET /backend/profile
```

**Response**
```json
{
   "success": true,
   "id": "6",
   "email": "tozogabee@gmail.com",
   "nickname": "tozogabee2",
   "birth_date": "1989-09-02"
}
```
---

## Tech Stack

| **Component**  | **Technology**            |
|-----------------|---------------------------|
| Backend         | PHP                       |
| Frontend        | HTML, CSS, JavaScript     |
| Database        | MySQL                     |
| Deployment      | Docker, Docker Compose    |


---
# System Under Test

This document provides a guide for testing the client system.

---

## Main Page

Navigate to the main page by visiting the following URL:

- **URL**: `http://localhost:8080/client` or `http://localhost:8080/client/`

On the main page, you will see buttons for **Login** and **Registration**.

---

## Registration

### **Positive Test**

1. Click on the **Registration** button.
2. You will be redirected to `http://localhost:8080/client/registration`.
3. Fill in the form fields correctly (e.g., email, password, username).
4. Click on the **Registration** button.
5. Verify:
   - The user is successfully added to the database.
   - The `is_logged` field for the user in the database is `0` (user is not logged in).

### **Negative Test**

1. Try registering a user with an already existing email.
   - **Expected Result**: You should see an error message indicating that the "Email is already used."
2. Try registering a user with an already existing nickname.
   - **Expected Result**: You should see an error message indicating that the "Nickname is already used."

---

## Update User Profile

### **Positive Test**

1. Login with a user:
   - Click on the **Login** button.
   - Fill in the fields correctly and submit.
   - You will be redirected to the **Profile page**.
2. Verify:
   - The `is_logged` field for the user in the database is updated to `1` (user is logged in).
3. On the profile page, fill in the fields you want to update.
4. Click on the **Update Profile** button.
5. Verify:
   - The user data is successfully updated in the database.
   - is_logged set to 0 again, user logged out.

### **Negative Test**

1. Attempt to update the profile using an email or nickname that already belongs to another existing user.
   - **Expected Result**: You should see an error message indicating that the email is already used or nickname is already used.

---

## Summary of Test URLs

| **Functionality** | **URL**                                         | **Action**                                                  |
|-------------------|-------------------------------------------------|-------------------------------------------------------------|
| Main Page         | `http://localhost:8080/client`                  | Displays the main page with Login and Registration options. |
| Login page        | `http://localhost:8080/client/login`            | Displays the main page with Login and Registration options. |
| Logout page       | `http://localhost:8080/client/logout/{user_id}` | Logged user logout.                                         |
| Registration Form | `http://localhost:8080/client/registration`     | Allows users to register.                                   |
| Profile Page      | After login                                     | Redirects to the profile page for logged-in users.          |

---

Follow this guide to systematically test the system and ensure that it meets all functional requirements.



Feel free to reach out if you have any questions or suggestions! 🎉
