
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
    "email": "example@example.com",
    "password": "securepassword123"
}
```

**Response**
```json
{
   "success": true,
   "id": 6,
   "email": "tozics@gmail.com",
   "nickname": "tozics",
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
## System under test

Go to the localhost:8080/client or localhost:8080/client/
You can see the main page with Login or Registration button.

#### Registration
##### Positive test

Click to the Registration
You will see the localhost:8080/client/registration
Fill the datas correctly, click to the Registration.
Check the database table with registered user and not logged in with is_logged is 0.

##### Negative test
Try to register user with same email and then same nick name.
You will see the email/nickname already used.
---

#### Update user
##### Positive test
Login with user
Click to the Login.
Fill the field correctly.
You will redirect to the profile site.
Check the is_logged value to the user, You will see this is 1.
Fill the datas that want to update, click to Update the profile.

##### Negative test
Try the update with same email/nickname that belongs to the other existing user
You must get email/nick name already used.
---

Feel free to reach out if you have any questions or suggestions! 🎉
